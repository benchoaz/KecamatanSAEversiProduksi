<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\PublicService;
use App\Models\PublicServiceAttachment;
use App\Models\MasterLayanan;
use App\Models\ServiceNode;
use App\Models\Desa;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use App\Traits\HasWhatsAppNotifications;

class LayananController extends Controller
{
    use HasWhatsAppNotifications;
    /**
     * Show dynamic layanan page (besluit tree or legacy fallback)
     */
    public function showLayanan(string $slug)
    {
        // Cari berdasarkan slug ATAU nama layanan (backward compatible)
        $layanan = MasterLayanan::where(function($q) use ($slug) {
            $q->where('slug', $slug)
              ->orWhere('nama_layanan', $slug);
        })
        ->where('is_active', true)
        ->first();

        if (!$layanan) abort(404);

        $desas = Desa::orderBy('nama_desa')->get();

        // Jika punya node → tampil step navigator baru
        if ($layanan->has_nodes) {
            $nodes = ServiceNode::where('master_layanan_id', $layanan->id)
                ->whereNull('parent_id')
                ->where('is_active', true)
                ->orderBy('urutan')
                ->get();
        }

        // Bangun requirements dari attachment_requirements (berlaku untuk semua mode)
        $masterRequirements = !empty($layanan->attachment_requirements)
            ? collect($layanan->attachment_requirements)->values()->map(fn($r, $i) => [
                'id'             => 999000 + $i,
                'label'          => (string) $r,
                'type'           => 'file_upload',
                'is_required'    => true,
                'description'    => null,
                'accepted_types' => 'jpg,png,pdf',
                'max_size_mb'    => 5
            ])->values()->all()
            : [];

        return view('public.service_navigator', [
            'layanan'              => $layanan,
            'rootNodes'            => $nodes ?? [],
            'desas'                => $desas,
            'directSubmission'     => !$layanan->has_nodes,
            // Untuk layanan tanpa node: tampilkan langsung
            // Untuk layanan dengan node: requirements ditampilkan sebagai "syarat umum" di bawah node
            'requirements'         => !$layanan->has_nodes ? $masterRequirements : [],
            'masterRequirements'   => $masterRequirements,
        ]);
    }

    /**
     * Show the unified application form (legacy hardcoded types)
     */
    public function showForm($type)
    {
        $validTypes = ['ktp', 'kk', 'akta', 'sktm', 'domisili', 'nikah', 'bpjs'];
        
        if (in_array($type, $validTypes)) {
            $desas = Desa::orderBy('nama_desa')->get();
            $context = $this->getServiceContext($type);
            return view('public.apply', compact('type', 'desas', 'context'));
        }

        // Check if it's a dynamic service (MasterLayanan)
        return $this->showLayanan($type);
    }

