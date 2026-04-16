{!! $total_row ?? '0' !!}
<div class="table-responsive">
    <table class="table table-bordered mb-0 table-hover tablesorter">
		<thead class="bg-primary text-white">
			<tr>
			  	<th class="text-center" width="5%">No.</th>
				<th class="text-center" width="35%">Nama</th>
				<th class="text-center" width="15%">NPM</th>
				<th class="text-center" width="20%">Status</th>
				<th class="text-center" width="25%">Cetak</th>
			</tr>
		</thead>
		<tbody>
            @if(count($query) > 0)
                @foreach($query as $index => $row)
                <tr class="mahasiswa_{{ $row->ID }}">
                    <td class="text-center">{{ ++$offset }}.</td>
                    <td>
                        <div class="media thumbnail">
                            {!! get_photo($row->NPM, $row->Foto, $row->Kelamin, 'mahasiswa') !!}
                            <div class="media-body ml-2">
                                <a href="{{ url('mahasiswa/view/'.$row->ID) }}" >{{ $row->Nama }}</a>
                            </div>
                        </div>
                    </td>
                    <td class="text-center">{{ $row->NPM }}</td>
                    <td class="text-center">{{ get_field($row->StatusMhswID, 'statusmahasiswa') }}</td>
                    <td class="text-center">
                        <button type="button" class="btn btn-danger btn-sm waves-effect waves-light" onclick="opnmdl({{ $row->ID }})">
                            <i class="mdi mdi-printer"></i> Transkrip Sementara
                        </button>
                    </td>
                </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="5" class="text-center">Data tidak ditemukan</td>
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

<!-- Modal Info Transkrip -->
<div id="mdla" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="mdlaLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="mdlaLabel">Informasi Cetak Transkrip</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body" id="mdla-body">
                <!-- Content will be loaded via AJAX -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success waves-effect waves-light" onclick="savet()">Transkrip Sementara</button>
                <button type="button" class="btn btn-secondary waves-effect" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
function opnmdl(ID) {
    $("#mdla-body").load("{{ url('rencanastudi/loadinfo') }}/" + ID, function() {
        $("#mdla").modal('show');
    });
}

function savet() {
    var tgl2 = $("#tgl2").val();
    var ID = $("#IDDD").val();

    if(!tgl2) {
        swal("Peringatan", "Harap isi Tanggal Cetak", "warning");
        return;
    }

    window.open("{{ url('rencanastudi/cetak') }}/" + ID + "/SEMENTARA/1?tgl2=" + tgl2, "_Blank");
}

if(typeof tablesorter === 'function') {
    tablesorter();
}
</script>
