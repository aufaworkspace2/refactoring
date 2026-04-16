@extends('layouts.template1') 
@section('content') 

@php
if(empty($row)) {
	$row = (object)[
		'ID' => '',
		'LevelID' => '',
		'ModulID' => '',
		'Create' => '',
		'Read' => '',
		'Update' => '',
		'Delete' => '',
		'Shortcut' => '',
		'Icon' => ''
	];	
	$judul = 'Tambah Data Level Modul';
	$slog = 'Tambah Data Baru';
	$btn = 'Tambah';
} else {
	$judul = 'Detail Data Level Modul';
	$slog = 'Detail Data Level Modul <b>'.($row->LevelID ?? '').'</b>';
	$btn = 'Ubah';
}
@endphp

<div class="card">
	<div class="card-body">
		<!-- Heading  -->
		<div class="row-fluid mb-3">
			<div class="span12">
				<h3>{{ $judul }}</h3>
				<p class="lead">{!! ucwords(strtolower($slog)) !!}</p>
				<hr />
		  </div>
		</div>
		<!--  End Heading-->

		<form id="f_levelmodul" action="{{ url('levelmodul/save/'.$save) }}" enctype="multipart/form-data">
		@csrf
		<input class="span12" type="hidden" name="ID" id="ID" value="{{ $row->ID }}">
		<div class="row-fluid">
			<!--  Tab Content -->
			<div class="span12">
					<div class="tab-pane active" id="tab-details">
						<div class="form-row mt-3">
							<div class="form-group col-md-12">
								<label class="col-form-label" for="LevelID">Level ID *</label>
								<div class="controls">
									<input type="text" id="LevelID" name="LevelID" class="form-control" value="{{ $row->LevelID }}" />
								</div>
							</div>
							
							<div class="form-group col-md-12">
								<label class="col-form-label" for="ModulID">Modul ID *</label>
								<div class="controls">
									<input type="text" id="ModulID" name="ModulID" class="form-control" value="{{ $row->ModulID }}" />
								</div>
							</div>
							
							<div class="form-group col-md-12">
								<label class="col-form-label" for="Create">Create *</label>
								<div class="controls">
									<input type="text" id="Create" name="Create" class="form-control" value="{{ $row->Create }}" />
								</div>
							</div>
							
							<div class="form-group col-md-12">
								<label class="col-form-label" for="Read">Read *</label>
								<div class="controls">
									<input type="text" id="Read" name="Read" class="form-control" value="{{ $row->Read }}" />
								</div>
							</div>
							
							<div class="form-group col-md-12">
								<label class="col-form-label" for="Update">Update *</label>
								<div class="controls">
									<input type="text" id="Update" name="Update" class="form-control" value="{{ $row->Update }}" />
								</div>
							</div>
							
							<div class="form-group col-md-12">
								<label class="col-form-label" for="Delete">Delete *</label>
								<div class="controls">
									<input type="text" id="Delete" name="Delete" class="form-control" value="{{ $row->Delete }}" />
								</div>
							</div>
							
							<div class="form-group col-md-12">
								<label class="col-form-label" for="Shortcut">Shortcut *</label>
								<div class="controls">
									<input type="text" id="Shortcut" name="Shortcut" class="form-control" value="{{ $row->Shortcut }}" />
								</div>
							</div>
							
							<div class="form-group col-md-12">
								<label class="col-form-label" for="Icon">Icon *</label>
								<div class="controls">
									<input type="text" id="Icon" name="Icon" class="form-control" value="{{ $row->Icon }}" />
								</div>
							</div>
						</div>				
					</div>          	
				</div>	
				
		<button onClick="btnEdit({{ $save }},1)" type="button" class="btn btn-bordered-success waves-effect width-md waves-light btnEdit">{{ $btn }} Data <icon class="icon-ok-circle icon-white-t"></icon></button>
		<button type="submit" id="save_level" class="btn btn-bordered-primary waves-effect width-md waves-light btnSave">Simpan Data <icon class="icon-check icon-white-t"></icon></button>
		<button type="button" onClick="back()" class="btn btn-bordered-danger waves-effect width-md waves-light">Kembali <icon class="icon-share-alt icon-white-t"></icon></button>
				
		</div>
		</form>
	</div>
</div>
@push('scripts')
<script type="text/javascript">

$(document).ready(function() {
    btnEdit({{ $save }});

	$("#f_levelmodul").validate({
		rules: {
			LevelID: { required: true },
			ModulID: { required: true },
			Create: { required: true },
			Read: { required: true },
			Update: { required: true },
			Delete: { required: true },
			Shortcut: { required: true },
			Icon: { required: true },
		},	
		submitHandler: function(form){
		var formData = new FormData(form);
				
		$.ajax({
			type:'POST',
			url: $(form).attr('action'),
			data:formData,
			cache:false,
			contentType: false,
			processData: false,
			success:function(data){
				if({{ $save }} == '1')
				{
					window.location.href = "{{ url(request()->segment(1)) }}";
				}
				
				if({{ $save }} == '2')
				{
					// Simulasi view update success
					window.location.href = "{{ url(request()->segment(1)) }}";
				}
				$( ".alert-success" ).animate({ backgroundColor: "#dff0d8" }, 1000 );
				$( ".alert-success" ).animate({ backgroundColor: "#b6ef9e" }, 1000 );
				
				$(".alert-success").show();
				$(".alert-success-content").html("Data berhasil disimpan.");
				window.setTimeout(function() { $(".alert-success").slideUp(); }, 10000);
			},
			error: function(data){
				$( ".alert-error" ).animate({ backgroundColor: "#ec9b9b" }, 1000 );
				$( ".alert-error" ).animate({ backgroundColor: "#df3d3d" }, 1000 );
				
				$(".alert-error").show();
				$(".alert-error-content").html("Data gagal disimpan.");
				window.setTimeout(function() { $(".alert-error").slideUp( "slow" ); }, 6000);
			}
		});
		}
	});
});

function btnEdit(type,checkid = 0) {
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
