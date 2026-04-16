@extends('layouts.template1')
@section('content')
<?php
if(empty($row))
{
	$row = (object)[
		'ID' => '',
		'Nama' => ''
	];

	$judul = __('title_add');
	$slog = __('slog_add');
	$btn = __('add');
}
else
{
	$judul = __('title_view');
	$slog = __('slog_view').'<b>'.$row->Nama.'</b>';
	$btn = __('edit');
}
?>
<div class="card">
	<div class="card-body">
		<form id="f_tarif_sks" onsubmit="savedata(this); return false;" action="{{ url('metode_pembayaran/save/' . $save) }}" enctype="multipart/form-data">
		@csrf
		<input class="form-control" type="hidden" name="ID" id="ID" value="{{$row->ID}}">
			<h3>{{$btn}} Metode Pembayaran</h3>
				<div class="form-row mt-3">

					<div class="form-group col-md-12">
						<label class="col-form-label" for="Nama">Nama *</label>
						<div class="controls">
							<input type="text" id="Nama" name="Nama" value="{{$row->Nama}}" required class="form-control">
						</div>
					</div>

				</div>

		<button onClick="btnEdit({{$save}},1)" type="button" class="btn btn-bordered-success waves-effect width-md waves-light btnEdit">{{$btn}} Data <icon class="icon-ok-circle icon-white-t"></icon></button>
		<button type="submit" class="btn btn-bordered-primary waves-effect width-md waves-light btnSave">{{ __('save') }} Data <icon class="icon-check icon-white-t"></icon></button>
		<button type="button" onClick="back()" class="btn btn-bordered-danger waves-effect width-md waves-light">{{ __('back') }} <icon class="icon-share-alt icon-white-t"></icon></button>

		</form>
	</div>
</div>

<!-------------------------------------------------------------------- Javascript Area -------------------------------------------------------------------->
@push('scripts')
<script type="text/javascript">
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
					if({{$save}} == '1')
					{
						window.location="{{ url('metode_pembayaran') }}";
					}

					if({{$save}} == '2')
					{
						window.location="{{ url('metode_pembayaran/view/' . ($row->ID ?? '')) }}";
					}
					berhasil();
					alertsuccess();
				}
			},
			error: function(data){
				$(".btnSave").html('{{ __('save') }} Data <icon class="icon-check icon-white-t"></icon>');
				$(".btnSave").removeAttr("disabled");
				$( ".alert-error" ).animate({
					backgroundColor: "#ec9b9b"
				}, 1000 );
				$( ".alert-error" ).animate({
					backgroundColor: "#df3d3d"
				}, 1000 );
				$( ".alert-error" ).animate({
					backgroundColor: "#ec9b9b"
				}, 1000 );
				$( ".alert-error" ).animate({
					backgroundColor: "#df3d3d"
				}, 1000 );

				$(".alert-error").show();
				$(".alert-error-content").html("{{ __('alert-error') }}");
				window.setTimeout(function() { $(".alert-error").slideUp( "slow" ); }, 6000);
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
btnEdit({{$save}});

</script>
@endpush

<!------------------------------------------------------------------ End Javascript Area ------------------------------------------------------------------>
@endsection
