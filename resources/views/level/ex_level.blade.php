<h5>{{ strtoupper(__('slog')) }}</h5>
<table border="1">
	<tr>
		<th class="no">No.</th>
		<th>{{ __('Nama') }}</th>
        
	</tr>
@php $no = 0; @endphp
@foreach($query as $row)
	<tr class="level_{{ $row->ID }}" >
		<td class="no">{{ ++$no }}.</td>
		<td>{{ $row->Nama }}</td>
        
	</tr>
@endforeach
</table>