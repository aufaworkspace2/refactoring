<p>{!! $total_row ?? 0 !!}</p>
<div class="table-responsive">
    <table class="table table-bordered mb-0 table-hover tablesorter">
		<thead class="bg-primary text-white">
			<tr>
			  	<th class="text-center" width="2%">No.</th>
				<th class="text-center" width="23%">Nama</th>
				<th class="text-center" width="10%">NPM</th>
				<th class="text-center" width="10%">Tahun Masuk</th>
				<th class="text-center" width="10%">Program</th>
				<th class="text-center" width="15%">Prodi</th>
				<th class="text-center" width="5%">Jenjang</th>
				<th class="text-center" width="5%">Kelas</th>
				<th class="text-center" width="5%">Status</th>
				<th class="text-center" width="10%">Aksi</th>
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
                    <td class="text-center"><span class="badge badge-secondary">{{ $row->TahunMasuk }}</span></td>
                    <td>{{ get_field($row->ProgramID, 'program') }}</td>
                    <td>{{ get_field($row->ProdiID, 'programstudi') }}</td>
                    <td class="text-center">{{ get_field($row->JenjangID, 'jenjang') }}</td>
                    <td class="text-center">{{ get_field($row->KelasID, 'kelas') }}</td>
                    <td class="text-center">{{ get_field($row->StatusMhswID, 'statusmahasiswa') }}</td>
                    <td width="10%">
                        <button type="button" class="btn btn-danger text-white" onclick="opnmdl({{ $row->ID }})">
                            <i class="mdi mdi-download"></i> Perkembangan Akademik
                        </button>
                    </td>
                </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="10" class="text-center">Data tidak ditemukan</td>
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
                <button type="button" class="btn btn-primary waves-effect waves-light" onclick="savet()">Cetak</button>
                <button type="button" class="btn btn-secondary waves-effect" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
function opnmdl(ID) {
    $("#mdla-body").load("{{ url('perkembanganakademik/loadinfo') }}/" + ID, function() {
        $("#mdla").modal('show');
    });
}

function savet() {
    var nomor = $("#nomor").val();
    var bhs = $("#Bahasa").val();
    var tgl = $("#tgl").val();
    var tgl2 = $("#tgl2").val();
    var ID = $("#IDDD").val();
    var Dekan = $("#Dekan").val();
    var NIDN = $("#NIDN").val();
    
    if(!nomor || !tgl || !tgl2 || !Dekan || !NIDN) {
        swal("Peringatan", "Harap lengkapi semua isian bertanda *", "warning");
        return;
    }

    window.open("{{ url('perkembanganakademik/cetak') }}/" + ID + "/ASLI/" + bhs + "?nomor=" + nomor + "&tgl=" + tgl + "&Dekan=" + encodeURIComponent(Dekan) + "&NIDN=" + NIDN + "&tgl2=" + tgl2, "_Blank");
}

if(typeof tablesorter === 'function') {
    tablesorter();
}
</script>
