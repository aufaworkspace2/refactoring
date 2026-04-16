@extends('layouts.template1')

@section('content')
<div class="card">
	<div class="card-body">
		<div class="form-row">
			<div class="form-group col-md-4">
				<label class="col-form-label"><h5 class="mb-0">Gelombang</h5></label>
				<select class="gelombang form-control" onchange="change_gelombang_detail_pmb()">
					<option value=""> -- {{ __('app.view_all') }} -- </option>
					@foreach($data_gelombang as $g)
						<option value="{{ $g->id }}" {{ $selected_gelombang == $g->id ? 'selected' : '' }}>
							{{ $g->kode }} || {{ $g->nama }}
						</option>
					@endforeach
				</select>
            </div>
            <div class="form-group col-md-8">
				<label class="col-form-label"><h5 class="mb-0">Gelombang Detail</h5></label>
				<select class="gelombang_detail form-control" onchange="filter()">
					<option value=""> -- {{ __('app.view_all') }} -- </option>
					@foreach($data_gelombang_detail as $gd)
						<option value="{{ $gd->id }}"
							{{ $selected_gelombang_detail == $gd->id ? 'selected' : '' }}
							data-gelombang-id="{{ $gd->gelombang_id }}">
							{{ $gd->nama }}
						</option>
					@endforeach
				</select>
            </div>

            <div class="form-group col-md-6">
				<label class="col-form-label"><h5 class="mb-0">Urutkan dengan</h5></label>
				<div class="row">
					<div class="col-md-6">
						<select name="orderby" class="form-control orderby" id="orderby"  onchange="filter()">
							<option value="mahasiswa.urutall_lulus_pmb" {{ $selected_orderby == 'mahasiswa.urutall_lulus_pmb' ? 'selected' : '' }}>Urut Set Lulus</option>
							<option value="mahasiswa.Nama" {{ $selected_orderby == 'mahasiswa.Nama' ? 'selected' : '' }}>Nama</option>
							<option value="mahasiswa.noujian_pmb" {{ $selected_orderby == 'mahasiswa.noujian_pmb' ? 'selected' : '' }}>No Ujian</option>
							<option value="mahasiswa.nilai_pmb" {{ $selected_orderby == 'mahasiswa.nilai_pmb' ? 'selected' : '' }}>Nilai</option>
						</select>
					</div>
					<div class="col-md-6">
						<select name="descasc" class="form-control descasc" id="descasc" onchange="filter()">
							<option value="DESC" {{ $selected_descasc == 'DESC' ? 'selected' : '' }}>Z-A</option>
							<option value="ASC" {{ $selected_descasc == 'ASC' ? 'selected' : '' }}>A-Z</option>
						</select>
					</div>
				</div>
            </div>

            <div class="form-group col-md-6">
            	<label class="col-form-label"><h5 class="mb-0">{{ __('app.keyword_legend') }}</h5></label>
				<div class="row">
					<div class="col-md-3">
						<select name="viewpage" class="form-control viewpage" id="viewpage" onchange="filter()">
							<option value="10" {{ $selected_viewpage == '10' ? 'selected' : '' }}>10</option>
							<option value="25" {{ $selected_viewpage == '25' ? 'selected' : '' }}>25</option>
							<option value="50" {{ $selected_viewpage == '50' ? 'selected' : '' }}>50</option>
							<option value="100" {{ $selected_viewpage == '100' ? 'selected' : '' }}>100</option>
						</select>
					</div>
					<div class="col-md-9">
						<input type="text" class="form-control keyword" onkeyup="filter()" placeholder="{{ __('app.keyword') }} .."/>
					</div>
				</div>
            </div>
		</div>
	</div>
</div>
<div class="card">
	<div class="card-body">
		<div id="konten"></div>
	</div>
</div>
@endsection

@push('scripts')
<script type="text/javascript">
$.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

