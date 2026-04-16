@extends('layouts.template1')

@section('content')

@php
if(empty($row)) {
	$row = new stdClass();
	$row->ID = '';
	$row->JalurID = '';
	$row->ProdiID = '';
	$row->ListProdi2 = '';
	$row->ListProdi3 = '';
	$row->JumlahProdiTambahan = '';
	$judul = __('app.title_add');
	$btn = __('app.add');
} else {
	$row = (object) $row;
	$judul = __('app.title_view');
	$btn = __('app.edit');
}
@endphp

<div class="card">
	<div class="card-body">
		<form id="f_jalur_pendaftaran_pmb" onsubmit="savedata(this); return false;" action="{{ url('setting_prodi_tambahan_jurusan/save/' . ($save ?? 1)) }}" enctype="multipart/form-data">
		<input type="hidden" name="id" id="id" value="{{ $row->ID ?? '' }}">
			<h3>Setting Pilihan Prodi Tambahan</h3>
				<div class="form-row mt-3">
					<div class="form-group col-md-12">
						<label class="col-form-label" for="JalurID">Jalur Pendaftaran *</label>
						<div class="controls">
							<select id="JalurID" name="JalurID[]" class="span5" multiple required="">
								<option value=''>Pilih</option>
									@php
										if(isset($row->JalurID) && $row->JalurID){
											$jalur = explode(",", $row->JalurID);
										}else{
											$jalur = array();
										}
										$jalur_list = \DB::table('pmb_edu_jalur_pendaftaran')->get();
									@endphp
									@foreach($jalur_list as $raw)
										@php
											$s = (in_array($raw->id, $jalur))? 'selected' : '';
										@endphp
										<option value="{{ $raw->id }}" {{ $s }} >{{ $raw->nama }}</option>
									@endforeach
							</select>
						</div>
					</div>
					<div class="form-group col-md-12">
						<label class="col-form-label" for="ProdiID">Program Studi Pilihan 1 *</label>
						<div class="controls">
							<select id="ProdiID" name="ProdiID[]" class="span5" multiple required="">
								<option value=''>Pilih</option>
									@php
										if(isset($row->ProdiID) && $row->ProdiID){
											$prodi = explode(",", $row->ProdiID);
										}else{
											$prodi = array();
										}
										$prodi_list = \DB::table('programstudi')->get();
									@endphp
									@foreach($prodi_list as $raw)
										@php
											$jenjang = function_exists('get_field') ? get_field($raw->JenjangID,"jenjang") : '';
											$s = (in_array($raw->ID, $prodi))? 'selected' : '';
										@endphp
										<option value="{{ $raw->ID }}" {{ $s }} > {{ $jenjang }} {{ $raw->Nama }}</option>
									@endforeach
							</select>
						</div>
					</div>
					<div class="form-group col-md-12">
						<label class="col-form-label" for="JumlahProdiTambahan">Jumlah Program Studi Pilihan *</label>
						<div class="controls">
							<select id="JumlahProdiTambahan" onchange="pilihanprodi()" name="JumlahProdiTambahan" class="form-control span5" required="">
								<option value=''>Pilih</option>
								<option {{ ($row->JumlahProdiTambahan ?? '') == "2" ? "selected" : "" }} value='2'>2 </option>
								<option {{ ($row->JumlahProdiTambahan ?? '') == "3" ? "selected" : "" }} value='3'>3</option>
							</select>
						</div>
					</div>
					<div id="div_prodi2" class="col-md-12" style="display:none;">
						<div class="form-group col-md-12">
							<label class="col-form-label" for="ProdiID2">Program Studi Pilihan 2</label>
							<div class="controls">
								<select id="ProdiID2" name="ProdiID2[]" class="span5" multiple>
									<option value=''>Pilih</option>
										@php
											if(isset($row->ListProdi2) && $row->ListProdi2){
												$prodi = explode(",", $row->ListProdi2);
											}else{
												$prodi = array();
											}
											$prodi_list = \DB::table('programstudi')->get();
										@endphp
										@foreach($prodi_list as $raw)
											@php
												$jenjang = function_exists('get_field') ? get_field($raw->JenjangID,"jenjang") : '';
												$s = (in_array($raw->ID, $prodi))? 'selected' : '';
											@endphp
											<option value="{{ $raw->ID }}" {{ $s }} > {{ $jenjang }} {{ $raw->Nama }}</option>
										@endforeach
								</select>
							</div>
						</div>
					</div>
					<div id="div_prodi3" class="col-md-12" style="display:none;">
						<div class="form-group col-md-12">
							<label class="col-form-label" for="ProdiID3">Program Studi Pilihan 3</label>
							<div class="controls">
								<select id="ProdiID3" name="ProdiID3[]" class="span5" multiple>
									<option value=''>Pilih</option>
										@php
											if(isset($row->ListProdi3) && $row->ListProdi3){
												$prodi = explode(",", $row->ListProdi3);
											}else{
												$prodi = array();
											}
											$prodi_list = \DB::table('programstudi')->get();
										@endphp
										@foreach($prodi_list as $raw)
											@php
												$s = (in_array($raw->ID, $prodi))? 'selected' : '';
											@endphp
											<option value="{{ $raw->ID }}" {{ $s }} >{{ $raw->Nama }}</option>
										@endforeach
								</select>
							</div>
						</div>
					</div>
				</div>
				<button onClick="btnEdit({{ $save ?? 1 }},1)" type="button" class="btn btn-bordered-success waves-effect  width-md waves-light btnEdit">{{ $btn }} Data</button>
				<button type="submit" class="btn btn-bordered-primary waves-effect  width-md waves-light btnSave">{{ __('app.save') }} Data</button>
				<button type="button" onClick="back()" class="btn btn-bordered-danger waves-effect  width-md waves-light">{{ __('app.back') }}</button>

		</form>
	</div>
