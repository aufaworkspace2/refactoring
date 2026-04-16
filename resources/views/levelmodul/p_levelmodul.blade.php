<link rel="stylesheet" type="text/css" href="{{ public_path('assets/theme/css/pdf.css') }}" />
{!! cetak_header() !!}
<h5>DATA LEVEL MODUL</h5>
<table>
	<tr>
		<th class="no">No.</th>
		<th style="width:15%;">Level ID</th>
		<th style="width:15%;">Modul ID</th>
		<th style="width:10%;">Create</th>
		<th style="width:10%;">Read</th>
		<th style="width:10%;">Update</th>
		<th style="width:10%;">Delete</th>
		<th style="width:10%;">Shortcut</th>
		<th style="width:10%;">Icon</th>
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
		<th style="width:15%;">Level ID</th>
		<th style="width:15%;">Modul ID</th>
		<th style="width:10%;">Create</th>
		<th style="width:10%;">Read</th>
		<th style="width:10%;">Update</th>
		<th style="width:10%;">Delete</th>
		<th style="width:10%;">Shortcut</th>
		<th style="width:10%;">Icon</th>
	</tr>
</table>
@php $a=0; @endphp
@endif
<table>
	<tr>
		<td class="no">{{ ++$no }}.</td>
		<td style="width:15%;">{{ $row->LevelID }}</td>
		<td style="width:15%;">{{ $row->ModulID }}</td>
		<td style="width:10%;">{{ $row->Create }}</td>
		<td style="width:10%;">{{ $row->Read }}</td>
		<td style="width:10%;">{{ $row->Update }}</td>
		<td style="width:10%;">{{ $row->Delete }}</td>
		<td style="width:10%;">{{ $row->Shortcut }}</td>
		<td style="width:10%;">{{ $row->Icon }}</td>
	</tr>
</table>
@endforeach
