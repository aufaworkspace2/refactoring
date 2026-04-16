<div class="text-right">
	<b>Keterangan Warna</b>
	<br>
	<span class="label label-success" style="background:rgb(233, 255, 228);border:1px solid #000;border-radius:3px;">&nbsp;&nbsp;&nbsp;</span> Sudah ada transaksi [KRS] (Tidak Bisa Dihapus)<br>
	<span class="label" style="background:#fff;border:1px solid #000;border-radius:3px;">&nbsp;&nbsp;&nbsp;</span> Belum ada transaksi (Bisa Dihapus)
</div>
<div class="row mb-2">
	<div class="col-md-12">
		{!! $total_row !!}
	</div>
</div>
<?php
	$Qlvl = DB::table('leveluser')->where('UserID', Session::get('UserID'))->first();
?>
<form id="f_delete_mahasiswa" action="{{ url('mahasiswa/delete') }}">
    @csrf
	<div class="table-responsive">
        <table class="table table-hover table-bordered tablesorter">
			<thead class="bg-primary text-white">
				<tr>

				  @if($Delete == 'YA')
					<th width="2%">
						<div class="checkbox checkbox-info">
							<input type="checkbox" name="checkAll" id="checkAll" onClick="checkall(this,document.forms.namedItem('f_delete_mahasiswa')); show_btnDelete();">
							<label for="checkAll"></label>
						</div>
					</th>
				  @endif

					<th class="text-center" width="2%">No.</th>
					<th class="text-center">{{ __('mahasiswa.Nama') }}</th>
					<th class="text-center">{{ __('mahasiswa.NPM') }}</th>
					<th class="text-center">{{ __('mahasiswa.TahunMasuk') }}</th>
					<th class="text-center">{{ __('mahasiswa.ProgramID') }}</th>
					<th class="text-center">{{ __('mahasiswa.ProdiID') }}</th>
					<th class="text-center">{{ __('mahasiswa.JenjangID') }}</th>
					<th class="text-center">Kurikulum</th>
					<th class="text-center">Ketua Kelas</th>
					<th class="text-center">Konsentrasi</th>
					<th class="text-center">{{ __('mahasiswa.StatusMhswID') }}</th>
					<th class="text-center">Lihat Dokumen</th>
					<th class="text-center">Cetak KTM</th>
				</tr>
			</thead>
			<tbody>
            <?php $no=$offset; $i=0; ?>
            @foreach($query as $row)
            	<?php
                $rencanastudiID = DB::table('rencanastudi')->where('MhswID', $row->ID)->first();

            	if($rencanastudiID) {
                    $bg = "rgb(233, 255, 228) none repeat scroll 0% 0%";
                    $disabled = 'disabled';
                } else {
                    $bg = "";
                    $disabled = '';
                }
				?>
				<tr class="mahasiswa_{{ $row->ID }}" style="background:{{ $bg }};">
				  @if($Delete == 'YA')
					@if(empty($disabled))
						<td class="align-middle">
							<div class="checkbox checkbox-info">
								<input type="checkbox" name="checkID[]" id="checkID{{ $i }}" onclick="show_btnDelete()" value="{{ $row->ID }}" >
								<label for="checkID{{ $i }}"></label>
							</div>
						</td>
					 <?php $i++; ?>
                    @else
						<td></td>
					@endif
				  @endif
					<td class="text-center align-middle">{{ ++$no }}.</td>
					<td class="align-middle">
						<div class="media thumbnail">
							{!! get_photo($row->NPM ?? '', $row->Foto ?? '', $row->Kelamin ?? '', 'mahasiswa', 'photo_profile') !!}
							<div class="media-body">
								@if($Update == 'YA')
									<a href="{{ url('mahasiswa/view/'.$row->ID) }}" >{{ $row->Nama }}</a>
								@else
									{{ $row->Nama }}
								@endif
							</div>
						</div>
					</td>

					<td class="text-center align-middle">
					@if($Update == 'YA')
						<a href="{{ url('mahasiswa/view/'.$row->ID) }}" >{{ $row->NPM }}</a>
					@else
					    {{ $row->NPM }}
					@endif
					</td>

					<td class="text-center align-middle"><span class="label">{{ $row->TahunMasuk }}</span></td>
					<td class="align-middle">{{ get_field($row->ProgramID,'program') }}</td>
					<td class="align-middle">{{ get_field($row->ProdiID,'programstudi') }}</td>
					<td class="text-center align-middle">{{ get_field($row->JenjangID,'jenjang') }}</td>
					<td class="align-middle">{{ get_field($row->KurikulumID,'kurikulum') }}</td>
					<td class="text-center text-nowarp align-middle">
						<div style="width:195px;">
							<input class="ketuakelas" onchange="changeStatusKetua(this)" value="{{ $row->ID }}" type="checkbox" data-on-color="success" data-off-color="danger" {{ ($row->KetuaKelas == 1) ? "checked" : "" }} data-on-text="Ya" data-off-text="Bukan"/>
						</div>
					</td>
					<script>
						$('.ketuakelas').bootstrapSwitch();
					</script>
					<td class="align-middle">
						<div id="s_edit{{ $row->ID }}" style="display:none">
						{{ get_field($row->KonsentrasiID,'konsentrasi') }}
						</div>
						<div id="v_edit{{ $row->ID }}">
							<select class="form-control" id="U_KonsentrasiID{{ $row->ID }}" disabled style="width: 150px;">
								<option value="">Tidak ada</option>
								<?php
									$qKons = DB::table('konsentrasi')->where('ProdiID', $row->ProdiID)->get();
									foreach($qKons as $rKons){
										$sel = ($row->KonsentrasiID == $rKons->ID) ? 'selected':'';
								?>
								<option value="{{ $rKons->ID }}" {{ $sel }}>{{ $rKons->Nama }}</option>
								<?php } ?>
							</select>
						</div>
						<button type="button" class="btn btn-ch{{ $row->ID }} btn-info mt-1" onclick="$('.btn-ch{{ $row->ID }}').toggle();$('#U_KonsentrasiID{{ $row->ID }}').removeAttr('disabled')"><i class="fa fa-pencil-alt"></i></button>
						<button type="button" class="btn btn-ch{{ $row->ID }} btnsimpankons{{ $row->ID }} btn-success mt-1" style="display:none" onclick="saveKons({{ $row->ID }})"><i class="fa fa-save"></i></button>
						<button type="button" class="btn btn-ch{{ $row->ID }} btn-danger mt-1" style="display:none" onclick="$('.btn-ch{{ $row->ID }}').toggle();$('#U_KonsentrasiID{{ $row->ID }}').attr('disabled',true)"><i class="fa fa-times"></i></button>
					</td>
					<td class="text-center align-middle">{{ get_field($row->StatusMhswID,'statusmahasiswa') }}</td>
					<td class="text-center align-middle"><a href="javascript:void(0)" class="btn btn-danger" data-toggle="modal" data-target="#myModal" onclick="lihatdok({{ $row->ID }})">Lihat</a></td>
					<td class="text-center align-middle"><button class='btn btn-danger' type='button' onclick='window.open("{{ url('mahasiswa/ktm/'.$row->ID) }}");'>Cetak</button></td>
				</tr>
            @endforeach
			</tbody>
		</table>
		<div id="hapus" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="hapus" aria-hidden="true">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<h4 class="modal-title" id="hapus">{{ __('mahasiswa.confirm_header') }}</h4>
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
					</div>
					<div class="modal-body">
						<p>{{ __('mahasiswa.confirm_message') }}</p>
						<p class="data_name"></p>
					</div>
					<div class="modal-footer">
						<button type="submit" class="btn btn-danger waves-effect" >{{ __('mahasiswa.delete') }}</button>
						<button type="button" class="btn btn-primary waves-effect waves-light" data-dismiss="modal">{{ __('mahasiswa.close') }}</button>
					</div>
				</div>
			</div>
		</div>
	</div>
    <div class="row">
		<div class="col-md-12">
			<div class="pagination-wrapper">
				{!! $link !!}
			</div>
		</div>
	</div>

