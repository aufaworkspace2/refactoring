<script>
function openmodul(id,jml){
	for(i=0;i<=jml;i++){
		$("#openmodul"+id+"-"+i).slideToggle();
	}
	var cek = $('#grp'+id).attr('class');
	if(cek == 'checked_tabel')
	{
		$('#grp'+id).removeClass('checked_tabel');
		$('.panah'+id).removeClass('mdi-chevron-down');
		$('.panah'+id).addClass('mdi-chevron-right');
	}
	else
	{
		$('#grp'+id).addClass('checked_tabel');
		$('.panah'+id).removeClass('mdi-chevron-right');
		$('.panah'+id).addClass('mdi-chevron-down');
	}
}

function opensubmodul(id,jml){
	for(i=0;i<=jml;i++){
		$("#opensubmodul"+id+"-"+i).slideToggle();
	}
	var cek = $('#mdul'+id).attr('class');
	if(cek == 'buka')
	{
		$('#mdul'+id).removeClass('buka');
		$('#mdul'+id).addClass('tutup');
		$('.panah2'+id).removeClass('mdi-chevron-down');
		$('.panah2'+id).addClass('mdi-chevron-right');
	}
	else
	{
		$('#mdul'+id).removeClass('tutup');
		$('#mdul'+id).addClass('buka');
		$('.panah2'+id).removeClass('mdi-chevron-right');
		$('.panah2'+id).addClass('mdi-chevron-down');
	}
}

function checkall2(chkAll,id,jml) {
	for(i=0;i<=jml;i++){
		$("#openmodul"+id+"-"+i).slideDown();
	}
	
	var cek = $('#grp'+id).attr('class');
	if(cek != 'checked_tabel')
	{
		$('#grp'+id).addClass('checked_tabel');
		$('.panah'+id).addClass('mdi-chevron-down');
	}
	var checkid = $("input:checkbox[class^='modul"+id+"']");
	if (checkid != null) 
	{
		if (checkid.length == null) checkid.checked = chkAll.checked;
        else for (i=0;i<checkid.length;i++) checkid[i].checked = chkAll.checked;
		
		if(chkAll.checked == true)
		{
			$('.warna'+id).removeClass('btn-success');
			$('.warna'+id).addClass('btn-danger');
			$('.checklabel'+id).html('Uncheck All');
		}
		else
		{
			$('.warna'+id).removeClass('btn-danger');
			$('.warna'+id).addClass('btn-success');
			$('.checklabel'+id).html('Check All');
		}
    }
}


function unchekall(){
	$("input:checkbox[class='master']:checked").attr('checked',false);
}
</script>

<form id="f_delete_levelmodul" action="{{ route('levelmodul.delete') }}" >
@csrf
<input type="hidden" name="LevelID" value="{{ $my_level }}">
<div class="row mb-3">
	<div class="col-md-4 align-self-text-center">
		<h5>Level : {!! get_field($my_level, 'level') !!}</h5>
	</div>
	<div class="col-md-8 b3">
		<div class="button-list float-right">	
			<button type="submit" class="btn btn-bordered-primary waves-effect btnSave width-md waves-light">Simpan Data <icon class="fa fa-check icon-white-t"></icon></button>
			<button type="button" onclick="back()" class="btn btn-bordered-danger waves-effect  width-md waves-light">Kembali <icon class="icon-share-alt icon-white-t"></icon></button>
		</div>
	</div>
	<div class="col-md-8 b4" style="display:none">
		<div class="button-list float-right">	
			<button class='btn btn-bordered-primary waves-effect  width-md waves-light' type='button' disabled><i class='fa fa-spin fa-spinner'></i> Silahkan tunggu...</button>
		</div>
	</div>
