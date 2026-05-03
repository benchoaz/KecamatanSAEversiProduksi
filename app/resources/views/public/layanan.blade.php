@extends('layouts.public')

@section('title', 'Lacak Berkas - ' . appProfile()->region_level . ' ' . appProfile()->region_name)

@section('content')
    @php
        $waRaw = preg_replace('/[^0-9]/', '', appProfile()->phone ?? '6281232232532');
        $waAdminLink = str_starts_with($waRaw, '0') ? '62' . substr($waRaw, 1) : $waRaw;
    @endphp

    <div class="min-h-[80vh] bg-[radial-gradient(ellipse_at_top_right,_var(--tw-gradient-stops))] from-slate-50 via-teal-50/30 to-blue-50/30 py-12 md:py-24 relative overflow-hidden">
        {{-- Decorative Elements --}}
        <div class="absolute top-0 right-0 w-[500px] h-[500px] bg-teal-500/5 rounded-full blur-[120px] -mr-64 -mt-64"></div>
        <div class="absolute bottom-0 left-0 w-[500px] h-[500px] bg-blue-500/5 rounded-full blur-[120px] -ml-64 -mb-64"></div>

        <div class="container mx-auto px-6 relative z-10">
            {{-- Header --}}
            <div class="max-w-4xl mx-auto text-center mb-16 md:mb-24 animate__animated animate__fadeIn">
                <div class="inline-flex items-center justify-center w-24 h-24 bg-white rounded-[2.5rem] shadow-2xl shadow-teal-900/10 mb-8 text-teal-600 relative group transition-transform hover:scale-110 duration-500">
                    <div class="absolute inset-0 bg-teal-600 rounded-[2.5rem] opacity-0 group-hover:opacity-10 blur-xl transition-opacity"></div>
                    <i class="fas fa-search-location text-4xl"></i>
                </div>
                <h1 class="text-4xl md:text-6xl font-black text-slate-800 mb-6 tracking-tight">Lacak Berkas Anda</h1>
                <p class="text-slate-500 text-lg md:text-xl font-medium leading-relaxed max-w-2xl mx-auto">
                    Pantau progres pengajuan layanan Anda secara real-time. Masukkan PIN Lacak atau Nomor WhatsApp Anda di bawah ini.
                </p>
            </div>

            <div class="max-w-6xl mx-auto grid grid-cols-1 lg:grid-cols-12 gap-8 md:gap-16 items-start">
                {{-- Left: Tracking Form --}}
                <div class="lg:col-span-7 space-y-8 animate__animated animate__fadeInLeft">
                    <div class="glass-card bg-white/70 backdrop-blur-xl rounded-[2.5rem] md:rounded-[3.5rem] shadow-2xl shadow-slate-200/50 p-1 md:p-2 border border-white/50 overflow-hidden relative group">
                        <div class="absolute inset-0 bg-gradient-to-br from-teal-500/5 to-blue-500/5 opacity-0 group-hover:opacity-100 transition-opacity duration-700"></div>
                        
                        <div class="relative bg-white/50 rounded-[2rem] md:rounded-[3rem] p-8 md:p-12 border border-white">
                            <form id="trackingForm" class="space-y-8">
                                <div class="space-y-6">
                                    <div class="form-control w-full">
                                        <label class="label mb-3 px-2">
                                            <span class="label-text font-black text-slate-700 uppercase tracking-widest text-[10px]">Identitas Berkas</span>
                                        </label>
                                        <div class="relative group">
                                            <div class="absolute inset-y-0 left-0 pl-6 flex items-center pointer-events-none text-slate-400 group-focus-within:text-teal-500 transition-colors duration-300">
                                                <i class="fas fa-fingerprint text-xl"></i>
                                            </div>
                                            <input type="text" id="identifier" name="identifier" required
                                                placeholder="PIN 6-Digit atau No. WhatsApp"
                                                class="input input-lg w-full h-16 md:h-20 pl-16 bg-white/80 border-slate-200 focus:border-teal-500 rounded-[1.5rem] md:rounded-[2rem] transition-all duration-300 font-bold text-slate-800 placeholder:text-slate-300 shadow-sm focus:shadow-teal-500/10 text-lg" />
                                        </div>
                                    </div>

                                    {{-- Secure Verification Field --}}
                                    <div id="verificationField" class="form-control w-full hidden">
                                        <label class="label mb-3 px-2">
                                            <span class="label-text font-black text-teal-700 uppercase tracking-widest text-[10px] flex items-center gap-2">
                                                <i class="fas fa-shield-alt"></i> Verifikasi Keamanan
                                            </span>
                                        </label>
                                        <div class="relative group">
                                            <div class="absolute inset-y-0 left-0 pl-6 flex items-center pointer-events-none text-slate-400 group-focus-within:text-teal-500 transition-colors duration-300">
                                                <i class="fab fa-whatsapp text-xl"></i>
                                            </div>
                                            <input type="text" id="whatsapp_verify" name="whatsapp_verify"
                                                placeholder="Konfirmasi No. WhatsApp"
                                                class="input input-lg w-full h-16 md:h-20 pl-16 bg-teal-50/30 border-teal-100 focus:border-teal-500 rounded-[1.5rem] md:rounded-[2rem] transition-all duration-300 font-bold text-slate-800 placeholder:text-teal-200 shadow-sm" />
                                        </div>
                                        <p class="mt-3 px-2 text-[10px] font-bold text-slate-400 italic">
                                            *Wajib diisi jika menggunakan pelacakan via PIN.
                                        </p>
                                    </div>
                                </div>

                                <button type="submit" id="btnTrack" class="btn btn-lg h-16 md:h-20 w-full bg-slate-900 hover:bg-black text-white border-0 rounded-[1.5rem] md:rounded-[2rem] font-black text-lg md:text-xl shadow-2xl shadow-slate-900/20 group relative overflow-hidden transition-all duration-300">
                                    <div class="absolute inset-0 bg-gradient-to-r from-teal-500 to-blue-500 opacity-0 group-hover:opacity-10 transition-opacity"></div>
                                    <span class="relative z-10 flex items-center justify-center gap-3">
                                        Lacak Sekarang
                                        <i class="fas fa-arrow-right group-hover:translate-x-2 transition-transform"></i>
                                    </span>
                                </button>
                            </form>

                            {{-- Result Container --}}
                            <div id="resultContainer" class="hidden mt-12 animate__animated animate__fadeIn">
                                <div id="resultContent" class="space-y-8">
                                    {{-- Dynamic content from JS --}}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Right: Sidebar Info --}}
                <div class="lg:col-span-5 space-y-8 animate__animated animate__fadeInRight">
                    {{-- Status Legend --}}
                    <div class="bg-white rounded-[2.5rem] p-10 shadow-xl shadow-slate-200/50 border border-slate-100 relative overflow-hidden group">
                        <div class="absolute top-0 right-0 w-24 h-24 bg-slate-50 rounded-bl-[5rem] -mr-8 -mt-8 transition-transform group-hover:scale-110 duration-500"></div>
                        
                        <h4 class="text-xl font-black text-slate-800 mb-8 relative">Alur Layanan</h4>
                        <div class="space-y-6 relative">
                            <div class="flex gap-6 items-start">
                                <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-500 flex items-center justify-center flex-shrink-0 shadow-inner">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <div>
                                    <h5 class="font-black text-slate-800 text-sm">Menunggu Verifikasi</h5>
                                    <p class="text-xs text-slate-500 leading-relaxed mt-1">Berkas telah diterima sistem & menunggu pengecekan awal oleh petugas.</p>
                                </div>
                            </div>
                            <div class="flex gap-6 items-start">
                                <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-500 flex items-center justify-center flex-shrink-0 shadow-inner">
                                    <i class="fas fa-sync-alt fa-spin-slow"></i>
                                </div>
                                <div>
                                    <h5 class="font-black text-slate-800 text-sm">Sedang Diproses</h5>
                                    <p class="text-xs text-slate-500 leading-relaxed mt-1">Petugas sedang mengerjakan administrasi berkas Anda.</p>
                                </div>
                            </div>
                            <div class="flex gap-6 items-start">
                                <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-500 flex items-center justify-center flex-shrink-0 shadow-inner">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <div>
                                    <h5 class="font-black text-slate-800 text-sm">Selesai</h5>
                                    <p class="text-xs text-slate-500 leading-relaxed mt-1">Berkas Anda sudah siap! Bisa diambil di kantor atau didownload.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Help Card --}}
                    <div class="bg-teal-600 rounded-[2.5rem] p-10 text-white shadow-2xl shadow-teal-900/20 relative overflow-hidden group">
                        <div class="absolute top-0 left-0 w-full h-full bg-[radial-gradient(circle_at_top_right,_var(--tw-gradient-stops))] from-white/10 to-transparent opacity-50"></div>
                        <div class="relative z-10">
                            <div class="w-16 h-16 bg-white/20 rounded-2xl flex items-center justify-center mb-8">
                                <i class="fab fa-whatsapp text-3xl"></i>
                            </div>
                            <h4 class="text-2xl font-black mb-4">Butuh Bantuan?</h4>
                            <p class="text-teal-50 font-medium text-sm leading-relaxed mb-8 opacity-80">
                                Tim pelayanan kami siap membantu jika Anda mengalami kendala dalam proses pengajuan atau pelacakan berkas.
                            </p>
                            <a href="https://wa.me/{{ $waAdminLink }}" target="_blank" class="btn bg-white hover:bg-teal-50 text-teal-600 border-0 rounded-2xl w-full h-14 font-black text-sm uppercase tracking-wider group">
                                Hubungi Petugas
                                <i class="fas fa-external-link-alt ml-2 group-hover:rotate-12 transition-transform"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Catalog Section (Condensed) --}}
            <div class="mt-32">
                <div class="flex flex-col md:flex-row justify-between items-end mb-12 gap-8 px-2">
                    <div class="max-w-xl">
                        <h2 class="text-3xl md:text-4xl font-black text-slate-800 mb-4 tracking-tight">Katalog Layanan Digital</h2>
                        <p class="text-slate-500 font-medium">Bikin berkas jadi lebih simpel. Pilih layanan di bawah untuk mulai pengajuan online.</p>
                    </div>
                    <a href="#trackingForm" class="hidden md:flex items-center gap-3 text-sm font-black text-teal-600 hover:text-teal-700 transition-colors group">
                        KEMBALI KE ATAS
                        <i class="fas fa-chevron-up group-hover:-translate-y-1 transition-transform"></i>
                    </a>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
                    @foreach($masterLayanan as $svc)
                        <div class="group bg-white rounded-[2.5rem] p-10 shadow-xl shadow-slate-200/40 border border-transparent hover:border-teal-200 hover:shadow-teal-900/5 transition-all duration-500 flex flex-col h-full relative">
                            <div class="absolute top-8 right-8 w-12 h-12 rounded-2xl {{ $svc->warna_bg ?? 'bg-slate-50' }} {{ $svc->warna_text ?? 'text-slate-400' }} flex items-center justify-center opacity-40 group-hover:opacity-100 transition-all duration-500 group-hover:scale-110">
                                <i class="fas {{ $svc->ikon ?? 'fa-file-alt' }} text-lg"></i>
                            </div>
                            
                            <h3 class="text-xl font-black text-slate-800 mb-4 pr-12">{{ $svc->nama_layanan }}</h3>
                            
                            <div class="flex items-center gap-2 mb-6">
                                <span class="px-3 py-1 bg-slate-100 text-slate-500 rounded-full text-[10px] font-black uppercase tracking-widest">
                                    EST. {{ $svc->estimasi_waktu ?? '3 HARI' }}
                                </span>
                            </div>

                            <p class="text-sm text-slate-500 font-medium leading-relaxed mb-8 flex-grow">
                                {{ Str::limit($svc->deskripsi_syarat ?? 'Persyaratan umum berlaku.', 70) }}
                            </p>

                            <a href="{{ $svc->slug ? route('apply.layanan', $svc->slug) : '#' }}" class="btn btn-md w-full bg-slate-50 hover:bg-teal-600 hover:text-white text-slate-600 border-0 rounded-2xl normal-case font-black transition-all duration-300">
                                Ajukan Online
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const urlParams = new URLSearchParams(window.location.search);
            const query = urlParams.get('q');
            const wa = urlParams.get('wa');
            
            if (query) {
                document.getElementById('identifier').value = query;
                handleIdentifierChange(query);
                
                if (wa) {
                    document.getElementById('whatsapp_verify').value = wa;
                }

                setTimeout(() => {
                    document.getElementById('trackingForm').dispatchEvent(new Event('submit'));
                }, 500);
            }
        });

        const identifierInput = document.getElementById('identifier');
        const verificationField = document.getElementById('verificationField');
        const whatsappVerify = document.getElementById('whatsapp_verify');

        function handleIdentifierChange(val) {
            const trimmed = val.trim();
            // Jika 6 digit (PIN), tampilkan verifikasi WA
            if (/^[0-9]{6}$/.test(trimmed)) {
                verificationField.classList.remove('hidden');
                verificationField.classList.add('animate__animated', 'animate__fadeInDown');
                whatsappVerify.required = true;
            } else {
                // Jika UUID atau No WA (biasanya > 10 digit), sembunyikan verifikasi tambahan
                verificationField.classList.add('hidden');
                whatsappVerify.required = false;
            }
        }

        identifierInput.addEventListener('input', (e) => handleIdentifierChange(e.target.value));

        document.getElementById('trackingForm').addEventListener('submit', async function (e) {
            e.preventDefault();
            const btn = document.getElementById('btnTrack');
            const resultContainer = document.getElementById('resultContainer');
            const resultContent = document.getElementById('resultContent');

            const identifier = identifierInput.value;
            const whatsapp = whatsappVerify.value;

            // Loading state
            btn.disabled = true;
            btn.innerHTML = `<span class="loading loading-spinner loading-md"></span> <span class="ml-3">Mencari Berkas...</span>`;
            
            resultContainer.classList.add('hidden');
            resultContent.innerHTML = '';

            try {
                const response = await fetch('{{ route('public.tracking.check') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ identifier, whatsapp })
                });

                const data = await response.json();

                if (response.status === 403 && data.auth_required) {
                    verificationField.classList.remove('hidden');
                    whatsappVerify.required = true;
                    whatsappVerify.focus();
                    resultContent.innerHTML = `
                        <div class="p-8 bg-amber-50 rounded-[2rem] border border-amber-200 text-amber-800 font-bold flex items-center gap-4">
                            <div class="w-12 h-12 bg-amber-200 rounded-2xl flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-key text-xl"></i>
                            </div>
                            <div class="text-sm">
                                \${data.message}
                            </div>
                        </div>
                    `;
                    resultContainer.classList.remove('hidden');
                } else if (data.found) {
                    renderResult(data);
                } else {
                    renderNotFound(data.message);
                }
            } catch (error) {
                renderError();
            } finally {
                btn.disabled = false;
                btn.innerHTML = `Lacak Sekarang <i class="fas fa-arrow-right ml-2"></i>`;
            }
        });

        function renderResult(data) {
            const resultContent = document.getElementById('resultContent');
            const resultContainer = document.getElementById('resultContainer');

            const html = `
                <div class="bg-white p-10 rounded-[2.5rem] border border-slate-100 shadow-2xl shadow-teal-900/5 animate__animated animate__fadeInUp">
                    <div class="flex flex-col md:flex-row justify-between items-start gap-8 mb-10 pb-10 border-b border-slate-50">
                        <div class="flex items-center gap-6">
                            <div class="w-16 h-16 rounded-[1.5rem] bg-\${data.status_color}-100 text-\${data.status_color}-600 flex items-center justify-center text-2xl shadow-inner">
                                <i class="fas \${getStatusIcon(data.status)}"></i>
                            </div>
                            <div>
                                <span class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 block mb-2">STATUS BERKAS</span>
                                <h4 class="text-2xl font-black text-\${data.status_color}-700">\${data.status_label}</h4>
                            </div>
                        </div>
                        <div class="bg-slate-50 px-8 py-5 rounded-[1.5rem] border border-slate-100 w-full md:w-auto">
                            <span class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 block mb-2">ID TRACKING</span>
                            <div class="flex items-center gap-3">
                                <code class="text-lg font-black text-slate-800 tracking-wider">#\${data.tracking_code || data.uuid.substring(0,8)}</code>
                                <button onclick="copyToClipboard('\${data.tracking_code || data.uuid}')" class="p-2 hover:bg-slate-200 rounded-lg transition-colors text-slate-400 hover:text-teal-600" title="Salin ID">
                                    <i class="far fa-copy"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-10">
                        <div class="space-y-6">
                            <div class="group">
                                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-2">Jenis Layanan</span>
                                <p class="text-lg font-black text-slate-800">\${data.jenis_layanan}</p>
                            </div>
                            <div class="group">
                                <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest block mb-2">Waktu Pengajuan</span>
                                <p class="text-sm font-bold text-slate-600">\${data.created_at}</p>
                            </div>
                        </div>
                        
                        <div class="space-y-6">
                            \${data.public_response ? \`
                                <div class="p-6 bg-slate-900 rounded-[2rem] text-white relative overflow-hidden">
                                    <div class="absolute top-0 right-0 w-20 h-20 bg-teal-500/10 rounded-bl-full"></div>
                                    <span class="text-[10px] font-black text-teal-400 uppercase tracking-widest block mb-3 relative">Pesan Petugas:</span>
                                    <p class="text-sm text-slate-300 leading-relaxed font-medium relative italic">"\${data.public_response}"</p>
                                </div>
                            \` : \`
                                <div class="p-6 bg-slate-50 rounded-[2rem] border border-dashed border-slate-200 text-center">
                                    <p class="text-xs text-slate-400 font-medium italic">Menunggu respon lanjutan dari petugas...</p>
                                </div>
                            \`}
                        </div>
                    </div>

                    \${data.download_url ? \`
                        <div class="mt-12">
                            <a href="\${data.download_url}" target="_blank" class="btn btn-lg h-18 w-full bg-emerald-600 hover:bg-emerald-700 text-white border-0 rounded-[1.5rem] font-black text-lg shadow-xl shadow-emerald-900/20 group">
                                <i class="fas fa-cloud-download-alt mr-3 group-hover:translate-y-1 transition-transform"></i>
                                Download Berkas Digital
                            </a>
                        </div>
                    \` : ''}

                    \${data.pickup_info ? \`
                        <div class="mt-10 p-8 bg-amber-50 rounded-[2rem] border border-amber-100 flex items-start gap-6">
                            <div class="w-14 h-14 bg-amber-200 text-amber-700 rounded-2xl flex flex-shrink-0 items-center justify-center text-xl">
                                <i class="fas fa-map-marked-alt"></i>
                            </div>
                            <div>
                                <h5 class="text-sm font-black text-amber-900 mb-2 uppercase tracking-widest">Pengambilan Fisik</h5>
                                <p class="text-sm text-slate-700 font-bold mb-1">Siap: \${data.pickup_info.ready_at || 'Segera'}</p>
                                <p class="text-xs text-slate-500 font-medium leading-relaxed">\${data.pickup_info.pickup_notes || 'Silakan datang ke kantor kecamatan dengan membawa berkas asli.'}</p>
                            </div>
                        </div>
                    \` : ''}

                    {{-- Feedback UI --}}
                    <div class="mt-12">
                        <h5 class="text-sm font-black text-slate-800 mb-6 uppercase tracking-widest flex items-center gap-3">
                            <i class="fas fa-history text-teal-500"></i>
                            Riwayat Aktivitas
                        </h5>
                        <div class="space-y-0 border-l-2 border-slate-100 ml-4">
                            <div class="relative pl-8 pb-8">
                                <div class="absolute left-[-9px] top-0 w-4 h-4 rounded-full bg-emerald-500 border-4 border-white shadow-sm"></div>
                                <p class="text-xs font-black text-slate-800 mb-1">Berkas Diterima Sistem</p>
                                <p class="text-[10px] text-slate-400 font-bold">\${data.created_at}</p>
                            </div>
                            \${data.histories.map(h => \`
                                <div class="relative pl-8 pb-8">
                                    <div class="absolute left-[-9px] top-0 w-4 h-4 rounded-full bg-teal-500 border-4 border-white shadow-sm"></div>
                                    <p class="text-xs font-black text-slate-800 mb-1">\${h.status_to}</p>
                                    <p class="text-[10px] text-slate-400 font-bold mb-2">\${h.created_at}</p>
                                    \${h.comment ? \`
                                        <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100 text-[11px] text-slate-500 font-medium leading-relaxed italic">
                                            "\${h.comment}"
                                        </div>
                                    \` : ''}
                                </div>
                            \`).join('')}
                        </div>
                    </div>

                    ${renderFeedback(data)}
                </div>
            `;
            resultContent.innerHTML = html;
            resultContainer.classList.remove('hidden');
            resultContainer.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        function getStatusIcon(status) {
            switch(status) {
                case 'menunggu_verifikasi': return 'fa-clock';
                case 'diproses': return 'fa-sync-alt fa-spin';
                case 'selesai': return 'fa-check-circle';
                case 'ditolak': return 'fa-times-circle';
                default: return 'fa-info-circle';
            }
        }

        function renderFeedback(data) {
            return `
                <div class="mt-12 pt-10 border-t border-slate-100" id="feedbackSection">
                    \${data.feedback_at ? \`
                        <div class="bg-teal-50/50 p-8 rounded-[2rem] border border-teal-100 relative overflow-hidden">
                            <div class="absolute top-4 right-6 text-teal-100 text-6xl opacity-20"><i class="fas fa-quote-right"></i></div>
                            <div class="flex justify-between items-center mb-6">
                                <div class="flex gap-1">
                                    \${Array.from({length: 5}, (_, i) => \`
                                        <i class="fas fa-star \${i < data.rating ? 'text-amber-400' : 'text-slate-200'} text-lg"></i>
                                    \`).join('')}
                                </div>
                                <span class="text-[10px] font-black text-teal-400 uppercase tracking-widest">\${data.feedback_at}</span>
                            </div>
                            <p class="text-slate-600 font-bold italic text-sm">"\${data.citizen_feedback || 'Pelayanan memuaskan.'}"</p>
                        </div>
                    \` : \`
                        <div class="text-center bg-slate-50 p-10 rounded-[2.5rem] border border-slate-100">
                            <h4 class="text-lg font-black text-slate-800 mb-2">Bantu Kami Meningkatkan Layanan</h4>
                            <p class="text-xs font-medium text-slate-400 mb-8 uppercase tracking-widest">Berikan Rating Kepuasan Anda</p>
                            
                            <div class="flex justify-center gap-4 mb-8">
                                \${[1,2,3,4,5].map(s => \`
                                    <button type="button" onclick="setRating(\${s})" class="star-btn w-12 h-12 rounded-2xl bg-white text-slate-300 hover:text-amber-400 shadow-sm transition-all duration-300 text-xl" data-value="\${s}">
                                        <i class="fas fa-star"></i>
                                    </button>
                                \`).join('')}
                            </div>

                            <textarea id="feedback_comment" placeholder="Ada saran atau masukan untuk kami? (Opsional)" class="textarea textarea-lg w-full rounded-[1.5rem] bg-white border-slate-200 text-sm focus:border-teal-500 min-h-[120px] mb-6 p-6"></textarea>
                            
                            <button type="button" onclick="submitFeedback('\${data.uuid}')" id="btnSubmitFeedback" class="btn btn-lg h-16 w-full bg-slate-900 hover:bg-black text-white border-0 rounded-2xl font-black text-sm uppercase tracking-widest">
                                Kirim Penilaian
                            </button>
                        </div>
                    \`}
                </div>
            `;
        }

        function renderNotFound(message) {
            const resultContent = document.getElementById('resultContent');
            const resultContainer = document.getElementById('resultContainer');
            resultContent.innerHTML = \`
                <div class="p-12 text-center bg-rose-50 rounded-[3rem] border border-rose-100 animate__animated animate__shakeX">
                    <div class="w-20 h-20 bg-white rounded-3xl text-rose-500 flex items-center justify-center mx-auto mb-6 shadow-xl shadow-rose-900/5">
                        <i class="fas fa-search-minus text-3xl"></i>
                    </div>
                    <h4 class="text-2xl font-black text-slate-800 mb-3">Berkas Tidak Ditemukan</h4>
                    <p class="text-sm font-medium text-slate-500 leading-relaxed max-w-sm mx-auto">
                        \${message || 'Mohon periksa kembali nomor WA atau PIN Lacak Anda. Pastikan berkas sudah terdaftar di sistem kami.'}
                    </p>
                    <button onclick="document.getElementById('identifier').focus()" class="mt-8 text-xs font-black text-rose-600 hover:text-rose-700 uppercase tracking-widest border-b-2 border-rose-200">Coba ID Lain</button>
                </div>
            \`;
            resultContainer.classList.remove('hidden');
        }

        function renderError() {
            document.getElementById('resultContent').innerHTML = \`
                <div class="alert alert-error rounded-2xl font-bold shadow-xl">
                    <i class="fas fa-exclamation-triangle"></i>
                    Gagal menghubungi server. Silakan muat ulang halaman.
                </div>
            \`;
            document.getElementById('resultContainer').classList.remove('hidden');
        }

        window.copyToClipboard = (text) => {
            navigator.clipboard.writeText(text).then(() => {
                alert('ID Lacak berhasil disalin!');
            });
        }

        // Star Rating Logic
        let currentRating = 0;
        window.setRating = (r) => {
            currentRating = r;
            document.querySelectorAll('.star-btn').forEach(btn => {
                const val = parseInt(btn.getAttribute('data-value'));
                if (val <= r) {
                    btn.classList.add('text-amber-400', 'shadow-amber-500/10', 'scale-110');
                    btn.classList.remove('text-slate-300');
                } else {
                    btn.classList.remove('text-amber-400', 'shadow-amber-500/10', 'scale-110');
                    btn.classList.add('text-slate-300');
                }
            });
        }

        window.submitFeedback = async (uuid) => {
            if (currentRating === 0) {
                alert('Silakan pilih rating bintang.');
                return;
            }

            const comment = document.getElementById('feedback_comment').value;
            const btn = document.getElementById('btnSubmitFeedback');
            
            btn.disabled = true;
            btn.innerHTML = \`<span class="loading loading-spinner loading-sm"></span>\`;

            try {
                const response = await fetch(\`/service/feedback/\${uuid}\`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ rating: currentRating, citizen_feedback: comment })
                });

                if (response.ok) {
                    document.getElementById('feedbackSection').innerHTML = `
                        <div class="p-10 bg-emerald-50 rounded-[2.5rem] border border-emerald-100 text-center animate__animated animate__heartBeat">
                            <div class="w-16 h-16 bg-white text-emerald-500 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-lg">
                                <i class="fas fa-check text-2xl"></i>
                            </div>
                            <h4 class="text-lg font-black text-emerald-900 mb-1">Terima Kasih!</h4>
                            <p class="text-sm text-emerald-700 font-medium">Masukan Anda sangat berharga bagi kami.</p>
                        </div>
                    `;
                } else {
                    const errData = await response.json();
                    alert(errData.message || 'Gagal mengirim penilaian.');
                }
            } catch (e) {
                console.error(e);
                alert('Gagal mengirim penilaian. Silakan cek koneksi Anda.');
            } finally {
                btn.disabled = false;
                btn.innerHTML = 'Kirim Penilaian';
            }
        }
    </script>

    <style>
        .glass-card {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
        }
        .fa-spin-slow {
            animation: fa-spin 3s infinite linear;
        }
        @keyframes fa-spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .input:focus {
            outline: none;
            box-shadow: 0 10px 15px -3px rgba(20, 184, 166, 0.1);
        }
    </style>
@endsection
