@extends('layouts.template1')

@section('content')
@php
if(empty($row)) { $row = new stdClass(); $row->id = ''; $row->gelombang_id = request('gelombang_id', ''); $row->pilihan_pendaftaran_id = ''; $row->biaya_semester_satu_id = ''; $row->prodi_id = ''; $row->biaya = ''; $row->date_start = ''; $row->date_end = ''; } else { $row = (object) $row; }
$gelombang = \DB::table('pmb_tbl_gelombang')->where('id', $row->gelombang_id)->first();
@endphp

<div class="card">
	<div class="card-body">
	<form id="f_gelombang_pmb" onsubmit="savedata(this); return false;" action="{{ url('gelombang_pmb/save_detail/' . ($save ?? 1)) }}" enctype="multipart/form-data">
		<input type="hidden" name="id" id="id" value="{{ $row->id ?? '' }}">
			<h3>Gelombang</h3>
			<div class="form-row mt-3">
				<div class="form-group col-md-12">
					<label class="col-form-label" for="gelombang_id">Gelombang*</label>
					<div class="controls">
						<input type="hidden" name="gelombang_id" value="{{ $row->gelombang_id ?? '' }}">
						<p>@if($gelombang){{ $gelombang->nama }} <br> Tahun Akademik {{ function_exists('get_field') ? get_field($gelombang->tahun_id,'tahun') : '' }}@endif</p>
					</div>
				</div>
				<div class="form-group col-md-12">
					<label class="col-form-label" for="pilihan_pendaftaran_id">Pilihan Pendaftaran *</label>
					<div class="controls">
						<select id="pilihan_pendaftaran_id" name="pilihan_pendaftaran_id" class="form-control" required="" onchange="change_penawaran();get_detail_pilihan_pendaftaran();" >
							<option value="">-- {{ __('app.select_all') }} --</option>
							@php $pilihan_pendaftaran_list = \DB::table('pmb_pilihan_pendaftaran')->where('aktif', 1)->where('tahun_id', $gelombang->tahun_id ?? '')->get(); @endphp
							@foreach($pilihan_pendaftaran_list as $raw) @php $s = ($raw->id == ($row->pilihan_pendaftaran_id ?? ''))? 'selected' : ''; @endphp<option value="{{ $raw->id }}" {{ $s }} >{{ $raw->nama }}</option>@endforeach
						</select>
					</div>
					<div id="detail_pilihan_pendaftaran" class="font-size-13"></div>
				</div>
				<div class="form-group col-md-12">
					<label class="col-form-label" for="prodi_id">Program Studi *</label>
					<div class="controls">
						<select id="prodi_id" name="prodi_id" class="form-control" required="" onchange="change_penawaran()">
								<option value="">-- {{ __('app.select_all') }} --</option>
								@php if(isset($row->prodi_id) && $row->prodi_id){ $prodi = explode(",", $row->prodi_id); }else{ $prodi = array(); } $programstudi_list = \DB::table('programstudi')->where('Aktif', 1)->get(); @endphp
								@foreach($programstudi_list as $raw) @php $s = (in_array($raw->ID, $prodi))? 'selected' : ''; $jenjang_nama = function_exists('get_field') ? get_field($raw->JenjangID,'jenjang') : ''; @endphp<option value="{{ $raw->ID }}" {{ $s }} >{{ $jenjang_nama }} | {{ $raw->Nama }}</option>@endforeach
						</select>
					</div>
				</div>
				<div class="form-group col-md-12">
					<label class="col-form-label" for="biaya_semester_satu_id">Penawaran Biaya Pendaftaran *</label>
					<div class="controls"><select id="biaya_semester_satu_id" name="biaya_semester_satu_id" class="form-control" required="" onchange="changebiaya()"></select></div>
				</div>
				<div class="form-group col-md-12">
					<label class="col-form-label" for="biaya">Biaya {{ $list_Formulir ?? 'Formulir' }} *</label>
					<div class="controls"><input type="text" name="biaya" class="currency biaya form-control" id="biaya" value="{{ $row->biaya ?? '' }}" readonly></div>
				</div>
				<div class="form-group col-md-12"><label class="col-form-label" for="datea">Tanggal Awal *</label><div class="controls"><input type="date" name="date_start" class="tgl form-control" id="datea" value="{{ $row->date_start ?? '' }}" required /></div></div>
				<div class="form-group col-md-12"><label class="col-form-label" for="dateb">Tanggal Akhir *</label><div class="controls"><input type="date" name="date_end" class="tgl form-control" id="dateb" value="{{ $row->date_end ?? '' }}" required /></div></div>
			</div>
			<button onClick="btnEdit({{ $save ?? 1 }},1)" type="button" class="btn btn-bordered-success waves-effect  width-md waves-light btnEdit">{{ $btn ?? __('app.add') }} Data</button>
			<button type="submit" class="btn btn-bordered-primary waves-effect width-md waves-light btnSave">{{ __('app.save') }} Data</button>
			<button type="button" onClick="back()" class="btn btn-bordered-danger waves-effect  width-md waves-light">{{ __('app.back') }}</button>
		</form>
	</div>