</div>
<h4><i class="mdi mdi-format-list-bulleted mr-1"></i>Link SIAK Application</h4>
<div class="table-responsive">
	<table class="table table-bordered mb-0 table-hover tablesorter">
		@php $no=$offset; $i=0; 
		
		 $modulgrups = \Illuminate\Support\Facades\DB::table('modulgrup')->where('AksesID',1)->orderBy('Urut','ASC')->get();
		 foreach($modulgrups as $row) { 
				 $A = \Illuminate\Support\Facades\DB::table('modul')->where('MdlGrpID', $row->ID)->get();
		@endphp
			<tr id="grp{{ $row->ID }}">
			 <td style="cursor:pointer; " colspan="5" 
			 onclick="openmodul('{{ $row->ID }}','{{ count($A) }}')"><h5>{{ ++$i . '. ' . $row->Nama }}
			 <i class="panah{{ $row->ID }} mdi mdi-chevron-right float-right"></i>	
			</h5>
			 </td>
			 </tr>
			  
			  <tr style="display:none;" id="openmodul{{ $row->ID }}-0">	
				<td  colspan="5" class="text-center" >
				<label class="warna{{ $row->ID }} btn btn-success btn-phone-block">
				<input style="margin:0 5px; visibility:hidden; position:absolute;" type="checkbox" name="checkAll" id="checkAll" onClick="checkall2(this,'{{ $row->ID }}','{{ count($A) }}'); " />
				<i class="fa fa-check icon-white-t"></i> <b class="checklabel{{ $row->ID }}">Check All</b></label>
				</td>
			
			 @php 
				
				 if(count($A) > 0) 
				 {
					 echo '</tr>';
					 $x1=1;
					 foreach($A as $raw)
					 {
						$my_modul = \Illuminate\Support\Facades\DB::table('levelmodul')->where('type','modul')->where('LevelID', $my_level)->where('ModulID', $raw->ID)->first();
						$B = \Illuminate\Support\Facades\DB::table('submodul')->where('ModulID', $raw->ID)->get();
						
						if(count($B) > 0){
							$stl="cursor:pointer;";
							$rowspansub = "colspan='5'";
						}
						else
						{
							$stl="";
							$rowspansub = "";
						}
					 @endphp
						
						<tr style="display:none;" id="openmodul{{ $row->ID }}-{{ $x1 }}" >
						<td style="{{ $stl }}" {!! $rowspansub !!} onclick="opensubmodul('{{ $raw->ID }}','{{ count($B) }}')"  id="mdul{{ $raw->ID }}" class="">
						&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>{{ $i.'.'.$x1.'. '.$raw->Nama }}</b>
						@if(count($B) > 0)<i class="panah2{{ $raw->ID }} mdi mdi-chevron-right float-right"></i>@endif
						</td>
						@if(count($B) < 1)
						<td><input class="modul{{ $row->ID }} mr-1" id="lihat_modul_{{ $raw->ID }}" name="lihat_modul_{{ $raw->ID }}" type="checkbox" value="{{ $raw->ID }}" {{ ($my_modul && $my_modul->Read == 'YA') ? "checked" : "" }} >Lihat</td>
						<td><input class="modul{{ $row->ID }} mr-1" id="input_modul_{{ $raw->ID }}" name="input_modul_{{ $raw->ID }}" type="checkbox" value="{{ $raw->ID }}" {{ ($my_modul && $my_modul->Create == 'YA') ? "checked" : "" }}>Input</td>
						<td><input class="modul{{ $row->ID }} mr-1" id="update_modul_{{ $raw->ID }}" name="update_modul_{{ $raw->ID }}" type="checkbox" value="{{ $raw->ID }}" {{ ($my_modul && $my_modul->Update == 'YA') ? "checked" : "" }}>Update</td>
						<td><input class="modul{{ $row->ID }} mr-1" id="delete_modul_{{ $raw->ID }}" name="delete_modul_{{ $raw->ID }}" type="checkbox" value="{{ $raw->ID }}" {{ ($my_modul && $my_modul->Delete == 'YA') ? "checked" : "" }}>Delete</td>
						@endif
						@php
							  if(count($B) > 0) 
							  {
								echo '</tr>';
								$x2=0;
								foreach($B as $riw)
								{
								$my_submodul = \Illuminate\Support\Facades\DB::table('levelmodul')->where('type','submodul')->where('LevelID', $my_level)->where('ModulID', $riw->ID)->first();
						
								@endphp
									<tr style="display:none;" id="opensubmodul{{ $raw->ID }}-{{ ++$x2 }}">
									<td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;{{ $i.'.'.$x1.'.'.$x2.'. '.$riw->Nama }}</td>
									<td><input class="modul{{ $row->ID }} mr-1" id="lihat_submodul_{{ $riw->ID }}" name="lihat_submodul_{{ $riw->ID }}" type="checkbox" {{ ($my_submodul && $my_submodul->Read == 'YA') ? "checked" : "" }} >Lihat</td>
									<td><input class="modul{{ $row->ID }} mr-1" id="input_submodul_{{ $riw->ID }}" name="input_submodul_{{ $riw->ID }}" type="checkbox" {{ ($my_submodul && $my_submodul->Create == 'YA') ? "checked" : "" }}>Input</td>
									<td><input class="modul{{ $row->ID }} mr-1" id="update_submodul_{{ $riw->ID }}" name="update_submodul_{{ $riw->ID }}" type="checkbox" {{ ($my_submodul && $my_submodul->Update == 'YA') ? "checked" : "" }}>Update</td>
									<td><input class="modul{{ $row->ID }} mr-1" id="delete_submodul_{{ $riw->ID }}" name="delete_submodul_{{ $riw->ID }}" type="checkbox" {{ ($my_submodul && $my_submodul->Delete == 'YA') ? "checked" : "" }}>Delete</td>
									</tr>
								@php 
								}
								echo '<tr>';
							 }
						 @endphp
						 </tr>
					 @php 
					$x1++; } 
					 echo '<tr>';
				 }
			@endphp
			</tr>
			@php } @endphp
			
	</table>
