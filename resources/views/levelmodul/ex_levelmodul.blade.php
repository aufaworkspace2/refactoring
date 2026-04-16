<h5>DATA LEVEL MODUL</h5>
<table border="1">
	<tr>
		<th class="no">No.</th>
		<th>Level ID</th>
		<th>Modul ID</th>
		<th>Create</th>
		<th>Read</th>
		<th>Update</th>
		<th>Delete</th>
		<th>Shortcut</th>
		<th>Icon</th>
	</tr>
@php $no = 0; @endphp
@foreach($query as $row)
	<tr>
		<td class="no">{{ ++$no }}.</td>
		<td>{{ $row->LevelID }}</td>
		<td>{{ $row->ModulID }}</td>
		<td>{{ $row->Create }}</td>
		<td>{{ $row->Read }}</td>
		<td>{{ $row->Update }}</td>
		<td>{{ $row->Delete }}</td>
		<td>{{ $row->Shortcut }}</td>
		<td>{{ $row->Icon }}</td>
	</tr>
@endforeach
</table>
