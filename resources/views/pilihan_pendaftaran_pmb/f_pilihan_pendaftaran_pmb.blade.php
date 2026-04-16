@extends('layouts.template1')

@section('content')

@php
if(empty($row)) {
	$row = new stdClass();
	$row->id = '';
	$row->kode = '';
	$row->nama = '';
	$row->tahun_id = '';
	$row->tahumasuk = '';

	$judul = __('title_add');
	$slog = __('slog_add');
	$btn = __('add');
} else {
	$row = (object) $row;
	$judul = __('title_view');
	$slog = __('slog_view') . '<b>' . ($row->nama ?? '') . '</b>';
	$btn = __('edit');
}
@endphp

<div class="card">
	<div class="card-body">
	<form id="f_pilihan_pendaftaran_pmb" onsubmit="savedata(this); return false;" action="{{ url('pilihan_pendaftaran_pmb/save/' . ($save ?? 1)) }}" enctype="multipart/form-data">
			<input class="span12" type="hidden" name="id" id="id" value="{{ $row->id ?? '' }}">
			<h3>Pilihan Pendaftaran</h3>
			<div class="form-row mt-3">

				<div class="form-group col-md-12">
					<label class="col-form-label" for="nama">Nama *</label>
					<div class="controls">
						<input type="text" required id="nama" name="nama" class="form-control" value="{{ $row->nama ?? '' }}" />

					</div>
				</div>

				<div class="form-group col-md-12">
					<label class="col-form-label" for="tahun_id">Tahun Akademik *</label>
					<div class="controls">
						<select id="tahun_id" name="tahun_id" onchange="changediskon();" class="form-control" required="">
							<option value=""> -- Pilih --</option>
								@php
									$tahun = \DB::table('tahun')->orderBy('TahunID', 'desc')->get();
								@endphp
								@foreach($tahun as $raw)
									@php
										$s = ($raw->ID == ($row->tahun_id ?? ''))? 'selected' : '';
									@endphp
										<option value="{{ $raw->ID }}" {{ $s }} >{{ $raw->Nama }}</option>
								@endforeach
						</select>
					</div>
				</div>




				<div class="form-group col-md-12">
					<label class="col-form-label" for="program_id">Program Kuliah *</label>
					<div class="controls">
						<select id="program_id" name="program_id" onchange="changediskon();" class="form-control"  required="">
							<!-- multiple -->
							<option value=''>Pilih</option>
								@php
									if(isset($row->program_id) && $row->program_id){
										$program = explode(",", $row->program_id);
									}else{
										$program = array();
									}
									$program_list = \DB::table('program')->get();
								@endphp
								@foreach($program_list as $raw)
									@php
										$s = (in_array($raw->ID, $program))? 'selected' : '';
									@endphp
										<option value="{{ $raw->ID }}" {{ $s }} >{{ $raw->Nama }}</option>
								@endforeach
						</select>
					</div>
				</div>

				<div class="form-group col-md-12">
					<label class="col-form-label" for="jenis_pendaftaran">Jenis Pendaftaran *</label>
					<div class="controls">
						<select id="jenis_pendaftaran" name="jenis_pendaftaran[]" class="form-control" onchange="changediskon();" multiple required="">
								@php
									if(isset($row->jenis_pendaftaran) && $row->jenis_pendaftaran){
										$jenis_pendaftaran = explode(",", $row->jenis_pendaftaran);
									}else{
										$jenis_pendaftaran = array();
									}
									$jenis_pendaftaran_list = \DB::table('jenis_pendaftaran')->get();
								@endphp
								@foreach($jenis_pendaftaran_list as $raw)
									@php
										$s = (in_array($raw->ID, $jenis_pendaftaran))? 'selected' : '';
									@endphp
										<option value="{{ $raw->ID }}" {{ $s }} >{{ $raw->Nama }}</option>
								@endforeach
						</select>
					</div>
				</div>

				<div class="form-group col-md-12">
					<label class="col-form-label" for="jalur">Jalur Pendaftaran *</label>
					<div class="controls">
						<!-- multiple -->
						<select id="jalur" name="jalur" class="form-control" onchange="changediskon();"  required="">
							<option value=''>Pilih</option>
								@php
									if(isset($row->jalur) && $row->jalur){
										$jalur = explode(",", $row->jalur);
									}else{
										$jalur = array();
									}
									$jalur_list = \DB::table('pmb_edu_jalur_pendaftaran')->where('aktif','1')->get();
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
					<label class="col-form-label" for="master_diskon_id_list">Beasiswa / Diskon </label>
					<div class="controls">
						<select id="master_diskon_id_list" name="master_diskon_id_list[]" class="form-control" multiple>

						</select>
						<br>
						<small style="color:red"><i>*Jika Tidak Ada Beasiswa yang muncul, berarti belum di set beasiswa di menu setup biaya sesuai dengan pilihan diatas.</i></small>
					</div>
				</div>
				<!-- <div class="form-group col-md-12">
					<label class="col-form-label" for="master_diskon_id_list">Beasiswa / Diskon</label>
					<div class="controls">
						<select id="master_diskon_id_list" name="master_diskon_id_list" class="form-control">

						</select>
						<br>
						<small>Jika Tidak Ada Beasiswa yang muncul, berarti belum di set beasiswa di menu setup biaya sesuai dengan pilihan diatas.</small>
					</div>
				</div> -->
			</div>
			<button onClick="btnEdit({{ $save ?? 1 }},1)" type="button" class="btn btn-bordered-success waves-effect  width-md waves-light btnEdit">{{ $btn }} Data <icon class="icon-ok-circle icon-white-t"></icon></button>
			<button type="submit" class="btn btn-bordered-primary waves-effect width-md waves-light btnSave">{{ __('save') }} Data <icon class="icon-check icon-white-t"></icon></button>
			<button type="button" onClick="back()" class="btn btn-bordered-danger waves-effect  width-md waves-light">{{ __('back') }} <icon class="icon-share-alt icon-white-t"></icon></button>
		</form>
	</div>