// Form submit handler - using event delegation for AJAX-loaded content
$(document).off('submit', '#f_save_set_draft_registrasiulang');
$(document).on('submit', '#f_save_set_draft_registrasiulang', function(e){
	e.preventDefault();
	e.stopPropagation();
	
	$.ajax({
		type: "POST",
		url: $(this).attr('action'),
		data: $(this).serialize(),
		dataType: 'json',
		beforeSend:function(data){
			$('#btnSubmit').prop('disabled',true);
			$('#btnSubmit').html("<i class='fa fa-spin fa-spinner'></i> Loading...");
		},
		success:function(response_json){
			console.log('Response:', response_json);
			let data = response_json.statuspesan;
			if(data == '0' || data == 0){
				swal('Gagal', response_json.message || 'Data gagal diupdate', 'error');
				filter("{{ url('set_draft_registrasiulang/search') }}");
				return;
			}
			if(data == 'tidak ada tagihan'){
				swal('Gagal','Tidak Dapat set draft tagihan karena belum ada biaya yang di setting sesuai gelombang mahasiswa','warning');
				filter("{{ url('set_draft_registrasiulang/search') }}");
				return;
			}

			if(data == 'diskon double'){
				swal('Gagal',response_json.message,'warning');
				filter("{{ url('set_draft_registrasiulang/search') }}");
				return;
			}

			$("#hapus").modal("hide");
			setTimeout(function(){
				$("body").removeClass("modal-open");
				$(".modal-backdrop").remove();
			}, 300);

            $( ".alert-success" ).animate({
					backgroundColor: "#dff0d8"
			}, 1000 );
			$( ".alert-success" ).animate({
					backgroundColor: "#b6ef9e"
			}, 1000 );
			$( ".alert-success" ).animate({
					backgroundColor: "#dff0d8"
			}, 1000 );
			$( ".alert-success" ).animate({
					backgroundColor: "#b6ef9e"
			}, 1000 );

			$(".alert-success").show();
			$(".alert-success-content").html("{{ __('app.alert-success') }}");
			window.setTimeout(function() { $(".alert-success").slideUp(); }, 10000);
			filter("{{ url('set_draft_registrasiulang/search') }}");
		},
		error: function(xhr, status, error){
			console.log('Error:', xhr.responseText);
			// Handle validation error
			if(xhr.status === 422) {
				let errors = xhr.responseJSON.errors;
				let errorMsg = '';
				$.each(errors, function(key, value) {
					errorMsg += value[0] + '<br>';
				});
				swal('Gagal', errorMsg, 'error');
			} else {
				swal('Gagal', 'Terjadi kesalahan saat update data', 'error');
			}
			filter("{{ url('set_draft_registrasiulang/search') }}");
		}
	});
	
	return false;
});

function show_btnSubmit() {
    i = 0;
    hasil = false;
    while (document.getElementsByName('checkID[]').length > i) {
        var checkname = document.getElementById('checkID' + i);

        if (checkname && checkname.checked) {
            hasil = true;
        }
        i++;
    }
    var action_do = $('#action_do').val();
    if (hasil == true && action_do != '') {
        $('#btnSubmit').removeAttr('disabled');
        $('#btnSubmit').removeAttr('title');
    } else {
        $('#btnSubmit').attr('disabled', 'disabled');
        $('#btnSubmit').attr('title', 'Pilih dahulu data yang akan di simpan');
    }
}

function checkall(chkAll,checkid) {
    if (checkid != null) {
        if (checkid.length == null) checkid.checked = chkAll.checked;
        else for (i=0;i<checkid.length;i++) checkid[i].checked = chkAll.checked;
        $("input:checkbox[name='checkID[]']").parents('tr').removeClass('table-danger');
        $("input:checkbox[name='checkID[]']:checked").parents('tr').addClass('table-danger');
    }
}

function filter(url) {
	if(url == null)
	url = "{{ url('set_draft_registrasiulang/search') }}";

	$.ajax({
		type: "POST",
		url: url,
		data: {
			keyword : $(".keyword").val(),
			bayar : '{{$bayar}}',
			gelombang : $(".gelombang").val(),
			gelombang_detail : $(".gelombang_detail").val(),
			pilihan1 : $(".pilihan1").val(),
			pilihan2 : $(".pilihan2").val(),
			program : $(".ProgramID").val(),
			orderby : $(".orderby").val(),
			descasc : $(".descasc").val(),
			viewpage : $(".viewpage").val(),
		},
		beforeSend:function(data){
			$('#konten').html('<center><i class="fa fa-spin fa-spinner"></i> Silahkan Tunggu...</center>');
		},
		success: function(data) {
			$("#konten").html(data);
			// Call tablesorter after AJAX load
			if(typeof tablesorter === 'function') {
				tablesorter();
			}
			// Re-bind checkbox click handlers after AJAX load
			$(document).off('click', 'input:checkbox[name="checkID[]"]');
			$(document).on('click', 'input:checkbox[name="checkID[]"]', function(){
				if(this.checked == true){ $(this).parents('tr').addClass('table-danger'); }
				else { $(this).parents('tr').removeClass('table-danger'); }
				show_btnSubmit();
			});
			// Initialize button state
			show_btnSubmit();
		}
	});
	return false;
}

function change_gelombang_detail_pmb()
{
	$.ajax({
		url:"{{ url('gelombang_pmb/change_gelombang_detail_pmb') }}",
		type:"POST",
		data: {
			gelombang : $(".gelombang").val()
		},
		success: function(data){
			if($(".gelombang").val() == '') {
				$('.gelombang_detail').attr('disabled',true);
			}
			else {
				$('.gelombang_detail').removeAttr('disabled');
			}
			$(".gelombang_detail").html(data);
			filter();
		}
	});
}
change_gelombang_detail_pmb();
</script>
@endpush
