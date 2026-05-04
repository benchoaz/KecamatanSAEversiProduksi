<!DOCTYPE html>
<html>
<head>
    <title>Rekapitulasi Siltap Kecamatan</title>
    <style>
        @page { margin: 1cm; }
        body { font-family: 'Arial', sans-serif; font-size: 11pt; line-height: 1.3; color: #000; }
        
        /* Surat Pengantar Style */
        .kop-surat { text-align: center; border-bottom: 3px solid #000; padding-bottom: 10px; margin-bottom: 20px; position: relative; }
        .kop-surat img { position: absolute; left: 0; top: 0; width: 70px; }
        .kop-surat h2 { margin: 0; font-size: 14pt; font-weight: bold; }
        .kop-surat h1 { margin: 0; font-size: 16pt; font-weight: bold; }
        .kop-surat p { margin: 0; font-size: 10pt; }
        
        .nomor-surat { margin-top: 20px; margin-bottom: 20px; }
        .nomor-surat table { border: none; }
        .nomor-surat td { border: none; padding: 1px; vertical-align: top; }
        
        .tujuan-surat { margin-left: 50%; margin-bottom: 30px; }
        
        .isi-surat { text-align: justify; margin-bottom: 30px; }
        
        /* Table Style */
        .page-break { page-break-after: always; }
        .judul-halaman { text-align: center; font-weight: bold; font-size: 12pt; margin-bottom: 20px; text-transform: uppercase; }
        
        table.data-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table.data-table th, table.data-table td { border: 1px solid #000; padding: 5px; font-size: 9pt; }
        table.data-table th { background-color: #e9e9e9; text-align: center; font-weight: bold; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        
        .ttd-container { width: 100%; margin-top: 30px; }
        .ttd-box { width: 250px; float: right; text-align: center; }
        .ttd-space { height: 60px; }
    </style>
</head>
<body>
    <!-- Page 1: Surat Pengantar -->
    <div class="kop-surat">
        @if(appProfile()->logo)
            <img src="{{ public_path('storage/' . appProfile()->logo) }}">
        @endif
        <h2>PEMERINTAH KABUPATEN {{ strtoupper(appProfile()->kabupaten_name ?? 'PROBOLINGGO') }}</h2>
        <h1>KECAMATAN {{ strtoupper(appProfile()->kecamatan_name ?? 'BESUK') }}</h1>
        <p>{{ appProfile()->alamat ?? 'Jl. Raya Besuk Nomor 37 Besuk Probolinggo - 67283' }}</p>
        <p>Telp ({{ appProfile()->telepon ?? '0335' }}) 4514312 e-mail : {{ appProfile()->email ?? 'kecamatanbesuk317@gmail.com' }}</p>
    </div>

    <div style="text-align: right; margin-bottom: 20px;">
        {{ appProfile()->kecamatan_name ?? 'Besuk' }}, {{ date('d F Y') }}
    </div>

    <div class="nomor-surat">
        <table>
            <tr><td width="80">Nomor</td><td>: 400.10/ &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; /426.413/{{ date('Y') }}</td></tr>
            <tr><td>Sifat</td><td>: Penting</td></tr>
            <tr><td>Lampiran</td><td>: 1 (satu) Berkas</td></tr>
            <tr><td>Perihal</td><td>: Rekapitulasi Siltap Bulan {{ date('F Y') }}</td></tr>
        </table>
    </div>

    <div class="tujuan-surat">
        Yth. Sdr. Kepala Dinas Pemberdayaan<br>
        Masyarakat dan Desa<br>
        di -<br>
        <strong><u>K R A K S A A N</u></strong>
    </div>

    <div class="isi-surat">
        <p>Bersama ini disampaikan dengan hormat Daftar Rekapitulasi Siltap Perangkat Desa Se-Kecamatan {{ appProfile()->kecamatan_name ?? 'Besuk' }} Bulan {{ date('F Y') }} sebagaimana terlampir.</p>
        <p>Demikian untuk menjadi periksa.</p>
    </div>

    <div class="ttd-container">
        <div class="ttd-box">
            <p><strong>CAMAT {{ strtoupper(appProfile()->kecamatan_name ?? 'BESUK') }}</strong></p>
            <div class="ttd-space"></div>
            <p><strong><u>{{ appProfile()->camat_name ?? '..........................' }}</u></strong></p>
            <p>Pembina Tingkat I</p>
            <p>NIP. {{ appProfile()->camat_nip ?? '..........................' }}</p>
        </div>
        <div style="clear: both;"></div>
    </div>

    <div class="page-break"></div>

    <!-- Page 2: Tabel Rekapitulasi -->
    <div class="judul-halaman">
        SILTAP PERANGKAT DESA SE - KEC.{{ strtoupper(appProfile()->kecamatan_name ?? 'BESUK') }}<br>
        BULAN {{ strtoupper(date('F')) }} &nbsp;&nbsp; TAHUN ANGGARAN {{ date('Y') }}
    </div>

    <table class="data-table">
        <thead>
            <tr>
                <th width="30">NO</th>
                <th>DESA</th>
                <th>NO REKENING DESA</th>
                <th>PAGU SILTAP</th>
                <th>SILTAP BULAN {{ strtoupper(date('F')) }}</th>
                <th>KETERANGAN</th>
            </tr>
            <tr style="background-color: #f9f9f9; font-size: 7pt;">
                <th style="font-size: 8pt;">1</th>
                <th style="font-size: 8pt;">2</th>
                <th style="font-size: 8pt;">3</th>
                <th style="font-size: 8pt;">4</th>
                <th style="font-size: 8pt;">5</th>
                <th style="font-size: 8pt;">6</th>
            </tr>
        </thead>
        <tbody>
            @foreach($desas as $index => $desa)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td style="text-transform: uppercase;">{{ $desa->nama_desa }}</td>
                    <td class="text-center">{{ $desa->rekening_desa ?? '-' }}</td>
                    <td class="text-right">{{ number_format($desa->pagu_siltap ?? 0, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($desa->total_siltap ?? 0, 0, ',', '.') }}</td>
                    <td style="font-size: 8pt;">
                        KADES {{ $desa->kades_count }} ORANG<br>
                        SEKDES {{ $desa->sekdes_count }} ORANG<br>
                        PERANGKAT {{ $desa->staff_count }} ORANG
                    </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="font-weight: bold; background-color: #e9e9e9;">
                <td colspan="3" class="text-center">TOTAL</td>
                <td class="text-right">{{ number_format($desas->sum('pagu_siltap'), 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($desas->sum('total_siltap'), 0, ',', '.') }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    <div class="ttd-container">
        <div class="ttd-box">
            <div class="ttd-space"></div>
            <p><strong>CAMAT {{ strtoupper(appProfile()->kecamatan_name ?? 'BESUK') }}</strong></p>
            <div class="ttd-space"></div>
            <p><strong><u>{{ appProfile()->camat_name ?? '..........................' }}</u></strong></p>
            <p>Pembina Tingkat I</p>
            <p>NIP. {{ appProfile()->camat_nip ?? '..........................' }}</p>
        </div>
        <div style="clear: both;"></div>
    </div>
</body>
</html>