</div>
@endsection

<!-------------------------------------------------------------------- Javascript Area -------------------------------------------------------------------->
@push('scripts')
<script type="text/javascript">
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

$(document).ready(function() {
    autocomplete('program_id');
    autocomplete('tahun_id');
    autocomplete('jenis_pendaftaran');
    autocomplete('jalur');
});

function changediskon(){
	$.ajax({
		url		: "{{ url('pilihan_pendaftaran_pmb/changediskon') }}",
		type	: "post",
		data	: {
			tahun_id : $('#tahun_id').val(),
			program_id : $('#program_id').val(),
			jenis_pendaftaran : $('#jenis_pendaftaran').val(),
			jalur : $('#jalur').val(),
			select_master_diskon : '{{ $row->master_diskon_id_list ?? '' }}'
		},
		success	: function(data){
			$('#master_diskon_id_list').html(data);
			autocomplete('master_diskon_id_list');
		}
	});
}
changediskon();


function savedata(formz){
		var formData = new FormData(formz);
		$.ajax({
			type:'POST',
			url: $(formz).attr('action'),
			data:formData,
			cache:false,
			contentType: false,
			processData: false,
			beforeSend: function(r){
			silahkantunggu();
			},
			success:function(data){
			if(data == 'gagal'){
				alertfail();
				berhasil();
			}else{
				if({{ $save ?? 1 }} == '1')
				{
					window.location="{{ url('pilihan_pendaftaran_pmb') }}";
				}

				if({{ $save ?? 1 }} == '2')
				{
					window.location.href = "{{ url('pilihan_pendaftaran_pmb/view') }}/{{ $row->id ?? '' }}";
				}
				berhasil();
				alertsuccess();
			}
			},
			error: function(data){
				$(".btnSave").html("{{ __('save') }} Data <icon class=\"icon-check icon-white-t\"></icon>");
				$(".btnSave").removeAttr("disabled");
				alertfail('Kesalahan jaringan, cobalah beberapa saat lagi.');
			}
		});
	}

function btnEdit(type,checkid) {
	$("input:text").attr('disabled',true);
    $("input:file").attr('disabled',true);
    $(".num").attr('disabled',true);
    $("input:radio").attr('disabled',true);
	$("button:submit").attr('disabled',true);
    $("select").attr('disabled',true);
    $("textarea").attr('disabled',true);
	$(".btnSave").css('display','none');

	if (checkid == 1)
	{
    $("input:text").removeAttr('disabled');
    $("input:file").removeAttr('disabled');
    $(".num").removeAttr('disabled');
    $("input:radio").removeAttr('disabled');
    $("select").removeAttr('disabled');
    $("textarea").removeAttr('disabled');
	$("button:submit").removeAttr('disabled');
	$(".btnEdit").fadeOut(0);
	$(".btnSave").fadeIn(0);
   	}

}
btnEdit({{ $save ?? 1 }});

</script>
@endpush

<!------------------------------------------------------------------ End Javascript Area ------------------------------------------------------------------>
