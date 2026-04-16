@extends('layouts.template1')

@section('content')
@php
if(empty($row)) {
	$row = new stdClass();
	$row->id = '';
	$row->kode = '';
	$row->nama = '';
	$row->tahun_id = '';
	$row->tahunmasuk = '';
	$row->GelombangKe = '';
	$row->date_start = '';
	$row->date_end = '';
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
	<form id="f_gelombang_pmb" onsubmit="savedata(this); return false;" action="{{ url('gelombang_pmb/save/' . ($save ?? 1)) }}" enctype="multipart/form-data">
			<input type="hidden" name="id" id="id" value="{{ $row->id ?? '' }}">
			<h3>Gelombang</h3>
			<div class="form-row mt-3">
				<div class="form-group col-md-12">
					<label class="col-form-label" for="kode">Kode *</label>
					<div class="controls">
						<input type="text" required id="kode" name="kode" class="form-control" value="{{ $row->kode ?? '' }}" />
						<small>* Tidak Boleh Menggunakan Kode yang sudah digunakan</small>
					</div>
				</div>
				<div class="form-group col-md-12">
					<label class="col-form-label" for="nama">Nama *</label>
					<div class="controls">
						<input type="text" required id="nama" name="nama" class="form-control" value="{{ $row->nama ?? '' }}" />
						<small>* Tidak Boleh Menggunakan Nama yang sudah digunakan di tahun akademik yang sama</small>
					</div>
				</div>
				<div class="form-group col-md-12">
					<label class="col-form-label" for="tahun_id">Tahun Akademik *</label>
					<div class="controls">
						<select id="tahun_id" name="tahun_id" class="span5" onchange="change_tahunmasuk()" required="">
							<option value=""> -- Pilih --</option>
							@php $tahun = \DB::table('tahun')->orderBy('TahunID', 'desc')->get(); @endphp
							@foreach($tahun as $raw)
								@php $s = ($raw->ID == ($row->tahun_id ?? ''))? 'selected' : ''; @endphp
								<option value="{{ $raw->ID }}" {{ $s }} >{{ $raw->Nama }}</option>
							@endforeach
						</select>
					</div>
				</div>
				<div class="form-group col-md-12">
					<label class="col-form-label" for="GelombangKe">Gelombang Ke- *</label>
					<div class="controls">
						<select id="GelombangKe" name="GelombangKe" class="form-control" required="">
							<option value=""> -- Pilih --</option>
							@php $gelombang_ke = \DB::table('gelombang_ke')->get(); @endphp
							@foreach($gelombang_ke as $raw)
								@php $s = ($raw->GelombangKe == ($row->GelombangKe ?? ''))? 'selected' : ''; @endphp
								<option value="{{ $raw->GelombangKe }}" {{ $s }} >{{ $raw->Nama }}</option>
							@endforeach
						</select>
					</div>
				</div>
				<div class="form-group col-md-12">
					<label class="col-form-label" for="tahunmasuk">Tahun Masuk Mahasiwa *</label>
					<div class="controls">
						<input type="hidden" name="tahunmasuk" id="tahunmasuk" value="{{ $row->tahunmasuk ?? '' }}">
						<p id="text_tahunmasuk">{{ $row->tahunmasuk ?? '' }}</p>
					</div>
				</div>
				<div class="form-group col-md-12">
					<label class="col-form-label">Pembukaan Pendaftaran *</label>
					<div class="row pr-0">
						<div class="col-md-5">
							<div class="controls">
								<input type="date" id='datea' name="date_start" class="tgl form-control" value="{{ $row->date_start ?? '' }}" required>
							</div>
						</div>
						<div class="col-md-1 pr-0 pl-0 pt-2" style="vertical-align : middle;text-align:center;">s/d</div>
						<div class="col-md-6">
							<div class="controls">
								<input type="date" id='dateb' name="date_end" class="tgl form-control" value="{{ $row->date_end ?? '' }}" required>
							</div>
						</div>
					</div>
				</div>
			</div>
			<button onClick="btnEdit({{ $save ?? 1 }},1)" type="button" class="btn btn-bordered-success waves-effect  width-md waves-light btnEdit">{{ $btn }} Data</button>
			<button type="submit" class="btn btn-bordered-primary waves-effect width-md waves-light btnSave">{{ __('app.save') }} Data</button>
			<button type="button" onClick="back()" class="btn btn-bordered-danger waves-effect  width-md waves-light">{{ __('app.back') }}</button>
		</form>
	</div>
</div>
@endsection

@push('scripts')
<script type="text/javascript">
$.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
$(document).ready(function() { autocomplete('tahun_id','','Pilih Tahun Akademik'); });

function savedata(formz){
	var formData = new FormData(formz);
	$.ajax({
		type:'POST', url: $(formz).attr('action'), data:formData, cache:false, contentType: false, processData: false,
		beforeSend: function(r){ silahkantunggu(); },
		success:function(data){
			if(data == 'gagal_belum_ada_biaya'){
				swal("Pemberitahuan","Tidak Dapat Menambah Gelombang karena belum ada biaya pendaftaran yang di setting di Tahun Akademik yang dipilih ","error");
				berhasil();
			} else if(data == 'gagal'){
				alertfail(); berhasil();
			} else {
				if({{ $save ?? 1 }} == '1') { window.location="{{ url('gelombang_pmb') }}/?simpan=1"; }
				if({{ $save ?? 1 }} == '2') { window.location.href = "{{ url('gelombang_pmb/view') }}/{{ $row->id ?? '' }}"; }
				berhasil(); alertsuccess();
			}
		},
		error: function(data){
			$(".btnSave").html("{{ __('app.save') }} Data"); $(".btnSave").removeAttr("disabled"); alertfail('Kesalahan jaringan, cobalah beberapa saat lagi.');
		}
	});
}

function change_tahunmasuk(){
	$.ajax({
		type:'POST', url: "{{ url('gelombang_pmb/change_tahunmasuk') }}", data:{ tahun_id : $('#tahun_id').val() },
		success:function(data){ $('#text_tahunmasuk').text("\n"+data); $('#tahunmasuk').val(data); },
		error: function(data){ alert('Ada Kesalahan Teknis / Jaringan'); }
	});
}
change_tahunmasuk();

function btnEdit(type,checkid) {
	$("input:text, input:file, .num, input:radio, select, textarea, button:submit").attr('disabled',true);
	$(".btnSave").css('display','none');
	if (checkid == 1) {
		$("input:text, input:file, .num, input:radio, select, textarea, button:submit").removeAttr('disabled');
		$(".btnEdit").fadeOut(0); $(".btnSave").fadeIn(0);
	}
}
btnEdit({{ $save ?? 1 }});
</script>
@endpush
