<!DOCTYPE html>
<html>
<head>
    <title>Cetak Per Prodi</title>
    <style>
        p{
            margin: 0px;
            font-family: sans-serif;
            line-height: 15px;
        }
        .titlepaper{
            margin: 20px 0px 25px;
        }
        .infopaper{
            width: 100%;
            float: left;
        }
        .infopaper .detailpaper{
            width: 50%;
            float: left;
        }
        .infopaper .detailpaper2{
            width: 50%;
            float: right;
            text-align: left;
        }
        table.table-daftar-hadir{
            width: 100%;
            margin-top: 40px;
            border: 1px solid #000;
            border-spacing: 0px;
            margin-bottom: 40px;
        }
        table.table-daftar-hadir thead tr td{
            border-spacing: 0px;
            padding: 6px;
            text-align: center;
            font-weight: bold;
            border: 1px solid #000;
            border-top: 0px;
            border-left: 0px;
        }
        table.table-daftar-hadir thead tr td:first-child{
            width: 30px;
        }
        table.table-daftar-hadir thead tr td:last-child{
            border-right: 0px;
        }
        table.table-daftar-hadir tbody tr td{
            border-spacing: 0px;
            padding: 6px;
            text-align: left;
            border: 1px solid #000;
            border-top: 0px;
            border-left: 0px;
            vertical-align: top;
        }
        table.table-daftar-hadir tbody tr td:last-child{
            border-right: 0px;
        }
        table.table-daftar-hadir tbody tr:last-child td{
            border-bottom: 0px;
        }
        .ttd1{
            padding-top: 26px;
            text-align: center;
            border-bottom: 1px dotted #000;
            margin-bottom: 10px;
        }
        .ttd2{
            border-top: 1px solid #000;
            margin-left: -6px;
            margin-right: -6px;
            padding: 10px 10px 10px;
        }
        .dotted_ttd{
            width: 100%;
            border-bottom: 1px dotted #000;
            margin-top: 40px;
        }
        .rowthan .ttd1{
            padding-top: 27px;
        }
        .rowthan .dotted_ttd{
            margin-top: 45px;
        }
    </style>
</head>
<body>
    <table cellpadding="1" cellspacing="1" style="width: 100%;">
    <tbody>
        <tr>
            <td style="text-align: center;"><img alt="" src="{{ public_path('images/' . ($identitas->Gambar ?? '')) }}" style="width: 70px;margin-top:5px;margin-right:5px;" /></td>
            <td>
            <h2 style="text-align: left;margin:0;">{{ $identitas->NamaPT ?? '' }}</h2>

            <p style="text-align: left;">{{ $identitas->AlamatPT ?? '' }}</p>
            <p style="text-align: left;margin: 2px 0 0 0;font-size: 14px;">Telp : {{ $identitas->TeleponPT ?? '' }} Fax : {{ $identitas->FaxPT ?? '' }} Email : {{ $identitas->EmailPT ?? '' }}</p>
            </td>
        </tr>
    </tbody>
    </table>
    <hr />
    <table class="table-daftar-hadir" style="font-size:12px;">
        <thead>
            <tr>
                <td width="5%" style="border-spacing: 0px;">NO</td>
                <td width="20%">NO UJIAN</td>
                <td width="25%">NAMA PESERTA</td>
                <td width="25%" colspan="2">TANDA TANGAN</td>
            </tr>
        </thead>
        <tbody>
            @foreach($datalist as $p)
            <tr>
                <td style="text-align:center">{{ $p['no'] ?? '' }}</td>
                <td>{{ $p['noujian'] ?? '' }}</td>
                <td>{{ $p['nama'] ?? '' }}</td>
                <td>
                    @if(($p['no'] ?? 0) % 2 != 0)
                        {{ $p['no'] ?? '' }}
                    @endif
                </td>
                <td>
                    @if(($p['no'] ?? 0) % 2 == 0)
                        {{ $p['no'] ?? '' }}
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <table style="float:right">
        <tbody>
            <tr>
                <td align="center">PENGAWAS UJIAN</td>
            </tr>
            <tr>
                <td><div style="height:80px" /></td>
            </tr>
            <tr>
                <td><div style="border-bottom:2px solid #000; width:260px;" /></td>
            </tr>
        </tbody>
    <table>
</body>
</html>
