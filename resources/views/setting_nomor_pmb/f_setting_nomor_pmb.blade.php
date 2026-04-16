@extends('layouts.template1')

@section('content')
<style>
div.message{ background: transparent url(msg_arrow.gif) no-repeat scroll left center; padding-left: 7px; }
div.error{ background-color:#F3E6E6; border-color: #924949; border-style: solid solid solid none; border-width: 2px; padding: 5px; }
</style>

@php
$btn = empty($row) ? __('app.add') : __('app.edit');
$judul = 'Format Nomor';
@endphp

<div class="card">
	<div class="card-body">
		<ul class="nav nav-tabs">
			<li class="nav-item"><a href="javascript:void(0)" data-toggle="tab" aria-expanded="false" class="nav-link active"><span class="d-sm-block">Format Nomor PMB</span></a></li>
			<li class="nav-item"><a href="{{ url('setting_nomor_pmb/setting_nomor_invoice') }}" class="nav-link"><span class="d-sm-block">Format Nomor Invoice</span></a></li>
			<li class="nav-item"><a href="{{ url('setting_nomor_pmb/setting_nomor_nim') }}" class="nav-link"><span class="d-sm-block">Format NIM</span></a></li>
		</ul>
		<div class="tab-content border-none">
			<div role="tabpanel" class="tab-pane fade show active">
				<form id="f_pmb" action="{{ url('setting_nomor_pmb/save/' . ($save ?? 1)) }}" enctype="multipart/form-data">
					<input type="hidden" name="ID" id="ID" value="{{ $row->id ?? '' }}">
					<input type="hidden" id="jumlah" value="{{ $jum_master ?? 1 }}" />
					<h3>Format No PMB</h3>
					<div class="form-row mt-3">
						<div class="form-group col-md-12">
							<label class="col-form-label" for="kode">Kode *</label>
							<div class="row">
								<div class="col-md-10">
									<select name="format[]" id="master" class="master form-control">
										@foreach($data_master ?? [] as $a)
											<option value="{{ $a->kode }}" {{ (isset($master[0]) && $a->kode == $master[0]) ? 'selected' : '' }} >{{ $a->kode }} ({{ $a->digit }} Digit)</option>
										@endforeach
									</select>
								</div>
								<div class="col-md-2">
									<button onclick="tambahItem()" type="button" class="btn btn-bordered-primary waves-effect  width-md waves-light btn-block"><i class="mdi mdi-plus"></i> Tambah</button>
								</div>
							</div>
						</div>
						<div class="isi col-md-12">
							@for($i=1; $i<($jum_master ?? 1); $i++)
								<div class="form-group" id="div_master{{ $i }}">
									<label class="col-form-label" for="kode">Kode *</label>
									<div class="row">
										<div class="col-md-10">
											<select name="format[]" id="master{{ $i }}" class="master form-control">
												@foreach($data_master ?? [] as $a)
													<option value="{{ $a->kode }}" {{ (isset($master[$i]) && $a->kode == $master[$i]) ? 'selected' : '' }} >{{ $a->kode }} ({{ $a->digit }} Digit)</option>
												@endforeach
											</select>
										</div>
										<div class="col-md-2">
											<button onclick="hapusItem({{ $i }})" type="button" class="btn btn-bordered-danger waves-effect  width-md waves-light btn-block"><i class="mdi mdi-delete"></i> Hapus</button>
										</div>
									</div>
								</div>
							@endfor
						</div>
						<button type="submit" class="btn btn-bordered-success waves-effect  width-md waves-light btnSave">{{ __('app.save') }} Data</button>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
@endsection

@push('scripts')
<script type="text/javascript">
$.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

var counter = {{ $jum_master ?? 1 }};

function tambahItem() {
    var options = $('#master option').clone();
    var html = '<div class="form-group" id="div_master' + counter + '"><label class="col-form-label" for="kode">Kode *</label><div class="row"><div class="col-md-10"><select name="format[]" id="master' + counter + '" class="master form-control">' + $(options).html() + '</select></div><div class="col-md-2"><button onclick="hapusItem(' + counter + ')" type="button" class="btn btn-bordered-danger waves-effect  width-md waves-light btn-block"><i class="mdi mdi-delete"></i> Hapus</button></div></div></div>';
    $('.isi').append(html);
    counter++;
    $('#jumlah').val(counter);
}

function hapusItem(i) {
    $('#div_master' + i).remove();
    counter--;
    $('#jumlah').val(counter);
}

$("#f_pmb").submit(function(e){
    e.preventDefault();
    $.ajax({
        type:'POST',
        url: $(this).attr('action'),
        data: $(this).serialize(),
        beforeSend: function(){ silahkantunggu(); },
        success:function(data){
            if(data == '1'){
                berhasil();
                alertsuccess();
                window.location = "{{ url('setting_nomor_pmb') }}";
            } else {
                alertfail();
                berhasil();
            }
        },
        error: function(){
            $(".btnSave").html("{{ __('app.save') }} Data");
            $(".btnSave").removeAttr("disabled");
            alertfail('Kesalahan jaringan, cobalah beberapa saat lagi.');
        }
    });
});
</script>
@endpush
