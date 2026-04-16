<link rel="stylesheet" type="text/css" href="{{ public_path('assets/theme/css/pdf.css') }}" />
{!! cetak_header() !!}
<h5>{{ strtoupper(__('slog')) }}</h5>
<table>
	<tr>
		<th class="no">No.</th>
		<th style="width:95%;">{{ __('Nama') }}</th>
        
	</tr>
</table>
@php 
$no=0; $a=0; 
@endphp
@foreach($query as $row) 
@php
++$a;
@endphp
@if($a >= 37) 
	{!! cetak_header() !!}
<table>
	<tr>
		<th class="no">No.</th>
		<th style="width:95%;">{{ __('Nama') }}</th>
        
	</tr>
</table>
@php $a=0; @endphp
@endif
<table>
	<tr class="level_{{ $row->ID }}" >
		<td class="no">{{ ++$no }}.</td>
		<td style="width:95%;">{{ $row->Nama }}</td>
        
	</tr>
</table>
@endforeach