<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Delegasi Universitas Pilmapres</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #1f2937;
            line-height: 1.5;
        }

        .header {
            margin-bottom: 14px;
            padding-bottom: 10px;
            border-bottom: 2px solid #111827;
            text-align: center;
        }

        .header h2 {
            margin: 0;
            font-size: 16px;
        }

        .header p {
            margin: 4px 0 0;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
        }

        th,
        td {
            border: 1px solid #d1d5db;
            padding: 7px;
            text-align: left;
            vertical-align: top;
        }

        th {
            background: #f3f4f6;
            text-align: center;
        }

        .center {
            text-align: center;
        }

        .footer-note {
            margin-top: 16px;
            font-size: 11px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>Ucapan Selamat Delegasi Pilmapres Tingkat Universitas</h2>
        <p>Ditujukan kepada: {{ $recipientRoleLabel }}</p>
    </div>

    <p>
        Selamat! Anda telah dipercaya untuk melanjutkan peran di tingkat universitas.
        Berikut urutan peserta dan nilai sebagai informasi resmi saat surat ini diterbitkan.
    </p>

    <table>
        <thead>
            <tr>
                <th width="6%">No</th>
                <th width="18%">NIM</th>
                <th width="30%">Nama Peserta</th>
                <th width="24%">Fakultas</th>
                <th width="12%">Prodi</th>
                <th width="10%">Nilai</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rankings as $index => $registration)
                @php
                    $score = $registration->total_score_univ ?? $registration->total_score_fakultas ?? 0;
                @endphp
                <tr>
                    <td class="center">{{ $index + 1 }}</td>
                    <td class="center">{{ $registration->student->nim ?? '-' }}</td>
                    <td>{{ $registration->student->user->name ?? '-' }}</td>
                    <td>{{ $registration->student->faculty->name ?? '-' }}</td>
                    <td>{{ $registration->student->prodi ?? '-' }}</td>
                    <td class="center">{{ number_format((float) $score, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="center">Belum ada peserta pada tingkat universitas.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <p class="footer-note">
        Dokumen ini dibuat otomatis oleh sistem Pilmapres.
    </p>
</body>
</html>