<div class="dokumen">
	<div id="myModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModal" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title" id="myModal">Lihat Dokumen</h4>
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				</div>
				<div class="modal-body">
					<div class="table-responsive">
						<table class="table table-hover table-bordered tablesorter">
							<thead class="bg-primary text-white">
								<tr>
									<th class="text-center" width="1%" class="text-center">No.</th>
									<th width="50%" class="text-center">Nama File</th>
									<th width="49%" class="text-center">Tanggal Upload</th>
								</tr>
							</thead>
							<tbody id="table_body">
							</tbody>
						</table>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-danger waves-effect waves-light" data-dismiss="modal">{{ __('mahasiswa.close') }}</button>
				</div>
			</div>
		</div>
	</div>
</div>
</form>

<script>
function lihatdok(id) {
	var	td = '';
	var no = 0;
	$.ajax({
		type: 'POST',
		url: '{{ url("mahasiswa/daftarFile") }}',
		data: {
			mahasiswaID : id,
            _token: '{{ csrf_token() }}'
		},
		dataType:'json',
		success: function (data) {
			if (data.length > 0) {
				$.each(data, function(index, value) {
					no++;
					td += '<tr>';
					td += '<td class="text-center"><small>' + no + '</small></td>';
					td += '<td><a href="javascript:void(0);" style="font-size:12px" onclick="showDocument(' + id + ",'" + value.File + "','" + value.jenis + "'" + ');" style="cursor: pointer; text-decoration: none;">' + value.File + '</a></td>';
					td += '<td><small>' + value.TanggalInput + '</small></td>';
					td += '</tr>';
				});
			} else {
				td += '<tr>'
				td += '<td colspan=4 class="text-center">Maaf Data Dokumen Tidak Ditemukan !</td>'
				td += '</tr>'
            }
			$('#table_body').html(td);
		}, error: function (jqXHR, textStatus, errorThrown) {
			alert('Maaf file tidak ditemukan ! '+textStatus);
		}
	});
}

