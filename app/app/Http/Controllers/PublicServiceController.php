<?php

namespace App\Http\Controllers;

use App\Models\PublicService;
use App\Models\Desa;
use App\Models\PengunjungKecamatan;
use App\Models\PublicServiceAttachment;
use App\Services\PortalService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class PublicServiceController extends Controller
{
    protected $portalService;

    public function __construct(PortalService $portalService)
    {
        $this->portalService = $portalService;
    }
    public function submit(Request $request)
    {
        // 1. Honeypot check (simple anti-spam)
        if ($request->filled('website')) {
            return response()->json(['message' => 'Spam detected.'], 422);
        }

        // 2. Rate Limiting (2 reports / 24h per WA number)
        // Skip rate limiting for chatbox handoff requests (source=chatbox)
        $isChatboxHandoff = $request->input('source') === 'chatbox';
        if (!$isChatboxHandoff) {
            $count = PublicService::where('whatsapp', $request->whatsapp)
                ->where('created_at', '>=', Carbon::now()->subDay())
                ->count();
            if ($count >= 2) {
                return response()->json(['message' => 'Anda telah mencapai batas pengiriman laporan hari ini. Silakan coba lagi besok.'], 429);
            }
        }

        // 3. Security Keyword filtering (Soft redirection to SP4N-LAPOR)
        $isHandoff = Str::startsWith($request->uraian, '[Diteruskan dari Bot FAQ]');

        if (!$isHandoff) {
            $securityKeywords = ['korupsi', 'suap', 'pencurian', 'pidana', 'dana desa'];
            foreach ($securityKeywords as $keyword) {
                if (Str::contains(strtolower($request->uraian), $keyword)) {
                    return response()->json([
                        'type' => 'security_referral',
                        'message' => 'Informasi: Untuk laporan terkait indikasi tata kelola keuangan atau penyimpangan berat, disarankan menggunakan kanal resmi SP4N-LAPOR! demi perlindungan data Anda.',
                        'link' => 'https://lapor.go.id'
                    ], 200);
                }
            }

            // 4. SIAK keyword filtering (Passive redirection)
            $siakKeywords = ['ktp', 'kk', 'kartu keluarga', 'akta', 'capil', 'siak', 'domisili'];
            foreach ($siakKeywords as $keyword) {
                if (Str::contains(strtolower($request->uraian), $keyword)) {
                    return response()->json([
                        'type' => 'siak_referral',
                        'message' => 'Informasi: Untuk layanan kependudukan (KTP, KK, Akta), silakan merujuk ke portal resmi SIAK atau layanan Dispendukcapil Kabupaten.',
                        'link' => 'https://siakterpusat.kemendagri.go.id'
                    ], 200);
                }
            }
        }

        // 5. FAQ Logic Integration
        if ($request->filled('uraian')) {
            $userQuestion = strtolower($request->uraian);
            $matchingFaq = \App\Models\PelayananFaq::where('is_active', true)->get()->first(function ($faq) use ($userQuestion) {
                $keywords = explode(',', strtolower($faq->keywords));
                foreach ($keywords as $kw) {
                    if (trim($kw) !== '' && str_contains($userQuestion, trim($kw))) {
                        return true;
                    }
                }
                return false;
            });

            if ($matchingFaq) {
                return response()->json([
                    'type' => 'faq_match',
                    'question' => $matchingFaq->question,
                    'message' => "Jawaban Otomatis:\n" . $matchingFaq->answer . "\n\nInformasi ini bersifat umum. Jika Anda masih ingin mengirim laporan resmi, silakan ubah sedikit deskripsi Anda atau sampaikan detail lainnya.",
                    'answer' => $matchingFaq->answer
                ], 200);
            }
        }

        // Honeypot check for bots
        if ($request->filled('website')) {
            return response()->json(['message' => 'Layanan tidak dapat diproses (Spam detected).'], 422);
        }

        // 5. Validation
        $validator = Validator::make($request->all(), [
            'category' => 'nullable|in:pelayanan,pengaduan,umkm,loker',
            'jenis_layanan' => 'required|string',
            'desa_id' => 'nullable|string', // Changed to string to handle '999'
            'nama_pemohon' => 'nullable|string|max:255',
            'nik' => 'nullable|string|max:16',
            'uraian' => 'required|string|max:1000',
            'whatsapp' => 'required|string|regex:/^[0-9+]+$/',
            'foto.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $desaId = $request->desa_id;
        if ($desaId == '999') {
            $desaId = null;
        }

        // 6. Create record (Status: Menunggu Verification)
        $service = PublicService::create([
            'uuid' => (string) Str::uuid(),
            'nama_pemohon' => $request->nama_pemohon ?? 'Warga (Web)',
            'nik' => $request->nik,
            'desa_id' => $desaId,
            'jenis_layanan' => $request->jenis_layanan,
            'uraian' => $request->uraian,
            'whatsapp' => $request->whatsapp,
            'is_agreed' => $request->boolean('is_agreed', true),
            'ip_address' => $request->ip(),
            'status' => PublicService::STATUS_MENUNGGU,
            'category' => $request->input('category', PublicService::CATEGORY_PELAYANAN),
            'source' => $request->input('source', 'web_form')
        ]);

        // 6b. GUEST BOOK INTEGRATION: Create record in pengunjung_kecamatan
        try {
            PengunjungKecamatan::create([
                'nama' => $request->nama_pemohon ?? 'Warga (Bot)',
                'nik' => $request->nik,
                'desa_asal_id' => $desaId,
                'alamat_luar' => ($request->desa_id == '999') ? 'Luar Wilayah Kecamatan Besuk' : null,
                'no_hp' => $request->whatsapp,
                'tujuan_bidang' => 'Pelayanan Umum', // Aligned with visitor dropdown
                'keperluan' => '[' . $request->jenis_layanan . '] ' . $request->uraian,
                'jam_datang' => now(),
                'status' => 'menunggu'
            ]);
        } catch (\Exception $e) {
            \Log::error('Gagal mencatat buku tamu (PublicService): ' . $e->getMessage());
        }

        // 7. Handle uploads (Dynamic Multi-File)
        if ($request->hasFile('foto')) {
            $files = $request->file('foto');
            $labels = $request->input('foto_labels', []);

            foreach ($files as $i => $file) {
                if ($file->isValid()) {
                    $path = $file->store('public_services', 'local');
                    $label = $labels[$i] ?? 'Berkas ' . ($i + 1);

                    PublicServiceAttachment::create([
                        'public_service_id' => $service->id,
                        'label' => $label,
                        'file_path' => $path,
                        'original_name' => $file->getClientOriginalName(),
                        'file_type' => $file->getClientMimeType()
                    ]);

                    // Fallback for old system (Keep first 2 as file_path_1 and file_path_2 for basic compatibility)
                    if ($i === 0)
                        $service->update(['file_path_1' => $path]);
                    if ($i === 1)
                        $service->update(['file_path_2' => $path]);
                }
            }
        }

        // 8. Send WhatsApp notification to reporter via standardized PortalService
        try {
            $this->portalService->sendComplaintConfirmation($service);
        } catch (\Exception $e) {
            \Log::warning('WA notification gagal (non-fatal): ' . $e->getMessage());
        }

        return response()->json([
            'message' => 'Terima kasih. Laporan Anda telah kami terima dengan PIN Lacak: ' . $service->tracking_code . '. Status awal: "Menunggu Klarifikasi".',
            'uuid' => $service->uuid,
            'tracking_code' => $service->tracking_code,
            'receipt_url' => route('receipt.download', $service->uuid),
            'tracking_url' => route('public.tracking') . '?q=' . $service->tracking_code
        ]);
    }

        // Deleted old __construct and faqSearch logic moved below if needed
    
    public function faqSearch(Request $request)
    {
        // Use injected service instead of standalone
        $faqSearchService = app(\App\Services\FaqSearchService::class);
        $query = $request->query('q', '');
        $data = $faqSearchService->search($query);

        // Adjust for legacy frontend expectation if necessary
        if ($data['found'] && !isset($data['multiple'])) {
            $data['multiple'] = false;
        }

        return response()->json($data);
    }

    /**
     * Public Tracking Page
     */
    public function trackingPage()
    {
        $masterLayanan = \App\Models\MasterLayanan::where('is_active', true)
            ->orderBy('urutan')
            ->get();
            
        return view('public.layanan', compact('masterLayanan'));
    }

    /**
     * Check Status via WA, PIN or UUID
     */
    public function checkStatus(Request $request)
    {
        $request->validate([
            'identifier' => 'required|string',
            'whatsapp' => 'nullable|string', 
        ]);

        try {
            $identifier = trim($request->identifier);
            $inputWa = $request->whatsapp ? preg_replace('/[^0-9]/', '', $request->whatsapp) : null;
            $cleanInput = preg_replace('/[^0-9]/', '', $identifier);

            // 1. Build Universal Query
            $query = PublicService::query();

            // Check for UUID format
            if (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $identifier)) {
                $query->where('uuid', $identifier);
            } 
            // Check for PIN (6 digits)
            elseif (preg_match('/^[0-9]{6}$/', $identifier)) {
                $query->where('tracking_code', $identifier);
            }
            // Check for Phone (>= 9 digits)
            elseif (strlen($cleanInput) >= 9) {
                $suffix = substr($cleanInput, -10);
                $query->where(function($q) use ($suffix, $cleanInput) {
                    $q->where('whatsapp_suffix', $suffix)
                      ->orWhere('whatsapp', 'like', "%$cleanInput%");
                });
            } else {
                return response()->json(['found' => false, 'message' => 'Format identitas tidak dikenali.'], 400);
            }

            $service = $query->with(['desa', 'handler', 'histories'])
                ->latest()
                ->first();

            if (!$service) {
                return response()->json([
                    'found' => false, 
                    'message' => 'Berkas tidak ditemukan. Mohon periksa kembali PIN atau nomor WhatsApp Anda.'
                ], 404);
            }

            // 2. Security Verification
            // Skip verification if searching directly by full WhatsApp number
            $isPhoneSearch = (strlen($cleanInput) >= 10 && str_contains($service->whatsapp, $cleanInput));
            
            if (!$isPhoneSearch && !$inputWa) {
                return response()->json([
                    'found' => false,
                    'auth_required' => true,
                    'message' => 'Untuk keamanan, masukkan Nomor WhatsApp yang digunakan saat mendaftar.'
                ], 403);
            }

            if ($inputWa) {
                $ownerPhone = preg_replace('/[^0-9]/', '', $service->whatsapp);
                // Match either full number or last 4 digits
                if (!str_contains($ownerPhone, $inputWa) && !str_contains($inputWa, substr($ownerPhone, -4))) {
                    return response()->json([
                        'found' => false,
                        'auth_required' => true,
                        'message' => 'Kombinasi data tidak cocok. Pastikan nomor WA sudah benar.'
                    ], 403);
                }
            }

            return response()->json($this->buildStatusResponse($service));

        } catch (\Exception $e) {
            \Log::error("Tracking Error: " . $e->getMessage(), ['input' => $request->all()]);
            return response()->json([
                'found' => false, 
                'message' => 'Gagal memuat status berkas. Silakan coba beberapa saat lagi.'
            ], 500);
        }
    }

    /**
     * Build standardized status response
     */
    protected function buildStatusResponse(PublicService $service): array
    {
        $response = [
            'found' => true,
            'uuid' => $service->uuid,
            'tracking_code' => $service->tracking_code,
            'jenis_layanan' => $service->jenis_layanan,
            'status' => $service->status,
            'status_label' => $service->status_label,
            'status_color' => $service->status_color,
            'created_at' => $service->created_at->format('d M Y, H:i'),
            'public_response' => $service->effective_public_response,
            'completion_type' => $service->completion_type,
            'rating' => $service->rating,
            'citizen_feedback' => $service->citizen_feedback,
            'feedback_at' => $service->feedback_at ? $service->feedback_at->format('d M Y, H:i') : null,
            'histories' => $service->histories->map(function($h) {
                return [
                    'status_to' => $h->status_to_label,
                    'comment' => $h->comment,
                    'created_at' => $h->created_at->format('d M Y, H:i'),
                    'action_type' => $h->action_type
                ];
            }),
        ];

        // Digital completion
        if ($service->completion_type === 'digital' && $service->result_file_path) {
            $response['download_url'] = asset('storage/' . $service->result_file_path);
        }

        // Physical completion
        if ($service->completion_type === 'physical') {
            $response['pickup_info'] = [
                'ready_at' => $service->ready_at?->format('d M Y, H:i'),
                'pickup_person' => $service->pickup_person,
                'pickup_notes' => $service->pickup_notes,
            ];
        }

        return $response;
    }

    /**
     * Store PublicService from WhatsApp API Gateway
     * Called by the WhatsApp automation system via REST API
     */
    public function storeFromWhatsapp(Request $request)
    {
        // Validate incoming data from WhatsApp API
        $validator = Validator::make($request->all(), [
            'uuid' => 'required|string|unique:public_services,uuid',
            'category' => 'required|in:pengaduan,pelayanan,umkm,loker',
            'whatsapp' => 'required|string',
            'nama_pemohon' => 'required|string|max:255',
            'uraian' => 'required|string',
            'jenis_layanan' => 'required|string',
            'status' => 'nullable|string',
            'source' => 'nullable|string',
            'desa_id' => 'nullable|integer|exists:desa,id',
            'nama_desa_manual' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Create PublicService record
            $service = PublicService::create([
                'uuid' => $request->uuid,
                'category' => $request->category,
                'source' => $request->input('source', 'whatsapp'),
                'whatsapp' => $request->whatsapp,
                'nama_pemohon' => $request->nama_pemohon,
                'uraian' => $request->uraian,
                'jenis_layanan' => $request->jenis_layanan,
                'status' => $request->input('status', PublicService::STATUS_MENUNGGU),
                'desa_id' => $request->desa_id,
                'nama_desa_manual' => $request->nama_desa_manual,
                'ip_address' => $request->ip(),
            ]);

            // Log successful creation
            \Log::info('WhatsApp message received and stored', [
                'service_id' => $service->id,
                'category' => $service->category,
                'phone' => $service->whatsapp
            ]);

            return response()->json([
                'success' => true,
                'message' => 'WhatsApp message successfully stored. Tracking PIN: ' . $service->tracking_code,
                'data' => [
                    'id' => $service->id,
                    'uuid' => $service->uuid,
                    'tracking_code' => $service->tracking_code,
                    'category' => $service->category,
                    'status' => $service->status
                ]
            ], 201);

        } catch (\Exception $e) {
            \Log::error('Failed to store WhatsApp message', [
                'error' => $e->getMessage(),
                'data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to store message',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    /**
     * Submit feedback/rating for a completed service
     */
    public function submitFeedback(Request $request, $uuid)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'citizen_feedback' => 'nullable|string|max:500',
        ]);

        $service = PublicService::where('uuid', $uuid)->firstOrFail();

        // Prevent double feedback
        if ($service->feedback_at) {
            return response()->json(['message' => 'Anda sudah memberikan penilaian untuk layanan ini.'], 422);
        }

        $service->update([
            'rating' => $request->rating,
            'citizen_feedback' => $request->citizen_feedback,
            'feedback_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Terima kasih atas penilaian Anda! Masukan Anda sangat berarti bagi peningkatan layanan kami.'
        ]);
    }
}