    /**
     * Store the service application
     */
    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required|string',
            'nama_pemohon' => 'required|string|max:255',
            'nik' => 'required|string|size:16',
            'whatsapp' => 'required|string|min:10',
            'desa_id' => 'required|exists:desa,id',
            'uraian' => 'nullable|string|max:1000',
            'attachments.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        try {
            return DB::transaction(function () use ($request) {
                // 1. Create PublicService Record
                $service = PublicService::create([
                    'uuid' => (string) Str::uuid(),
                    'category' => PublicService::CATEGORY_PELAYANAN,
                    'jenis_layanan' => strtoupper($request->type),
                    'nama_pemohon' => $request->nama_pemohon,
                    'nik' => $request->nik,
                    'whatsapp' => $request->whatsapp,
                    'desa_id' => $request->desa_id,
                    'uraian' => $request->uraian ?? "Pengajuan online " . strtoupper($request->type),
                    'status' => PublicService::STATUS_MENUNGGU,
                    'source' => 'web_portal',
                    'is_agreed' => true,
                    'ip_address' => $request->ip(),
                ]);

                // 2. Handle Attachments
                if ($request->hasFile('attachments')) {
                    foreach ($request->file('attachments') as $index => $file) {
                        $path = $file->store('public_services', 'local');
                        
                        PublicServiceAttachment::create([
                            'public_service_id' => $service->id,
                            'label' => $request->attachment_labels[$index] ?? 'Lampiran ' . ($index + 1),
                            'file_path' => $path,
                            'original_name' => $file->getClientOriginalName(),
                            'file_type' => $file->getClientMimeType(),
                        ]);

                        // Backward compatibility for old columns if they exist
                        if ($index === 0) $service->update(['file_path_1' => $path]);
                        if ($index === 1) $service->update(['file_path_2' => $path]);
                    }
                }

                // 3. Send WhatsApp Notification (submission)
                $this->sendWaNotification($service, 'submission');

                return response()->json([
                    'success' => true,
                    'message' => 'Permohonan berhasil dikirim!',
                    'tracking_code' => $service->tracking_code,
                    'redirect' => route('layanan') . '?q=' . $service->tracking_code
                ]);
            });
        } catch (\Exception $e) {
            \Log::error('Service Submission Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem. Silakan coba lagi.'
            ], 500);
        }
    }

    public function storeNodeBased(Request $request)
    {
        $node = $request->node_id ? ServiceNode::find($request->node_id) : null;
        $showIdentity = $node ? $node->show_identity_form : true;

        $request->validate([
            'node_id'            => 'nullable|exists:service_nodes,id',
            'master_layanan_id'  => 'required|exists:master_layanan,id',
            'nama_pemohon'       => $showIdentity ? 'required|string|max:255' : 'nullable|string|max:255',
            'nik'                => $showIdentity ? 'required|string|size:16|regex:/^[0-9]+$/' : 'nullable|string|size:16|regex:/^[0-9]+$/',
            'whatsapp'           => $showIdentity ? 'required|string|min:9|max:15|regex:/^[0-9+]+$/' : 'nullable|string|min:9|max:15|regex:/^[0-9+]+$/',
            'desa_id'            => $showIdentity ? 'required|exists:desa,id' : 'nullable|exists:desa,id',
            'uraian'             => 'nullable|string|max:1000',
            'is_agreed'          => 'required|accepted',
            'attachments.*'      => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:10240',
            'attachment_req_ids' => 'nullable|array',
        ]);

        try {
            return DB::transaction(function () use ($request) {
                $layanan = MasterLayanan::findOrFail($request->master_layanan_id);
                $node    = $request->node_id ? ServiceNode::find($request->node_id) : null;

                // Security: Ensure node belongs to the service
                if ($node && $node->master_layanan_id != $layanan->id) {
                    abort(403, 'Akses node tidak valid untuk layanan ini.');
                }

                // Format WhatsApp (normalize to 62xxx)
                $wa = preg_replace('/[^0-9]/', '', $request->whatsapp ?? '');
                if (str_starts_with($wa, '0')) $wa = '62' . substr($wa, 1);
                if (!str_starts_with($wa, '62')) $wa = '62' . $wa;

                // Build service data — cek kolom service_node_id agar tidak error
                // jika migration belum dijalankan di server
                $serviceData = [
                    'uuid'          => (string) Str::uuid(),
                    'category'      => PublicService::CATEGORY_PELAYANAN,
                    'jenis_layanan' => $node
                        ? $layanan->nama_layanan . ' — ' . $node->name
                        : $layanan->nama_layanan,
                    'nama_pemohon'  => $request->nama_pemohon,
                    'nik'           => $request->nik,
                    'whatsapp'      => $wa,
                    'desa_id'       => $request->desa_id,
                    'uraian'        => $request->uraian
                        ?? 'Pengajuan online: ' . ($node ? $layanan->nama_layanan . ' — ' . $node->name : $layanan->nama_layanan),
                    'status'        => PublicService::STATUS_MENUNGGU,
                    'source'        => 'web_portal',
                    'is_agreed'     => true,
                    'ip_address'    => $request->ip(),
                ];

                // Hanya tambahkan service_node_id jika kolom sudah ada di DB
                if (Schema::hasColumn('public_services', 'service_node_id')) {
                    $serviceData['service_node_id'] = $node?->id;
                }

                $service = PublicService::create($serviceData);

                // Handle attachments (dengan requirement_id per file)
                if ($request->hasFile('attachments')) {
                    $reqIds = $request->input('attachment_req_ids', []);
                    $labels = $request->input('attachment_labels', []);

                    // Pre-validate requirement IDs to prevent FK violations
                    // (VPS may have different service_requirements IDs than form expects)
                    $validReqIds = [];
                    if (!empty($reqIds)) {
                        $validReqIds = \DB::table('service_requirements')
                            ->whereIn('id', array_filter($reqIds))
                            ->pluck('id')
                            ->flip()
                            ->all();
                    }

                    foreach ($request->file('attachments') as $idx => $file) {
                        $path = $file->store('public_services/' . $service->id, 'local');
                        $reqId = $reqIds[$idx] ?? null;

                        PublicServiceAttachment::create([
                            'public_service_id' => $service->id,
                            'requirement_id'    => (isset($validReqIds[$reqId]) ? $reqId : null),
                            'label'             => $labels[$idx] ?? 'Lampiran ' . ($idx + 1),
                            'file_path'         => $path,
                            'original_name'     => $file->getClientOriginalName(),
                            'file_type'         => $file->getClientMimeType(),
                        ]);
                    }
                }

                // Kirim notifikasi WhatsApp
                $this->sendWaNotification($service, 'submission');

                return response()->json([
                    'success'       => true,
                    'tracking_code' => $service->tracking_code,
                    'redirect'      => route('layanan') . '?q=' . $service->tracking_code,
                ]);
            });
        } catch (\Exception $e) {
            \Log::error('Node Submission Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem. Silakan coba lagi.',
            ], 500);
        }
    }

    /**
     * Get specific requirements and context for each service type
     */
    protected function getServiceContext($type)
    {
        $contexts = [
            'ktp' => [
                'title' => 'Pemohonan KTP-el',
                'icon' => 'fas fa-id-card',
                'color' => 'blue',
                'requirements' => [
                    'Fotokopi Kartu Keluarga (KK)',
                    'Fotokopi Akta Kelahiran',
                    'Pas Foto 3x4 (jika belum perekaman)',
                ]
            ],
            'kk' => [
                'title' => 'Pembaruan Kartu Keluarga',
                'icon' => 'fas fa-users',
                'color' => 'emerald',
                'requirements' => [
                    'KK Asli yang lama',
                    'Surat Nikah/Akta Cerai (jika ada perubahan)',
                    'Surat Keterangan Pindah (jika pindah datang)',
                ]
            ],
            'akta' => [
                'title' => 'Akta Kelahiran/Kematian',
                'icon' => 'fas fa-file-signature',
                'color' => 'amber',
                'requirements' => [
                    'Surat Keterangan Lahir/Mati dari RS/Desa',
                    'Fotokopi KK & KTP Orang Tua',
                    'Fotokopi Buku Nikah (untuk Akta Lahir)',
                ]
            ],
            'sktm' => [
                'title' => 'Surat Keterangan Tidak Mampu',
                'icon' => 'fas fa-hand-holding-heart',
                'color' => 'rose',
                'requirements' => [
                    'Surat Pengantar RT/RW/Desa',
                    'Fotokopi KTP & KK',
                    'Foto Rumah (Tampak Depan)',
                ]
            ],
            'domisili' => [
                'title' => 'Surat Keterangan Domisili',
                'icon' => 'fas fa-map-marker-alt',
                'color' => 'teal',
                'requirements' => [
                    'Surat Pengantar Desa',
                    'Fotokopi KTP & KK',
                ]
            ],
            'nikah' => [
                'title' => 'Rekomendasi Nikah (N1-N4)',
                'icon' => 'fas fa-heart',
                'color' => 'pink',
                'requirements' => [
                    'Fotokopi KTP & KK Calon Pengantin',
                    'Fotokopi KTP & KK Orang Tua',
                    'Pas Foto Background Biru',
                ]
            ],
            'bpjs' => [
                'title' => 'Pendaftaran/Update BPJS PBI',
                'icon' => 'fas fa-hospital-user',
                'color' => 'indigo',
                'requirements' => [
                    'Fotokopi KTP & KK',
                    'Surat Keterangan Tidak Mampu (SKTM)',
                ]
            ],
        ];

        return $contexts[$type] ?? $contexts['ktp'];
    }
}