function showDocument(id,namaFile,jenis) {
	window.open('{{ url("mahasiswa/showDocument") }}/?mahasiswaID=' + id + '&namaFile=' + namaFile + '&jenis=' + jenis, "_Blank");
}

tablesorter();
$("#f_delete_mahasiswa").submit(function(){
		$.ajax({
		type: "POST",
		url: $("#f_delete_mahasiswa").attr('action'),
		data: $("#f_delete_mahasiswa").serialize(),
		dataType: 'json',
		success:function(response){
            if(response.status === 'success' && response.removed_ids){
                // Remove rows based on response
                response.removed_ids.forEach(function(id) {
                    var className = '.' + response.class_prefix + id;
                    $(className).remove();
                });

                $("#hapus").modal("hide");
                setTimeout(function() {
                    $("body").removeClass("modal-open");
                    $(".modal-backdrop").remove();
                }, 300);
                filter();
                toastr.success("{{ __('mahasiswa.alert-success-delete') }}");
            }
		},
		error: function(data){
            toastr.error("{{ __('mahasiswa.alert-error-delete') }}");
		}
		});
		return false;
});


window.show_btnDelete = function(){
	i=0; hasil = false;
	var checkElements = document.getElementsByName('checkID[]');
	while(checkElements.length > i)
	{
		var checkname = document.getElementById('checkID'+i);
		if(checkname && checkname.checked)
		{
			hasil = true;
		}
		i++;
	}
	if(hasil == true) {
		if($('#btnDelete').length) {
			$('#btnDelete').removeAttr('disabled');
			$('#btnDelete').removeAttr('href');
			$('#btnDelete').removeAttr('title');
			$('#btnDelete').attr('href', '#hapus');
		}
	}
	else
	{
		if($('#btnDelete').length) {
			$('#btnDelete').attr('disabled','disabled');
			$('#btnDelete').attr('href','#');
			$('#btnDelete').attr('title', 'Pilih dahulu data yang akan di hapus');
		}
	}
}
show_btnDelete();

$("input:checkbox[name='checkID[]']").click(function(){
	if(this.checked == true){
		$(this).parents('tr').addClass('table-danger');
	}
	else
	{
		$(this).parents('tr').removeClass('table-danger');
	}
});

$('#btnDelete').click(function(){
    // Simplified for adaption - typically a confirmation text
    $('.data_name').html('Anda yakin ingin menghapus data-data terpilih?');
});

function changeStatusKetua(id)
{
	var status;
	if($(id).bootstrapSwitch('state') == true) {
		status = 1;
	} else {
		status = 0;
	}

 	$.ajax({
		url : "{{ url('mahasiswa/changeStatusKetua') }}",
		type: "POST",
		data: {
			mhswID : $(id).val(),
			status : status,
            _token: '{{ csrf_token() }}'
		},
		success: function(data){
			toastr.success("Data berhasil di perbarui!");
		}
	});
}

function saveKons(id){
	$.ajax({
		url:"{{ url('mahasiswa/saveKons') }}",
		data:{
			id : id,
			KonsentrasiID : $('#U_KonsentrasiID'+id).val(),
            _token: '{{ csrf_token() }}'
		},
		type:"POST",
		beforeSend: function(data){
			$('.btnsimpankons'+id).prop('disabled',true);
			$('.btnsimpankons'+id).html('<i class="fa fa-spin fa-spinner"></i>');
		},
		success: function(data) {
			$('.btnsimpankons'+id).prop('disabled',false);
			$('.btnsimpankons'+id).html('<i class="fa fa-save"></i>');

			if(data == 'error') {
				toastr.error("Update Data Gagal");
			} else {
				toastr.success("Update Data Berhasil");
				$('.btn-ch'+id).toggle();
				$('#U_KonsentrasiID'+id).attr('disabled',true);
			}
		},
		error: function(){
			$('.btnsimpankons'+id).prop('disabled',false);
			$('.btnsimpankons'+id).html('<i class="fa fa-save"></i>');
			toastr.error("Update Data Gagal");
		}
	});
}
</script>
