<!DOCTYPE html>
<html>
<head>
    <title>Surat Keputusan Pemenang Pilmapres</title>
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12px;
            color: #333;
            line-height: 1.5;
        }
        .header {
            text-align: center;
            border-bottom: 3px solid #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .header h2, .header h3, .header h4 {
            margin: 0;
            padding: 2px 0;
        }
        .title {
            text-align: center;
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 20px;
            text-transform: uppercase;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table, th, td {
            border: 1px solid #000;
        }
        th, td {
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            text-align: center;
        }
        .text-center {
            text-align: center;
        }
        .text-bold {
            font-weight: bold;
        }
        .footer {
            margin-top: 50px;
            width: 100%;
        }
        .signature-box {
            float: right;
            width: 300px;
            text-align: center;
        }
        .clear {
            clear: both;
        }
    </style>
</head>
<body>

    <div class="header">
        <h2>KEMENTERIAN PENDIDIKAN, KEBUDAYAAN, RISET, DAN TEKNOLOGI</h2>
        <h3>UNIVERSITAS MATARAM</h3>
        <h4>PANITIA PEMILIHAN MAHASISWA BERPRESTASI (PILMAPRES) TAHUN 2026</h4>
        <p style="margin:0; font-size:10px;">Jl. Majapahit No.62, Mataram, Nusa Tenggara Barat</p>
    </div>

    <div class="title">
        DAFTAR PERINGKAT (LEADERBOARD) PILMAPRES<br>
        TAHAP {{ strtoupper($stage) }} {{ $facultyNameTitle ?? '' }}<br>
        TAHUN 2026
    </div>

    <table>
        <thead>
            <tr>
                <th width="5%">Peringkat</th>
                <th width="20%">NIM</th>
                <th width="40%">Nama Mahasiswa</th>
                <th width="20%">Program Studi</th>
                <th width="15%">Skor AHP</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rankings as $index => $reg)
            <tr>
                <td class="text-center text-bold">{{ $index + 1 }}</td>
                <td class="text-center">{{ $reg->student->nim }}</td>
                <td>{{ $reg->student->user->name }}</td>
                <td class="text-center">{{ $reg->student->prodi }}</td>
                <td class="text-center text-bold">{{ number_format($reg->$scoreColumn, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <div class="signature-box">
            <p>Mataram, {{ date('d F Y') }}<br>Ketua Panitia Pilmapres,</p>
            <br><br><br><br>
            <p class="text-bold">.....................................................<br>NIP. ........................................</p>
        </div>
        <div class="clear"></div>
    </div>

</body>
</html>