</div>
@endsection

@push('scripts')
<script type="text/javascript">
$.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

$(document).ready(function() {
    autocomplete('ProdiID2');
    autocomplete('ProdiID3');
    autocomplete('ProdiID');
    autocomplete('JalurID');
    
    pilihanprodi({{ $row->JumlahProdiTambahan ?? 'null' }});

    $('#JumlahProdiTambahan').on('change', function() {
        pilihanprodi($(this).val());
    });
});

function pilihanprodi(pilihan) {
    console.log('pilihan:', pilihan);

    // Hide semua dulu
    $('#div_prodi2').hide();
    $('#div_prodi3').hide();

    // Tampilkan sesuai nilai
    if (pilihan == '2') {
        $('#div_prodi2').show();
    } else if (pilihan == '3') {
        $('#div_prodi2').show();
        $('#div_prodi3').show();
    }
}

function savedata(formz){
	var formData = new FormData(formz);
	$.ajax({
		type:'POST',
		url: $(formz).attr('action'),
		data:formData,
		cache:false,
		contentType: false,
		processData: false,
		beforeSend: function(r){ silahkantunggu(); },
		success:function(data){
			if(data == 'gagal'){
				alertfail();
				berhasil();
			}else{
				if({{ $save ?? 1 }} == '1') {
					window.location="{{ url('setting_prodi_tambahan_jurusan') }}";
				}
				if({{ $save ?? 1 }} == '2') {
					window.location.href = "{{ url('setting_prodi_tambahan_jurusan/view') }}/{{ $row->ID ?? '' }}";
				}
				berhasil();
				alertsuccess();
			}
		},
		error: function(data){
			$(".btnSave").html("{{ __('app.save') }} Data");
			$(".btnSave").removeAttr("disabled");
			alertfail('Kesalahan jaringan, cobalah beberapa saat lagi.');
		}
	});
}

function btnEdit(type,checkid) {
	$("input:text, input:file, .num, input:radio, select, textarea, button:submit").attr('disabled',true);
	$(".btnSave").css('display','none');

	if (checkid == 1) {
		$("input:text, input:file, .num, input:radio, select, textarea, button:submit").removeAttr('disabled');
		$(".btnEdit").fadeOut(0);
		$(".btnSave").fadeIn(0);
	}
}
btnEdit({{ $save ?? 1 }});
</script>
@endpush
