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
				<label class="col-form-label "><h5 class="m-0">Tahun Masuk</h5></label>
				<select class="TahunMasuk form-control" onchange="filter();" >
					<option value="">-- {{ __('app.view_all') }} --</option>
					@foreach($TahunMasuk as $row)
						<option value="{{ $row['TahunMasuk'] ?? '' }}">{{ $row['TahunMasuk'] ?? '' }}</option>
					@endforeach
				</select>
			</div>
			<div class="form-group col-md-3">
				<label class="col-form-label "><h5 class="m-0">Status Bayar</h5></label>
				<select class="statusbayar_registrasi_pmb form-control" onchange="filter();" >
					<option value=""> -- {{ __('app.view_all') }} -- </option>
					<option value="00">Belum</option>
					<option value="011" selected>Sudah Ada Bayar</option>
				</select>
			</div>
			<div class="form-group col-md-3">
				<label class="col-form-label "><h5 class="m-0">Asal Sekolah</h5></label>
				<select id="SekolahID" class="SekolahID" name="SekolahID form-control" onchange="filter();" >

				</select>
			</div>
			<div class="form-group col-md-6">
				<label class="col-form-label "><h5 class="m-0">{{ __('app.keyword_legend') }}</h5></label>
				<input type="text" class="form-control keyword" onkeyup="filter()" placeholder="{{ __('app.keyword') }} ..">
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
		url = "{{ url('jumlah_sudah_bayar_registrasi_ulang_pmb/search') }}";

		$.ajax({
			type: "POST",
			url: url,
			data: {
				gelombang : $(".gelombang").val(),
				gelombang_detail : $(".gelombang_detail").val(),
				ProgramID : $(".ProgramID").val(),
				TahunMasuk: $(".TahunMasuk").val(),
				pilihan1 : $(".pilihan1").val(),
				statusbayar_registrasi_pmb : $(".statusbayar_registrasi_pmb").val(),
				SekolahID : $("#SekolahID").val(),
				keyword : $(".keyword").val(),
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
		var TahunMasuk =  $(".TahunMasuk").val();
		var statusbayar_registrasi_pmb = $(".statusbayar_registrasi_pmb").val();
		var SekolahID = $("#SekolahID").val();
		var keyword= $(".keyword").val();

		var link = "";

		if(SekolahID == 'null' || SekolahID == null){
			SekolahID = '';
		}

		link += "&gelombang="+gelombang;
		link += "&gelombang_detail="+gelombang_detail;
		link += "&TahunMasuk="+TahunMasuk;
		link += "&statusbayar_registrasi_pmb="+statusbayar_registrasi_pmb;
		link += "&SekolahID="+SekolahID;
		link += "&keyword="+keyword;

		window.open('{{ url("jumlah_sudah_bayar_registrasi_ulang_pmb/pdf") }}/?1'+link,'_Blank');
	}

	function excel(){
		var gelombang = $(".gelombang").val();
		var gelombang_detail = $(".gelombang_detail").val();
		var TahunMasuk =  $(".TahunMasuk").val();
		var statusbayar_registrasi_pmb = $(".statusbayar_registrasi_pmb").val();
		var SekolahID = $("#SekolahID").val();
		var keyword= $(".keyword").val();

		var link = "";

		if(SekolahID == 'null' || SekolahID == null){
			SekolahID = '';
		}

		link += "&gelombang="+gelombang;
		link += "&gelombang_detail="+gelombang_detail;
		link += "&TahunMasuk="+TahunMasuk;
		link += "&statusbayar_registrasi_pmb="+statusbayar_registrasi_pmb;
		link += "&SekolahID="+SekolahID;
		link += "&keyword="+keyword;

		window.open('{{ url("jumlah_sudah_bayar_registrasi_ulang_pmb/excel") }}/?1'+link,'_Blank');
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

	$('#SekolahID').select2({
		ajax: {
			url: '{{ url("c_mahasiswa/jsonSekolah") }}',
			dataType: 'json',
			delay: 250,
			data: function (params) {
				return {
					keyword: params.term, // search term
					page: params.page,
					ID: '',
				};
			},
			processResults: function (data,params) {
				params.page = params.page || 1;
				return {
					results: data.items,
				};
			},
			cache: true
		}

	});
</script>
@endpush
