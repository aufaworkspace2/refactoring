@extends('layouts.template1') 
@section('content') 

@php
if(empty($row)) {
	$row = (object)[
        'ID' => '',
        'Nama' => '',
        'Urut' => ''
    ];	
	$judul = 'Tambah Data Level';
	$btn = 'Tambah';
} else {
	$judul = 'Detail Data Level';
	$btn = 'Ubah';
}
@endphp

<div class="card">
	<div class="card-body">
		<form id="f_level" action="{{ url('level/save/'.$save) }}" enctype="multipart/form-data">
        @csrf
		<input class="span12" type="hidden" name="ID" id="ID" value="{{ $row->ID }}">
			
			<h3>{{ $judul }}</h3>
            <div class="form-row mt-3">
                <div class="form-group col-md-12">
                    <label class="col-form-label" for="Urut">No Urut *</label>
                    <div class="controls">
                        <input type="text" id="Urut" name="Urut" class="form-control" value="{{ $row->Urut }}" />
                    </div>
                </div>						
                <div class="form-group col-md-12">
                    <label class="col-form-label" for="Nama">Nama Level *</label>
                    <div class="controls">
                        <input type="text" id="Nama" name="Nama" class="form-control" value="{{ $row->Nama }}" />
                    </div>
                </div>						
            </div>     	
				
		<button onClick="btnEdit({{ $save }},1)" type="button" class="btn btn-bordered-success waves-effect width-md waves-light btnEdit">{{ $btn }} Data <icon class="icon-ok-circle icon-white-t"></icon></button>
		<button type="submit" id="save_level" class="btn btn-bordered-primary waves-effect width-md waves-light btnSave">Simpan Data <icon class="icon-check icon-white-t"></icon></button>
		<button type="button" id="backbut" onclick="back()" class="btn btn-bordered-danger waves-effect width-md waves-light">Kembali <icon class="icon-share-alt icon-white-t"></icon></button>
				
		</form>
	</div>
</div>

@push('scripts')
<script type="text/javascript">
$(document).ready(function() {
    btnEdit({{ $save }});

    $("#f_level").validate({
        rules: {
            Nama: { required: true }
        },	
        submitHandler: function(form){
            var formData = new FormData(form);
                
            $.ajax({
                type:'POST',
                url: $(form).attr('action'),
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                success:function(data){
                    window.location.href = "{{ url(request()->segment(1)) }}";
                },
                error: function(data){
                    $(".alert-error").show();
                    $(".alert-error-content").html("Gagal Menyimpan Data!");
                    window.setTimeout(function() { $(".alert-error").slideUp( "slow" ); }, 6000);
                }
            });
        }
    });
});

function btnEdit(type, checkid = 0) {
    if (checkid == 0) {
        $("input[type='text'], input[type='file'], input[type='radio'], select, textarea").prop('disabled', true);
        $("#save_level").hide();
        $(".btnEdit").show();
    } else if (checkid == 1) {
        $("input[type='text'], input[type='file'], input[type='radio'], select, textarea").prop('disabled', false);
        $(".btnEdit").hide();
        $("#save_level").show();
    }
}

function back() {
    window.location.href = "{{ url(request()->segment(1)) }}";
}
</script>
@endpush
@endsection