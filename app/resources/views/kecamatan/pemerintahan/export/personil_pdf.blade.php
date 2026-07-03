<!DOCTYPE html>
<html>
<head>
    <title>{{ $title }}</title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 10px; color: #333; margin: 0; padding: 0; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        .header h1 { font-size: 15px; margin: 0; color: #000; text-transform: uppercase; }
        .header h2 { font-size: 13px; margin: 5px 0 0; color: #444; }
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; page-break-inside: auto; }
        th { background-color: #f1f5f9; color: #1e293b; font-weight: bold; border: 1px solid #cbd5e1; padding: 6px 4px; text-align: left; }
        td { border: 1px solid #cbd5e1; padding: 6px 4px; vertical-align: top; }
        
        .font-bold { font-weight: bold; }
        .text-center { text-align: center; }
        
        .signature-table { width: 100%; margin-top: 40px; border: none; }
        .signature-table td { border: none; width: 50%; text-align: center; }
        
        .badge { padding: 2px 5px; border-radius: 3px; font-size: 8px; font-weight: bold; text-transform: uppercase; }
        .badge-success { background-color: #dcfce7; color: #166534; }
        .badge-danger { background-color: #fee2e2; color: #991b1b; }
        
        .footer { position: fixed; bottom: -30px; left: 0; right: 0; height: 30px; text-align: center; font-size: 8px; color: #94a3b8; }
        .page-number:after { content: counter(page); }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ strtoupper($title) }}</h1>
        <h2>KECAMATAN {{ strtoupper(appProfile()->region_name) }}</h2>
        <p style="margin: 5px 0 0; font-size: 9px;">Tanggal Unduh: {{ $date }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 25px; text-align: center;">NO</th>
                <th>NAMA LENGKAP</th>
                <th>NIK</th>
                <th>JABATAN</th>
                <th>NOMOR SK / TANGGAL SK</th>
                <th>MASA JABATAN</th>
                <th style="width: 60px; text-align: center;">STATUS</th>
            </tr>
        </thead>
        <tbody>
            @forelse($personils as $index => $p)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td class="font-bold">{{ $p->nama }}</td>
                <td>{{ $p->nik }}</td>
                <td>{{ $p->jabatan }}</td>
                <td>
                    {{ $p->nomor_sk ?? '-' }}<br>
                    <small style="color: #64748b;">Tgl SK: {{ $p->tanggal_sk ? $p->tanggal_sk : '-' }}</small>
                </td>
                <td>
                    {{ $p->masa_jabatan_mulai ? $p->masa_jabatan_mulai : '-' }}
                </td>
                <td class="text-center">
                    @if($p->is_active)
                        <span class="badge badge-success">Aktif</span>
                    @else
                        <span class="badge badge-danger">Non-Aktif</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center">Belum ada data.</td>
            </tr>
            @endforelse
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
        Halaman <span class="page-number"></span> - Laporan Administrasi {{ $kategori === 'bpd' ? 'BPD' : 'Perangkat Desa' }} {{ $desa->nama_desa }} - Dicetak via KecamatanSAE
    </div>
</body>
</html>
