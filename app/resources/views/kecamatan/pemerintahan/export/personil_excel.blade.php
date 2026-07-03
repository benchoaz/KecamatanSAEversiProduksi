<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        .title { font-family: sans-serif; font-size: 14pt; font-weight: bold; text-align: center; }
        .subtitle { font-family: sans-serif; font-size: 11pt; text-align: center; }
        .table-header { background-color: #1e293b; color: #ffffff; font-weight: bold; text-align: center; border: 1px solid #cbd5e1; }
        .table-cell { border: 1px solid #cbd5e1; font-family: sans-serif; font-size: 10pt; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .text-string { mso-number-format: "\@"; } /* Excel-specific format to keep NIK as string */
    </style>
</head>
<body>
    <table>
        <tr>
            <td colspan="7" class="title">{{ strtoupper($title) }}</td>
        </tr>
        <tr>
            <td colspan="7" class="subtitle">KECAMATAN {{ strtoupper(appProfile()->region_name) }}</td>
        </tr>
        <tr>
            <td colspan="7" class="subtitle">Tanggal Unduh: {{ $date }}</td>
        </tr>
        <tr>
            <td colspan="7"></td>
        </tr>
        <thead>
            <tr>
                <th class="table-header" style="width: 50px;">NO</th>
                <th class="table-header" style="width: 250px;">NAMA LENGKAP</th>
                <th class="table-header" style="width: 180px;">NIK</th>
                <th class="table-header" style="width: 200px;">JABATAN</th>
                <th class="table-header" style="width: 250px;">NOMOR SK</th>
                <th class="table-header" style="width: 150px;">TANGGAL SK</th>
                <th class="table-header" style="width: 150px;">STATUS</th>
            </tr>
        </thead>
        <tbody>
            @foreach($personils as $index => $p)
            <tr>
                <td class="table-cell text-center">{{ $index + 1 }}</td>
                <td class="table-cell font-bold">{{ $p->nama }}</td>
                <td class="table-cell text-string">{{ $p->nik }}</td>
                <td class="table-cell">{{ $p->jabatan }}</td>
                <td class="table-cell">{{ $p->nomor_sk ?? '-' }}</td>
                <td class="table-cell text-center">{{ $p->tanggal_sk ?? '-' }}</td>
                <td class="table-cell text-center" style="color: {{ $p->is_active ? '#166534' : '#991b1b' }}; font-weight: bold;">
                    {{ $p->is_active ? 'Aktif' : 'Non-Aktif' }}
                </td>
            </tr>
            @endforeach
        </tbody>
        <tr>
            <td colspan="7"></td>
        </tr>
        <tr>
            <td colspan="3" class="text-center" style="font-family: sans-serif; font-size: 10pt;">
                Mengetahui,<br>
                <strong>CAMAT {{ strtoupper(appProfile()->region_name) }}</strong>
                <br><br><br><br>
                ( ............................................ )
            </td>
            <td></td>
            <td colspan="3" class="text-center" style="font-family: sans-serif; font-size: 10pt;">
                {{ appProfile()->region_name }}, {{ $date }}<br>
                <strong>Kepala Seksi Pemerintahan</strong>
                <br><br><br><br>
                ( ............................................ )
            </td>
        </tr>
    </table>
</body>
</html>
