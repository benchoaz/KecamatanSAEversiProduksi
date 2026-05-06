<!DOCTYPE html>
<html>
<head>
    <title>{{ $title }}</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 11px; color: #333; margin: 0; padding: 0; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #444; padding-bottom: 10px; }
        .header h1 { font-size: 18px; margin: 0; color: #000; text-transform: uppercase; }
        .header p { margin: 5px 0 0; font-size: 12px; color: #666; }
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th { background-color: #f8fafc; color: #1e293b; font-weight: bold; border: 1px solid #e2e8f0; padding: 10px 5px; text-transform: uppercase; font-size: 9px; }
        td { border: 1px solid #e2e8f0; padding: 8px 5px; text-align: center; }
        
        .text-left { text-align: left; padding-left: 10px; }
        .font-bold { font-weight: bold; }
        .bg-gray { background-color: #f1f5f9; }
        
        .footer { position: fixed; bottom: -30px; left: 0; right: 0; height: 30px; text-align: center; font-size: 9px; color: #94a3b8; }
        .page-number:after { content: counter(page); }
        
        .summary-box { margin-top: 20px; padding: 15px; border: 1px solid #e2e8f0; background-color: #f8fafc; border-radius: 5px; }
        .summary-box h3 { margin: 0 0 10px; font-size: 12px; border-bottom: 1px solid #cbd5e1; padding-bottom: 5px; }
        
        .timestamp { text-align: right; font-size: 9px; color: #64748b; margin-top: 5px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $title }}</h1>
        <p>PEMERINTAH KABUPATEN {{ strtoupper(appProfile()->region_name) }}</p>
        <p>KANTOR KECAMATAN {{ strtoupper(appProfile()->region_name) }} - SEKSI PEMERINTAHAN</p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 30px;">NO</th>
                <th class="text-left">NAMA DESA</th>
                <th>KADES & PERANGKAT (A)</th>
                <th>ANGGOTA BPD (B)</th>
                <th>LEMBAGA DESA (C)</th>
                <th>ARSIP PERENCANAAN (D)</th>
                <th>MONITORING LAPORAN (E)</th>
                <th>INVENTARIS ASET (F)</th>
                <th>DOKUMEN RPJM/RKP (G)</th>
                <th>PERATURAN DESA (H)</th>
                <th>PAGU SILTAP (I)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $index => $item)
                <tr class="{{ $index % 2 == 0 ? '' : 'bg-gray' }}">
                    <td>{{ $index + 1 }}</td>
                    <td class="text-left font-bold">{{ strtoupper($item['nama']) }}</td>
                    <td>{{ $item['personil'] }}</td>
                    <td>{{ $item['bpd'] }}</td>
                    <td>{{ $item['lembaga'] }}</td>
                    <td>{{ $item['perencanaan'] }}</td>
                    <td>{{ $item['submission'] }}</td>
                    <td>{{ $item['inventaris'] }}</td>
                    <td>{{ $item['dokumen_perencanaan'] }}</td>
                    <td>{{ $item['peraturan'] }}</td>
                    <td class="text-left">Rp {{ number_format($item['pagu_siltap'], 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot class="bg-gray font-bold">
            <tr>
                <td colspan="2">TOTAL KECAMATAN</td>
                <td>{{ collect($data)->sum('personil') }}</td>
                <td>{{ collect($data)->sum('bpd') }}</td>
                <td>{{ collect($data)->sum('lembaga') }}</td>
                <td>{{ collect($data)->sum('perencanaan') }}</td>
                <td>{{ collect($data)->sum('submission') }}</td>
                <td>{{ collect($data)->sum('inventaris') }}</td>
                <td>{{ collect($data)->sum('dokumen_perencanaan') }}</td>
                <td>{{ collect($data)->sum('peraturan') }}</td>
                <td class="text-left">Rp {{ number_format(collect($data)->sum('pagu_siltap'), 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="summary-box">
        <h3>Informasi Laporan</h3>
        <p>Laporan ini merupakan konsolidasi data dari seluruh modul administrasi (A-H) yang telah diinput oleh operator desa dan divalidasi oleh Seksi Pemerintahan Kecamatan.</p>
        <div class="timestamp">Dicetak pada: {{ $date }} | Sistem KecamatanSAE</div>
    </div>

    <div class="footer">
        Halaman <span class="page-number"></span> - {{ $title }} - {{ appProfile()->region_name }}
    </div>
</body>
</html>
