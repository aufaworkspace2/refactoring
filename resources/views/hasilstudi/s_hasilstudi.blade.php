<p>{!! $total_row ?? 0 !!}</p>
<div class="table-responsive">
	<table class="table table-bordered mb-0 table-hover tablesorter">
		<thead class="bg-primary text-white">
			<tr>
				<th class="text-center" width="2%">No.</th>
				<th class="text-center" width="35%">Nama</th>
				<th class="text-center" width="10%">NPM</th>
				<th class="text-center" width="10%">Status Mahasiswa</th>
				<th class="text-center">Aksi</th>
			</tr>
		</thead>
		<tbody>
			@if(count($query) > 0)
                @foreach ($query as $row)
                    <tr class="mahasiswa_{{ $row->MhswID ?? '' }}">
                        <td class="text-center">{{ ++$offset }}.</td>
                        <td>
                            <div class="media thumbnail">
                                {!! get_photo($row->npmMahasiswa ?? '', $row->Foto ?? '', $row->Kelamin ?? '', 'mahasiswa') !!}
                                <div class="media-body ml-2">
                                    {{ $row->namaMahasiswa ?? '' }}
                                </div>
                            </div>
                        </td>
                        <td class="text-center">{{ $row->npmMahasiswa ?? '' }}</td>
                        <td class="text-center">{{ get_field($row->StatusMhswID ?? 0, 'statusmahasiswa') }}</td>
                        <td width="10%" class="text-center">
                            <a onclick="tampilkan_krs({{ $row->MhswID ?? 0 }})" class="btn btn-bordered-danger waves-effect waves-light text-white">Cetak KRS</a>
                        </td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="5" class="text-center">Tidak ada data sesuai filter</td>
                </tr>
            @endif
		</tbody>
	</table>
</div>
<div class="row mt-3">
	<div class="col-md-12">
		{!! $link ?? '' !!}
	</div>
</div>

<script>
	if(typeof tablesorter === 'function'){
        tablesorter();
    }
</script>
