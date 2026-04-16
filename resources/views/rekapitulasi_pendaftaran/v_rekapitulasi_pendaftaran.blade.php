@extends('layouts.template1')

@section('content')
<div class="card">
	<div class="card-body">
		<div class="row">
			<div class="col-md-12 mb-2">
				<div class="button-list">
					<div class="btn-group">
						<button type="button" class="btn btn-info dropdown-toggle waves-effect waves-light" data-toggle="dropdown" aria-expanded="false"><i class="mdi mdi-printer mr-1"></i> Cetak <i class="mdi mdi-chevron-down"></i></button>
						<div class="dropdown-menu">
							<a onclick="pdf()" href="javascript:void(0);" class="dropdown-item"><i class="mdi mdi-printer"></i> {{ __('app.pdf') }}</a>
							<a onclick="excel()" href="javascript:void(0);" class="dropdown-item"><i class="mdi mdi-printer"></i> {{ __('app.excel') }}</a>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-md-3">
				<label class="col-form-label "><h5 class="m-0">Gelombang</h5></label>
				<select class="gelombang form-control" onchange="change_gelombang_detail_pmb()">
					<option value=""> -- {{ __('app.view_all') }} -- </option>
					@foreach(get_all('pmb_tbl_gelombang') as $row)
						<option value="{{ $row->id }}">{{ $row->kode }} || {{ $row->nama }}</option>
					@endforeach
				</select>
			</div>
			<div class="form-group col-md-6">
				<label class="col-form-label "><h5 class="m-0">Gelombang Detail</h5></label>
				<select class="gelombang_detail form-control" onchange="filter()" >
					<option value=""> -- {{ __('app.view_all') }} -- </option>

				</select>
			</div>
			<div class="form-group col-md-3">
				<label class="col-form-label "><h5 class="m-0">Status</h5></label>
				<select class="status form-control" onchange="filter();" style="width: 100%">
					<option value="">-- {{ __('app.view_all') }} --</option>
					<option value="verifikasi" >Verifikasi</option>
					<option value="calon" >Calon Mahasiswa</option>
					<option value="sudahregistrasiulang" >Sudah Registrasi Ulang</option>
					<option value="sudahgeneratenim" >Sudah Generate NIM</option>
				</select>
			</div>
			<div class="form-group col-md-3">
				<label class="col-form-label "><h5 class="m-0">Status Ujian</h5></label>
				<select class="ujian_online_pmb form-control" onchange="filter();">
					<option value="">-- {{ __('app.view_all') }} --</option>
					<option value="1" >Bisa Ujian</option>
					<option value="2" >Tidak Bisa Ujian</option>
				</select>
			</div>
			<div class="form-group col-md-3">
				<label class="col-form-label "><h5 class="m-0">Status Ikut Ujian</h5></label>
				<select class="ikut_ujian_pmb form-control" onchange="filter();">
					<option value="">-- {{ __('app.view_all') }} --</option>
					<option value="1" >Bisa Ikut Ujian</option>
					<option value="2" >Tidak Bisa Ikut Ujian</option>
				</select>
			</div>
			<div class="form-group col-md-3">
				<label class="col-form-label "><h5 class="m-0">Dari</h5></label>
				<input class="form-control" type='text' id='Tgl1' value="{{ date('Y-m-d') }}" onkeyup="filter()" onchange="filter()" onfocus="filter()">
			</div>
			<div class="form-group col-md-3">
				<label class="col-form-label "><h5 class="m-0">Sampai</h5></label>
				<input class="form-control" type='text' id='Tgl2' value="{{ date('Y-m-d') }}" onkeyup="filter()" onchange="filter()" onfocus="filter()">
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

	$('#Tgl1').datetimepicker({
		format:'Y-m-d',
		onShow:function( ct ){
			this.setOptions({
				maxDate:$('#Tgl2').val()?$('#Tgl2').val():false
			})
		},
		timepicker:false,
		mask:'9999-19-39'
	});

	$('#Tgl2').datetimepicker({
		format:'Y-m-d',
		onShow:function( ct ){
			this.setOptions({
				minDate:$('#Tgl1').val()?$('#Tgl1').val():false
			})
		},
		timepicker:false,
		mask:'9999-19-39'
	});

	function filter(url) {
		if(url == null)
		url = "{{ url('rekapitulasi_pendaftaran_pmb/search') }}";

		$.ajax({
			type: "POST",
			url: url,
			data: {
				gelombang : $(".gelombang").val(),
				gelombang_detail : $(".gelombang_detail").val(),
				ikut_ujian : $(".ikut_ujian_pmb").val(),
				ujian : $(".ujian_online_pmb").val(),
				status : $(".status").val(),
				tgl1 : $("#Tgl1").val(),
				tgl2 : $("#Tgl2").val(),
                _token: '{{ csrf_token() }}'
			},
			success: function(data) {
				$("#konten").html(data);
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
				gelombang : $(".gelombang").val(),
                _token: '{{ csrf_token() }}'
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

	function pdf(){
		var gelombang = $(".gelombang").val();
		var gelombang_detail = $(".gelombang_detail").val();
		var ikut_ujian = $(".ikut_ujian_pmb").val();
		var ujian = $(".ujian_online_pmb").val();
		var status = $(".status").val();
		var tgl1 = $("#Tgl1").val();
		var tgl2 = $("#Tgl2").val();

		window.open('{{ url("rekapitulasi_pendaftaran_pmb/pdf") }}/?gelombang='+gelombang+'&gelombang_detail='+gelombang_detail+'&ikut_ujian='+ikut_ujian+'&ujian='+ujian+'&status='+status+'&tgl1='+tgl1+'&tgl2='+tgl2,"_Blank");
	}

	function excel(){
		var gelombang = $(".gelombang").val();
		var gelombang_detail = $(".gelombang_detail").val();
		var ikut_ujian = $(".ikut_ujian_pmb").val();
		var ujian = $(".ujian_online_pmb").val();
		var status = $(".status").val();
		var tgl1 = $("#Tgl1").val();
		var tgl2 = $("#Tgl2").val();
		window.open('{{ url("rekapitulasi_pendaftaran_pmb/excel") }}/?gelombang='+gelombang+'&gelombang_detail='+gelombang_detail+'&ikut_ujian='+ikut_ujian+'&ujian='+ujian+'&status='+status+'&tgl1='+tgl1+'&tgl2='+tgl2,"_Blank");
	}


	function checkall(chkAll,checkid) {
		if (checkid != null)
		{
			if (checkid.length == null) checkid.checked = chkAll.checked;
			else for (i=0;i<checkid.length;i++) checkid[i].checked = chkAll.checked;

			$("input:checkbox[name='checkID[]']").parents('tr').removeClass('checked_tabel');
			$("input:checkbox[name='checkID[]']:checked").parents('tr').addClass('checked_tabel');
		}
	}
</script>
@endpush