</div>
@endsection

@push('scripts')
<script type="text/javascript">
$.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

function change_penawaran() {
	$.ajax({ url: "{{ url('gelombang_pmb/change_penawaran') }}", type: "post", data: { pilihan_pendaftaran_id : $('#pilihan_pendaftaran_id').val(), prodi_id : $('#prodi_id').val(), biaya_semester_satu_id : '{{ $row->biaya_semester_satu_id ?? '' }}', gelombang_id : '{{ $row->gelombang_id ?? '' }}' }, success: function(data){ $('#biaya_semester_satu_id').html(data); changebiaya(); } });
}
change_penawaran();

function get_detail_pilihan_pendaftaran(){
	if($('#pilihan_pendaftaran_id').val() == ''){ $("#detail_pilihan_pendaftaran").html(""); return; }
	$.ajax({ url: "{{ url('gelombang_pmb/get_detail_pilihan_pendaftaran') }}", type: "POST", dataType: "JSON", data: { pilihan_pendaftaran_id : $('#pilihan_pendaftaran_id').val() }, success: function(data){ $("#detail_pilihan_pendaftaran").html(`<p class="mb-0">Program Kuliah : <strong>${data.NamaProgram}</strong></p><p class="mb-0">Jenis Pendaftaran : <strong>${data.NamaJenisPendaftaran}</strong></p><p class="mb-0">Jalur Pendaftaran : <strong>${data.NamaJalur}</strong></p>`); } });
}
get_detail_pilihan_pendaftaran()

function changebiaya() { let formulir = $('#biaya_semester_satu_id option:selected').attr('formulir'); $('#biaya').val(formulir); $('.currency').mask('#.##0', {reverse: true}); }

function savedata(formz){
	$('.currency').unmask();
	var formData = new FormData(formz);
	$.ajax({ type:'POST', url: $(formz).attr('action'), data:formData, cache:false, contentType: false, processData: false, beforeSend: function(r){ silahkantunggu(); },
		success:function(data){
			$('.currency').mask('#.##0', {reverse: true});
			if(data == 'gagal'){ alertfail(); berhasil(); } else {
				if({{ $save ?? 1 }} == '1') { window.location.href = "{{ url('gelombang_pmb/detail') }}?gelombang_id={{ $row->gelombang_id ?? '' }}"; }
				if({{ $save ?? 1 }} == '2') { window.location.href = "{{ url('gelombang_pmb/view_detail') }}/{{ $row->id ?? '' }}?gelombang_id={{ $row->gelombang_id ?? '' }}"; }
				berhasil(); alertsuccess();
			}
		},
		error: function(data){ $('.currency').mask('#.##0', {reverse: true}); $(".btnSave").html("{{ __('app.save') }} Data"); $(".btnSave").removeAttr("disabled"); alertfail('Kesalahan jaringan, cobalah beberapa saat lagi.'); }
	});
}

function btnEdit(type,checkid) {
	$("input:text, .tgl, input:file, .num, input:radio, select, textarea, button:submit").attr('disabled',true); $(".btnSave").css('display','none');
	if (checkid == 1) { $("input:text, .tgl, input:file, .num, input:radio, select, textarea, button:submit").removeAttr('disabled'); $(".btnEdit").fadeOut(0); $(".btnSave").fadeIn(0); }
}
btnEdit({{ $save ?? 1 }});
$('.currency').mask('#.##0', {reverse: true});
</script>
@endpush
