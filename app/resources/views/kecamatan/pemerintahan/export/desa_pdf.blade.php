<!DOCTYPE html>
<html>
<head>
    <title>{{ $title }}</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 10px; color: #333; margin: 0; padding: 0; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        .header h1 { font-size: 16px; margin: 0; color: #000; text-transform: uppercase; }
        .header h2 { font-size: 14px; margin: 5px 0 0; color: #444; }
        
        .section-title { background-color: #1e293b; color: #ffffff; padding: 5px 10px; margin-top: 20px; margin-bottom: 10px; font-weight: bold; text-transform: uppercase; font-size: 11px; }
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; page-break-inside: auto; }
        th { background-color: #f1f5f9; color: #1e293b; font-weight: bold; border: 1px solid #cbd5e1; padding: 6px 4px; text-align: left; }
        td { border: 1px solid #cbd5e1; padding: 6px 4px; vertical-align: top; }
        
        .font-bold { font-weight: bold; }
        .text-center { text-align: center; }
        .bg-light { background-color: #f8fafc; }
        
        .footer { position: fixed; bottom: -30px; left: 0; right: 0; height: 30px; text-align: center; font-size: 8px; color: #94a3b8; }
        .page-number:after { content: counter(page); }
        
        .signature-table { width: 100%; margin-top: 40px; border: none; }
        .signature-table td { border: none; width: 50%; text-align: center; }
        
        .badge { padding: 2px 5px; border-radius: 3px; font-size: 8px; font-weight: bold; text-transform: uppercase; }
        .badge-success { background-color: #dcfce7; color: #166534; }
        
        /* Page break after each major section if needed */
        .page-break { page-break-after: always; }
    </style>
</head>
<body>
    <div class="header">
        <h1>LAPORAN ADMINISTRASI DESA</h1>
        <h2>DESA {{ strtoupper($desa->nama_desa) }} - KECAMATAN {{ strtoupper(appProfile()->region_name) }}</h2>
        <p style="margin: 5px 0 0; font-size: 10px;">Periode Pelaporan: {{ $date }}</p>
    </div>

    <!-- Section A: Personil -->
    <div class="section-title">A. Data Kepala Desa & Perangkat Desa</div>
    <table>
        <thead>
            <tr>
                <th style="width: 25px;">NO</th>
                <th>NAMA LENGKAP</th>
                <th>NIK</th>
                <th>JABATAN</th>
                <th>NOMOR SK</th>
                <th>MASA JABATAN</th>
                <th>STATUS</th>
            </tr>
        </thead>
        <tbody>
            @foreach($personil as $index => $p)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="font-bold">{{ $p->nama }}</td>
                <td>{{ $p->nik }}</td>
                <td>{{ $p->jabatan }}</td>
                <td>{{ $p->nomor_sk }}</td>
                <td>{{ $p->masa_jabatan_mulai ? $p->masa_jabatan_mulai->format('d/m/Y') : '-' }} s/d {{ $p->masa_jabatan_selesai ? $p->masa_jabatan_selesai->format('d/m/Y') : 'Sekarang' }}</td>
                <td class="text-center"><span class="badge badge-success">{{ $p->status }}</span></td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Section B: BPD -->
    <div class="section-title">B. Data Pimpinan & Anggota BPD</div>
    <table>
        <thead>
            <tr>
                <th style="width: 25px;">NO</th>
                <th>NAMA LENGKAP</th>
                <th>NIK</th>
                <th>JABATAN</th>
                <th>NOMOR SK</th>
                <th>MASA KEANGGOTAAN</th>
            </tr>
        </thead>
        <tbody>
            @forelse($bpd as $index => $b)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="font-bold">{{ $b->nama }}</td>
                <td>{{ $b->nik }}</td>
                <td>{{ $b->jabatan }}</td>
                <td>{{ $b->nomor_sk }}</td>
                <td>{{ $b->masa_jabatan_mulai ? $b->masa_jabatan_mulai->format('d/m/Y') : '-' }} s/d {{ $b->masa_jabatan_selesai ? $b->masa_jabatan_selesai->format('d/m/Y') : 'Sekarang' }}</td>
            </tr>
            @empty
            <tr><td colspan="6" class="text-center">Data BPD belum diinput</td></tr>
            @endforelse
        </tbody>
    </table>

    <!-- Section C: Lembaga -->
    <div class="section-title">C. Registrasi Lembaga Kemasyarakatan Desa</div>
    <table>
        <thead>
            <tr>
                <th style="width: 25px;">NO</th>
                <th>NAMA LEMBAGA</th>
                <th>KETUA / PIMPINAN</th>
                <th>NOMOR SK</th>
                <th>JUMLAH PENGURUS</th>
                <th>STATUS</th>
            </tr>
        </thead>
        <tbody>
            @forelse($lembaga as $index => $l)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="font-bold">{{ $l->nama_lembaga }}</td>
                <td>{{ $l->ketua }}</td>
                <td>{{ $l->nomor_sk }}</td>
                <td class="text-center">{{ $l->jumlah_pengurus }}</td>
                <td class="text-center">{{ $l->status_label }}</td>
            </tr>
            @empty
            <tr><td colspan="6" class="text-center">Data lembaga belum diinput</td></tr>
            @endforelse
        </tbody>
    </table>

    <!-- Section I: Siltap -->
    <div class="section-title">I. Penghasilan Tetap (Siltap) Perangkat Desa</div>
    <table>
        <thead>
            <tr>
                <th>DESKRIPSI</th>
                <th>ANGGARAN (PAGU)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="font-bold">Total Pagu Siltap Tahunan</td>
                <td class="font-bold">Rp {{ number_format($desa->pagu_siltap ?? 0, 0, ',', '.') }}</td>
            </tr>
            <tr class="bg-light">
                <td colspan="2" style="font-size: 9px; font-style: italic;">
                    * Data ini merupakan alokasi dana untuk penghasilan tetap Kepala Desa, Perangkat Desa, dan Tunjangan BPD.
                </td>
            </tr>
        </tbody>
    </table>

    <!-- Section D, F, G, H Summary Table -->
    <div class="section-title">D, E, F, G, H. Ringkasan Inventaris & Dokumen</div>
    <table>
        <thead>
            <tr>
                <th>KATEGORI MODUL</th>
                <th>JUMLAH DATA</th>
                <th>KETERANGAN TERAKHIR</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="font-bold">D. Arsip Perencanaan Desa (Musrenbang)</td>
                <td class="text-center">{{ $perencanaan->count() }} Dokumen</td>
                <td>{{ $perencanaan->last() ? 'Terakhir: ' . $perencanaan->last()->tahun : '-' }}</td>
            </tr>
            <tr>
                <td class="font-bold">E. Monitoring LKPJ & LPPD</td>
                <td class="text-center">{{ $dokumen->whereIn('tipe_dokumen', ['LKPJ', 'LPPD'])->count() }} Laporan</td>
                <td>Laporan tahunan pertanggungjawaban Kepala Desa</td>
            </tr>
            <tr>
                <td class="font-bold">F. Administrasi Inventaris & Aset</td>
                <td class="text-center">{{ $inventaris->count() }} Item</td>
                <td>{{ $inventaris->sum('harga_perolehan') > 0 ? 'Total Nilai: Rp ' . number_format($inventaris->sum('harga_perolehan'), 0, ',', '.') : '-' }}</td>
            </tr>
            <tr>
                <td class="font-bold">G & H. Arsip Dokumen & Peraturan Desa</td>
                <td class="text-center">{{ $dokumen->whereIn('tipe_dokumen', ['RPJMDes', 'RKPDes', 'Perdes'])->count() }} File</td>
                <td>Mencakup SK, Perdes, dan Peraturan Kepala Desa</td>
            </tr>
        </tbody>
    </table>

    <div class="signature-table">
        <table style="border: none;">
            <tr>
                <td>
                    Mengetahui,<br>
                    <strong>CAMAT {{ strtoupper(appProfile()->region_name) }}</strong>
                    <br><br><br><br><br>
                    ( ............................................ )
                </td>
                <td>
                    {{ appProfile()->region_name }}, {{ $date }}<br>
                    <strong>Kepala Seksi Pemerintahan</strong>
                    <br><br><br><br><br>
                    ( ............................................ )
                </td>
            </tr>
        </table>
    </div>

    <div class="footer">
        Halaman <span class="page-number"></span> - Laporan Administrasi Desa {{ $desa->nama_desa }} - Dicetak via KecamatanSAE
    </div>
</body>
</html>
