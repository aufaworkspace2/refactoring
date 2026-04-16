@extends('layouts.template1')
@section('content')
<?php
if(empty($row))
{
	$row = (object)[
		'ID' => '',
		'Nama' => '',
		'Icon' => '',
		'MetodePembayaranID' => '',
		'JenisBiayaID_list' => '',
		'BiayaAdmin' => ''
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
		<form id="f_tarif_sks" onsubmit="savedata(this); return false;" action="{{ url('channel_pembayaran/save/' . $save) }}" enctype="multipart/form-data">
		@csrf
		<input class="form-control" type="hidden" name="ID" id="ID" value="{{$row->ID}}">
			<h3>{{$btn}} Channel Pembayaran</h3>
				<div class="form-row mt-3">

					<div class="form-group col-md-12">
						<label class="col-form-label" for="Nama">Nama *</label>
						<div class="controls">
							<input type="text" id="Nama" name="Nama" value="{{$row->Nama}}" required class="form-control">
						</div>
					</div>

					<div class="form-group col-md-12">
						<label class="col-form-label" for="Icon">Icon *</label>
						<div class="controls">
							<input type="file" id="Icon" name="Icon" class="form-control" accept="image/*" />
							<input type="hidden" id="IconLama" name="IconLama" value="{{$row->Icon}}"/>
							<p>File Sebelumnya : {{($row->Icon != "") ? "<a href='".asset('metodebayar/channelbayar/'.$row->Icon)."' target='_blank'>".$row->Icon."</a>" : "(Tidak Ada)" }}</p>
						</div>
					</div>

					<div class="form-group col-md-12">
						<label class="col-form-label" for="MetodePembayaranID">Metode Pembayaran *</label>
						<div class="controls">
							<select id="MetodePembayaranID" name="MetodePembayaranID" class="MetodePembayaranID form-control">
								<option value="">-- {{ __('view_all') }} --</option>
								@foreach($MetodePembayaranList as $raw)
									<?php $s = ($raw->ID == $row->MetodePembayaranID) ? 'selected' : ''; ?>
									<option value="{{$raw->ID}}" {{$s}} >{{$raw->Nama}}</option>
								@endforeach
							</select>
						</div>
					</div>

					<div class="form-group col-md-12">
						<label class="col-form-label" for="JenisBiayaID_list"> Komponen Biaya </label>
						<div class="controls">
							<select id="JenisBiayaID_list" name="JenisBiayaID_list[]" class="form-control" multiple>
								<!-- <option value="">-- Tidak Pilih Komponen Biaya --</option> -->
								<?php
								$arr_jb = explode(",",$row->JenisBiayaID_list);
								foreach($JenisBiayaList as $row_jb){
									$s = (in_array($row_jb->ID,$arr_jb)) ? 'selected' : '';
									echo "<option value='$row_jb->ID' $s>$row_jb->Nama</option>";
								}
								?>
							</select>
						</div>
					</div>

					<div class="form-group col-md-12">
						<label class="col-form-label" for="BiayaAdmin">Biaya Admin</label>
						<div class="input-group">
							<div class="input-group-append" id="div_rupiah">
								<span class="input-group-text" id="basic-addon1">Rp.</span>
							</div>
							<input type="text" class="form-control currency"  id="BiayaAdmin" name="BiayaAdmin" value="{{$row->BiayaAdmin}}" />

						</div>
					</div>


					<div class="form-group col-md-12">
							<label class="col-form-label" for="panduan_pembayaran">Panduan Pembayaran</label>
							<div class="controls">
								<div class="table-responsive" id="dataPilihan">
									<table class="table table-bordered table-hovered">
										<thead class="bg-primary text-white">
											<tr>
												<th colspan="2">List Panduan Pembayaran</th>
												<th style="width: 15%">Aksi</th>
											</tr>
										</thead>
										<tbody id="bodyPilihan">
										@if($save == 2 && count($PanduanPembayaranList) > 0)
											<?php $loop = 0; ?>
											@foreach($PanduanPembayaranList as $k)
												<tr class="item_{{$loop}}">
													<td colspan="2">
														<p>Judul</p>
														<input type="text" name="NamaPanduan[]" class="form-control" value="{{$k->NamaPanduan}}">
													</td>

													<td class="center" rowspan="2">
														<p>Panduan</p>
														<button type="button" class="btn btn-bordered-danger waves-effect waves-light btn-block btn-pilihan" onClick="deleteRow({{$loop}});"><i class="mdi mdi-delete"></i> Hapus</button>
													</td>
												</tr>
												<tr class="item_{{$loop}}">
													<td colspan="2">
														<textarea name="TextCaraBayar[]" id="TextCaraBayar_{{$loop}}" class="tinymce" rows="10">{{$k->TextCaraBayar}}</textarea>
													</td>
												</tr>
												<?php $loop++; ?>
											@endforeach
										@endif
										<input type="hidden" id="totalPilihan" value="{{isset($loop) ? $loop : 0}}" />
											</tbody>
											<tfoot id="actionPilihan">
												<tr>
													<td colspan="2">Tambah Panduan Pembayaran Baru</td>
													<td class="center"><button type="button" class="btn btn-bordered-success waves-effect waves-light btn-block" onclick="addPilihan();"><i class="mdi mdi-plus"></i> Tambah</button></td>
												</tr>
											</tfoot>
										</table>
									</div>
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

	autocomplete('JenisBiayaID_list','','Pilih Komponen Biaya');

	// Prevent normal form submission - always use AJAX
	$('#f_tarif_sks').on('submit', function(e) {
		e.preventDefault();
		e.stopImmediatePropagation();
		savedata(this);
		return false;
	});

	function addPilihan()
	{
		var nomor		= $('#totalPilihan').val();
		var temp		= '';

		temp			+= '<tr class="item_' + nomor + '">';
		temp			+= '<td colspan="2">';
		temp			+= '<p>Judul</p><input type="text" name="NamaPanduan[]" class="form-control">';
		temp			+= '</td>';

		temp			+= '<td class="center" rowspan="2">';
		temp			+= '<button type="button" class="btn btn-bordered-danger waves-effect waves-light btn-block btn-pilihan" onClick="deleteRow(' + nomor + ');"><i class="mdi mdi-delete"></i> Hapus</button>';
		temp			+= '</td>';
		temp			+= '</tr>';

		temp			+= '<tr class="item_' + nomor + '">';

		temp			+= '<td colspan="2">';
		temp			+= '<p>Panduan</p><textarea name="TextCaraBayar[]" class="tinymce" id="TextCaraBayar_<?= $loop?>" rows="10"></textarea>';
		temp			+= '</td>';

		temp			+= '</tr>';

		nomor++;

		$('#bodyPilihan').append(temp);

		tiny();
	}

	function deleteRow(id)
	{
		swal({
		  title: "Apakah anda yakin ?",
		  text: "Anda akan menghapus item panduan pembayaran.",
		  type: "warning",
		  showCancelButton: true,
		  confirmButtonColor: "#DD6B55",
		  confirmButtonText: "Ya",
		  cancelButtonText: "Batal"
		}).then(
		function(){
			$('.item_' + id).remove();
		});
	}

	function tiny(){
		if(tinymce){
			tinymce.triggerSave();
			tinymce.EditorManager.editors = [];
			console.log('tinymce');
		}


		tinymce.init({
			selector: '.tinymce',
			height: 200,
			plugins: [
				"advlist autolink lists link charmap print preview hr anchor pagebreak",
				"searchreplace wordcount visualblocks visualchars code fullscreen",
				"insertdatetime nonbreaking save table contextmenu directionality",
				"emoticons template paste textcolor colorpicker textpattern"
			],
			toolbar: "undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link",
			automatic_uploads: true,
			relative_urls: false,
			remove_script_host: false,
		});
	}
	tiny();

$('.currency').mask('#.##0', {reverse: true});
$('.currency').trigger('input');

function savedata(formz){
	$('.currency').unmask();
	tinymce.triggerSave();

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
						window.location="{{ url('channel_pembayaran') }}";
					}

					if({{$save}} == '2')
					{
						window.location="{{ url('channel_pembayaran/view/' . ($row->ID ?? '')) }}";
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
