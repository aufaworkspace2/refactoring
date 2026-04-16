<p>{!! $total_row !!}</p>
<form id="f_delete_level" action="{{ url('level/delete') }}" >
@csrf
<div class="table-responsive">
	<table class="table table-bordered mb-0 table-hover tablesorter">
		<thead class="bg-primary text-white">
			<tr>
				<th>
					<div class="checkbox checkbox-info">
						<input type="checkbox" name="checkAll" id="checkAll" onClick="checkall(this,document.forms.namedItem('f_delete_level')); show_btnDelete();">
						<label for="checkAll"></label>
					</div>
				</th>
				<th class="text-center" width="2%">No.</th>
				<th class="text-center" width="2%">No Urut</th>
				<th width="80%">Nama Level</th>
				<th class="text-center" width="16%">Level Modul</th>
			</tr>
		</thead>
		<tbody>
		@php $no = $offset; $i = 0; @endphp
        @foreach($query as $row)
			<tr class="level_{{ $row->ID }}">
				<td>
					@if($row->Nama != 'SUPER USER' && $row->Nama != 'Dosen KBK' && $row->Nama != 'ADMINISTRATOR' && $row->Nama != 'Staff')
						<div class="checkbox checkbox-info">
							<input type="checkbox" name="checkID[]" id="checkID{{$i}}" onclick="show_btnDelete()" value="{{ $row->ID }}" >
							<label for="checkID{{$i}}"></label>
						</div>
					@php $i++; @endphp
                    @endif
				</td>
				<td class="text-center">{{ ++$no }}.</td>
				<td class="text-center">{{ $row->Urut }}</td>
				<td>
					@if($row->Nama != 'SUPER USER' && $row->Nama != 'Dosen KBK' && $row->Nama != 'ADMINISTRATOR'  && $row->Nama != 'Staff')
					    <a href="{{ url(request()->segment(1) . '/view/' . $row->ID) }}" >{{ $row->Nama }}</a>
					@else
					    {{ $row->Nama }}
					@endif
				</td>
				<td class="text-center">
					@if(($row->Nama != 'SUPER USER' && $row->Nama != 'Dosen KBK' && $row->Nama != 'ADMINISTRATOR'  && $row->Nama != 'Staff') || (isset($_SESSION['devmode']) && $_SESSION['devmode'] == 1))
						<a href="{{ url('levelmodul?level=' . $row->ID) }}" class="btn btn-primary waves-effect waves-light btn-sm"><i class="mdi mdi-format-list-bulleted"></i> Level Modul</a>
					@else
						-
					@endif
				</td>
			</tr>
        @endforeach
		</tbody>
	</table>

    <div id="hapus" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="hapus" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title" id="hapus">Konfirmasi Penghapusan</h4>
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				</div>
				<div class="modal-body">
					<p>Apakah Anda yakin ingin menghapus data yang dipilih?</p>
					<p class="data_name"></p>
				</div>
				<div class="modal-footer">
					<button type="submit" class="btn btn-danger waves-effect" >Ya, Hapus!</button>
					<button type="button" class="btn btn-primary waves-effect waves-light" data-dismiss="modal">Tutup</button>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="row">
	<div class="col-md-12">
		{!! $link !!}
	</div>
</div>
</form>

<script>
tablesorter();

$("#f_delete_level").submit(function(e){
    e.preventDefault(); // Tambahkan ini agar form tidak tersubmit ganda

    $.ajax({
        type: "POST",
        url: $(this).attr('action'),
        data: $(this).serialize(),
        dataType: 'json',
        success:function(response){
            // Remove rows based on response
            var checkID = [];
            $("input:checkbox[name='checkID[]']:checked").each(function() {
                checkID.push($(this).val());
            });
            
            checkID.forEach(function(id) {
                $('.level_' + id).remove();
            });
            
            $("#hapus").modal("hide");

            setTimeout(function() {
                $('body').removeClass('modal-open');
                $('.modal-backdrop').remove();
            }, 300);

            alertsuccess("Data Berhasil Dihapus!");

            filter();
        },
        error: function(data){
            $("#hapus").modal("hide");
            setTimeout(function() {
                $('body').removeClass('modal-open');
                $('.modal-backdrop').remove();
            }, 300);

            alertfail("Terjadi kesalahan, gagal menghapus data!");
        }
    });
    return false;
});

window.show_btnDelete = function(){
	var i = 0;
    var hasil = false;
	var checkElements = document.getElementsByName('checkID[]');
	while(checkElements.length > i) {
		var checkname = document.getElementById('checkID'+i);
		if(checkname && checkname.checked) {
			hasil = true;
		}
		i++;
	}
	if(hasil == true) {
		if($('#btnDelete').length) {
			$('#btnDelete').removeAttr('disabled');
			$('#btnDelete').removeAttr('href');
			$('#btnDelete').removeAttr('title');
			$('#btnDelete').attr('href', '#hapus');
		}
	} else {
		if($('#btnDelete').length) {
			$('#btnDelete').attr('disabled','disabled');
			$('#btnDelete').attr('href','#');
			$('#btnDelete').attr('title', 'Pilih dahulu data yang akan di hapus');
		}
	}
}
show_btnDelete();

$("input:checkbox[name='checkID[]']").click(function(){
	if(this.checked == true){
		$(this).parents('tr').addClass('table-danger');
	} else {
		$(this).parents('tr').removeClass('table-danger');
	}
});

$('#btnDelete').click(function(){
	$.ajax({
		url : "{{ url('welcome/test') }}/?table=level&field=Nama",
		type: "POST",
		data: {
			checkID: $("input:checkbox[name='checkID[]']:checked").map(function(){
				return this.value;
			}).get(),
			_token: "{{ csrf_token() }}"
		},
		success: function(data){
			$('.data_name').html(data);
		}
	});
});
</script>