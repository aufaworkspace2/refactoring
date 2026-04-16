<link rel="stylesheet" type="text/css" href="{{ public_path('assets/theme/css/pdf.css') }}" />
{!! isset($cetak_header) ? cetak_header() : '' !!}
<h5>{{ __('DATA KATEGORI KEGIATAN SKPI') }}</h5>
<table>
    <tr>
        <th class="no">No.</th>
        <th style="width:70%;">{{ __('Nama') }}</th>
        <th style="width:30%;">Jenis Kategori</th>
    </tr>
</table>
@php
$no = 0; 
$a = 0;
@endphp
@foreach(($query ?? []) as $row)
@php
++$a;
$jenisKategori = \Illuminate\Support\Facades\DB::table('jenis_kategori_kegiatan')
    ->where('ID', $row['JenisKategoriID'] ?? 0)
    ->value('Nama');
@endphp
@if($a >= 37)
    {!! isset($cetak_header) ? cetak_header() : '' !!}
<table>
    <tr>
        <th class="no">No.</th>
        <th style="width:70%;">{{ __('Nama') }}</th>
        <th style="width:30%;">Jenis Kategori</th>
    </tr>
</table>
@php $a = 0; @endphp
@endif
<table>
    <tr class="kategori_kegiatan_{{ $row['ID'] ?? '' }}" >
        <td class="no">{{ ++$no }}.</td>
        <td style="width:70%;">{{ $row['Nama'] ?? '' }}</td>
        <td style="width:30%;">{{ $jenisKategori ?? '' }}</td>
    </tr>
</table>
@endforeach
