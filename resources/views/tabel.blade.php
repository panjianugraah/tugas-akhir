<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">

    <title>{{ $judul }}</title>

    <style>
        @page {
            margin: 28px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #222;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #222;
            padding-bottom: 12px;
            margin-bottom: 16px;
        }

        .header h1 {
            font-size: 17px;
            margin: 0 0 5px;
        }

        .header h2 {
            font-size: 13px;
            margin: 0;
        }

        .informasi {
            margin-bottom: 14px;
            line-height: 1.7;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            display: table-header-group;
        }

        tr {
            page-break-inside: avoid;
        }

        th,
        td {
            border: 1px solid #aaa;
            padding: 7px;
            text-align: left;
            vertical-align: top;
            overflow-wrap: break-word;
        }

        th {
            background: #e8eef8;
        }

        .nomor {
            width: 28px;
            text-align: center;
        }

        .kosong {
            text-align: center;
            padding: 20px;
        }

        .footer {
            margin-top: 16px;
            font-size: 9px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>SMP Angkasa Adi Soemarmo</h1>
        <h2>{{ $judul }}</h2>
    </div>

    <div class="informasi">
        @foreach ($keterangan as $label => $nilai)
            <div>
                <strong>{{ $label }}:</strong> {{ $nilai }}
            </div>
        @endforeach

        <div>
            <strong>Jumlah data sesuai filter:</strong> {{ $jumlah }}
        </div>

        <div>
            <strong>Dicetak pada:</strong> {{ $dicetakPada }}
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th class="nomor">No</th>

                @foreach ($kolom as $namaKolom)
                    <th>{{ $namaKolom }}</th>
                @endforeach
            </tr>
        </thead>

        <tbody>
            @forelse ($baris as $row)
                <tr>
                    <td class="nomor">{{ $loop->iteration }}</td>

                    @foreach ($row as $nilai)
                        <td>{{ $nilai ?? '-' }}</td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td
                        colspan="{{ count($kolom) + 1 }}"
                        class="kosong"
                    >
                        Tidak ada data sesuai filter.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Dokumen dihasilkan melalui sistem informasi sekolah.
        Gunakan sesuai kebutuhan administrasi sekolah.
    </div>
</body>
</html>