</div>


<div class="row">
	<div class="col-md-12 b1">
		<div class="button-list float-right">	
			<button type="submit" class="btn btn-bordered-primary waves-effect btnSave width-md waves-light">Simpan Data</button>
			<button type="button" onclick="back()" class="btn btn-bordered-danger waves-effect  width-md waves-light">Kembali</button>
		</div>
	</div>
	<div class="col-md-12 b2" style="display:none">
		<div class="button-list float-right">	
			<button class='btn btn-bordered-primary waves-effect  width-md waves-light' type='button' disabled><i class='fa fa-spin fa-spinner'></i> Silahkan tunggu...</button>
		</div>
	</div>
</div>
</form>
<!--  Modal Hapus Semua -->

<!--  End Modal Hapus Semua -->


<!-------------------------------------------------------------------- Javascript Area -------------------------------------------------------------------->

<script>
$("#f_delete_levelmodul").submit(function(){
		$.ajax({
		type: "POST",
		url: $("#f_delete_levelmodul").attr('action'),
		data: $("#f_delete_levelmodul").serialize(),
		dataType: 'json',
		beforeSend: function(r){
			$(".b1").hide(1);
			$(".b2").show(1);
			$(".b3").hide(1);
			$(".b4").show(1);
		},
		success:function(response){
			$(".b1").show(1);
			$(".b2").hide(1);
			$(".b3").show(1);
			$(".b4").hide(1);

			// Remove rows based on response
			if(response.deleted) {
				response.deleted.forEach(function(id) {
					$('.levelmodul_' + id).remove();
				});
			}

			filter();

            $( ".alert-success" ).animate({
					backgroundColor: "#dff0d8"
			}, 1000 );
			$( ".alert-success" ).animate({
					backgroundColor: "#b6ef9e"
			}, 1000 );
			$( ".alert-success" ).animate({
					backgroundColor: "#dff0d8"
			}, 1000 );
			$( ".alert-success" ).animate({
					backgroundColor: "#b6ef9e"
			}, 1000 );

			$(".alert-success").show();
			$(".alert-success-content").html("Data berhasil dihapus.");
			window.setTimeout(function() { $(".alert-success").slideUp(); }, 10000);

		},
		error: function(data){
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
			$(".alert-error-content").html("Data gagal disimpan.");
			window.setTimeout(function() { $(".alert-error").slideUp( "slow" ); }, 6000);
		}
		});
		return false;
	});
</script>


<!------------------------------------------------------------------ End Javascript Area ------------------------------------------------------------------>
