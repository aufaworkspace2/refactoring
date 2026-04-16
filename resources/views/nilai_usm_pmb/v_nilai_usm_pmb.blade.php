@extends('layouts.template1')

@section('content')
<style type="text/css">
    .select2-container .select2-selection--single {
        height: 38px;
    }
    .select2-container--default .select2-selection--single {
        border: 1px solid #ddd;
        border-radius: 4px;
    }
</style>

<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-md-12">
                <div class="button-list">
                    <button class="btn btn-bordered-danger waves-effect width-md waves-light" id="btnDelete" data-placement="top" title="Silahkan pilih data terlebih dahulu." data-toggle="modal" disabled>
                        <i class="mdi mdi-delete"></i> {{ __('app.delete') }}
                    </button>
                </div>
                <div class="alert alert-info">
                    <i class="fa fa-info"></i>
                        &nbsp; Info	: <br>
                        &nbsp; - Pertama pilih aksi yang akan terlebih dahulu	<br>
                        &nbsp; - Lalu centang mahasiswa yang akan diberlakukan aksi yang sudah dipilih	<br>
                        &nbsp; - Lalu klik submit	<br> <br>
                        &nbsp; Catatan : <br>
                        &nbsp; - Aksi Set Lulus USM -> Dilakukan untuk meluluskan tes USM mahasiswa (Sesuai yang centang)	<br>
                        &nbsp; - Aksi Set Tidak Lulus USM -> Dilakukan untuk tidak meluluskan tes USM mahasiswa (Sesuai yang centang) <br>
                        &nbsp; - Aksi Simpan Nilai -> Dilakukan untuk menyimpan nilai mahasiswa (Sesuai yang diisikan)	<br>

                </div>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group col-md-3">
                <label class="col-form-label"><h5 class="mb-0">Gelombang</h5></label>
                <select class="gelombang form-control select2" onchange="change_gelombang_detail_pmb()">
                    <option value=""> -- {{ __('app.view_all') }} -- </option>
                    @foreach($data_gelombang as $g)
                        <option value="{{ $g->id }}" {{ $selected_gelombang == $g->id ? 'selected' : '' }}>
                            {{ $g->kode }} || {{ $g->nama }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-6">
                <label class="col-form-label"><h5 class="mb-0">Gelombang Detail</h5></label>
                <select class="gelombang_detail form-control select2" onchange="filter()">
                    <option value=""> -- {{ __('app.view_all') }} -- </option>
                    @foreach($data_gelombang_detail as $gd)
                        <option value="{{ $gd->id }}"
                            {{ $selected_gelombang_detail == $gd->id ? 'selected' : '' }}
                            data-gelombang-id="{{ $gd->gelombang_id }}">
                            {{ $gd->nama }}
                        </option>
                    @endforeach
                </select>
            </div>


            {{-- Commented out fields from CI3 - not used --}}
            {{-- <div class="form-group col-md-3">
				<label class="col-form-label"><h5 class="mb-0">Program Kuliah</h5></label>
				<select class="ProgramID form-control" onchange="filter()">
					<option value=""> -- {{ __('app.view_all') }} -- </option>
					@foreach(get_all('program') as $row)
						<option value="{{ $row->ID }}">{{ $row->ProgramID }} || {{ $row->Nama }}</option>
					@endforeach
				</select>
            </div> --}}

            {{-- <div class="form-group col-md-3">
					<label class="col-form-label"><h5 class="mb-0">Pilihan Program Studi</h5></label>
					<select class="pilihan1 form-control" onchange="filter();">
					<option value="">-- {{ __('app.view_all') }} --</option>
					@foreach(get_all('programstudi') as $row)
						<option value="{{ $row->ID }}">{{ $row->ProdiID }} || {{ get_field($row->JenjangID,'jenjang') }} || {{ $row->Nama }}</option>
					@endforeach
					</select>
			</div> --}}
			<div class="form-group col-md-3">
				<label class="col-form-label"><h5 class="mb-0">Status Ujian</h5></label>
				<select class="statustest form-control select2" onchange="filter()">
					<option value=""> -- {{ __('app.view_all') }} -- </option>
					<option value="selesai" {{ $selected_statustest == 'selesai' ? 'selected' : '' }}>Selesai Ujian</option>
					<option value="belum" {{ $selected_statustest == 'belum' ? 'selected' : '' }}>Belum Selesai Ujian</option>
				</select>
            </div>
            <div class="form-group col-md-3">
				<label class="col-form-label"><h5 class="mb-0">Urutkan dengan</h5></label>
				<div class="row">
					<div class="col-md-6">
						<select name="orderby" class="form-control orderby select2" id="orderby"  onchange="filter()">
							<option value="mahasiswa.urutall_pmb" {{ $selected_orderby == 'mahasiswa.urutall_pmb' ? 'selected' : '' }}>Urut Verifikasi</option>
							<option value="mahasiswa.Nama" {{ $selected_orderby == 'mahasiswa.Nama' ? 'selected' : '' }}>Nama</option>
							<option value="mahasiswa.noujian_pmb" {{ $selected_orderby == 'mahasiswa.noujian_pmb' ? 'selected' : '' }}>No Ujian</option>
							<option value="mahasiswa.nilai_pmb" {{ $selected_orderby == 'mahasiswa.nilai_pmb' ? 'selected' : '' }}>Nilai</option>
						</select>
					</div>
					<div class="col-md-6">
						<select name="descasc" class="form-control descasc select2" id="descasc" onchange="filter()">
							<option value="DESC" {{ $selected_descasc == 'DESC' ? 'selected' : '' }}>Z-A</option>
							<option value="ASC" {{ $selected_descasc == 'ASC' ? 'selected' : '' }}>A-Z</option>
						</select>
					</div>
				</div>
            </div>

            <div class="form-group col-md-9">
            	<label class="col-form-label"><h5 class="mb-0">{{ __('app.keyword_legend') }}</h5></label>
				<div class="row">
					<div class="col-md-3">
						<select name="viewpage" class="form-control viewpage select2" id="viewpage" onchange="filter()">
							<option value="10" {{ $selected_viewpage == '10' ? 'selected' : '' }}>10</option>
							<option value="25" {{ $selected_viewpage == '25' ? 'selected' : '' }}>25</option>
							<option value="50" {{ $selected_viewpage == '50' ? 'selected' : '' }}>50</option>
							<option value="100" {{ $selected_viewpage == '100' ? 'selected' : '' }}>100</option>
							<option value="200" {{ $selected_viewpage == '200' ? 'selected' : '' }}>200</option>
							<option value="all" {{ $selected_viewpage == 'all' ? 'selected' : '' }}>-- Semua --</option>
						</select>
					</div>
					<div class="col-md-9">
						<input type="text" class="form-control keyword" onkeyup="filter()" placeholder="{{ __('app.keyword') }} .."/>
					</div>
				</div>
            </div>
		</div>
	</div>
</div>
<div class="card">
	<div class="card-body">
		<div id="konten"></div>
	</div>
</div>

<div id="modal-table-all" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modal-table-all" aria-hidden="true">
	<div class="modal-dialog">
		<form id="f_uploadexcel" action="{{ url('nilai_usm_pmb/upload_excel') }}" enctype="multipart/form-data">
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title" id="modal-table-all">Import Nilai</h4>
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				</div>

				<div class="modal-body">
					<div class="table-responsive">
						<table style="border:none">
							<tr>
								<td style="width:30%">Download Template</td>
								<td style="width:10%">:</td>
								<td style="width:60%">
									<a target="_blank" href="{{ url('nilai_usm_pmb/template') }}" >Download</a>
								</td>
							</tr>
							<tr>
								<td style="width:30%">Upload Template Excel</td>
								<td style="width:10%">:</td>
								<td style="width:60%"><input type="file" name="fileUpload" id="fileUpload" /></td>
							</tr>
						</table>
					</div>
				</div>
				<div class="modal-footer">
					<button class="btn btn-small btn-success btnUpload" type="button" onclick="$('#f_uploadexcel').submit()" id="btnUpload">Upload</button>
					<button type="button" class="btn btn-danger waves-effect waves-light" data-dismiss="modal">Batal</button>
				</div>
			</div>
		</form>
	</div>
</div>
@endsection

@push('scripts')
<script type="text/javascript">
$.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

// Initialize Select2
$(document).ready(function() {
    $('.select2').select2({
        placeholder: function(){
            $(this).data('placeholder');
        },
        allowClear: true,
        width: '100%'
    });

    filter();

    // Filter gelombang detail based on selected gelombang
    var selectedGelombang = $('.gelombang').val();
    if (selectedGelombang) {
        filterGelombangDetail(selectedGelombang);
    }
});

function change_gelombang_detail_pmb() {
    var selectedGelombang = $('.gelombang').val();
    if (selectedGelombang) {
        filterGelombangDetail(selectedGelombang);
    } else {
        $('.gelombang_detail').html('<option value=""> -- {{ __('app.view_all') }} -- </option>');
        filter();
    }
}

function filterGelombangDetail(gelombang_id) {
    // Filter options that belong to selected gelombang
    var options = $('.gelombang_detail option').filter(function() {
        return !$(this).attr('value') || $(this).data('gelombang-id') == gelombang_id;
    });

    $('.gelombang_detail').empty().append(options);

    // Re-initialize select2 after updating options
    $('.gelombang_detail').trigger('change');
    filter();
}

function filter(url) {
    if(url == null) url = "{{ url('nilai_usm_pmb/search') }}";

    $.ajax({
        type: "POST",
        url: url,
        data: {
            keyword : $(".keyword").val(),
            bayar : '{{$bayar}}',
            gelombang : $(".gelombang").val(),
            gelombang_detail : $(".gelombang_detail").val(),
            pilihan1 : $(".pilihan1").val(),
            pilihan2 : $(".pilihan2").val(),
            program : $(".ProgramID").val(),
            statustest : $(".statustest").val(),
            orderby : $(".orderby").val(),
            descasc : $(".descasc").val(),
            viewpage : $(".viewpage").val(),
        },
        beforeSend:function(data){
            $('#konten').html('<center><i class="fa fa-spin fa-spinner"></i> Silahkan Tunggu...</center>');
        },
        success: function(data) {
            $("#konten").html(data);
            // Re-initialize select2 after AJAX load
            setTimeout(function() {
                $('.select2').select2({
                    placeholder: function(){ $(this).data('placeholder'); },
                    allowClear: true,
                    width: '100%'
                });
            }, 100);
            bindFormSubmitHandler(); // Bind form submit handler
        }
    });
    return false;
}

// Bind form submit handler setelah AJAX load
function bindFormSubmitHandler() {
    // Unbind dulu untuk menghindari multiple binding
    $('#f_save_nilai_usm_pmb').off('submit');
    
    // Bind event handler
    $('#f_save_nilai_usm_pmb').on('submit', function(e){
        e.preventDefault();
        e.stopImmediatePropagation();
        
        var form = $(this);
        var formData = new FormData(this);
        var actionUrl = form.attr('action');
        
        $.ajax({
            type: 'POST',
            url: actionUrl,
            data: formData,
            cache: false,
            contentType: false,
            dataType: 'json',
            processData: false,
            beforeSend: function() {
                $('#btnSubmit').attr('disabled', 'disabled');
            },
            success: function(data) {
                if (data.status == '1' || data.status == 1) {
                    // Show success alert
                    $(".alert-success").show();
                    $(".alert-success-content").html(data.message || "{{ __('app.alert-success') }}");
                    
                    // Animate background color
                    $(".alert-success").animate({ backgroundColor: "#dff0d8" }, 1000)
                        .animate({ backgroundColor: "#b6ef9e" }, 1000)
                        .animate({ backgroundColor: "#dff0d8" }, 1000)
                        .animate({ backgroundColor: "#b6ef9e" }, 1000);
                    
                    // Auto hide after 10 seconds
                    window.setTimeout(function() {
                        $(".alert-success").slideUp();
                    }, 10000);
                    
                    // Refresh data
                    filter();
                } else if (data.status == '0' || data.status == 0) {
                    // Show error alert
                    $(".alert-error").show();
                    $(".alert-error-content").html(data.message || "Ups, Ada Kesalahan");
                    
                    $(".alert-error").animate({ backgroundColor: "#ec9b9b" }, 1000)
                        .animate({ backgroundColor: "#df3d3d" }, 1000)
                        .animate({ backgroundColor: "#ec9b9b" }, 1000)
                        .animate({ backgroundColor: "#df3d3d" }, 1000);
                    
                    window.setTimeout(function() {
                        $(".alert-error").slideUp();
                    }, 6000);
                    
                    $('#btnSubmit').removeAttr('disabled');
                }
            },
            error: function(xhr, status, error) {
                // Show error alert
                $(".alert-error").show();
                $(".alert-error-content").html("{{ __('app.alert-error') }}: " + error);
                
                $(".alert-error").animate({ backgroundColor: "#ec9b9b" }, 1000)
                    .animate({ backgroundColor: "#df3d3d" }, 1000)
                    .animate({ backgroundColor: "#ec9b9b" }, 1000)
                    .animate({ backgroundColor: "#df3d3d" }, 1000);
                
                window.setTimeout(function() {
                    $(".alert-error").slideUp();
                }, 6000);
                
                $('#btnSubmit').removeAttr('disabled');
            }
        });
        
        return false;
    });
}

$('.keyword').keyup(fncDelay(function (e) {
    filter();
}, 500));

function checkall(chkAll,checkid) {
    if (checkid != null) {
        if (checkid.length == null) checkid.checked = chkAll.checked;
        else for (i=0;i<checkid.length;i++) checkid[i].checked = chkAll.checked;
        $("input:checkbox[name='checkID[]']").parents('tr').removeClass('table-danger');
        $("input:checkbox[name='checkID[]']:checked").parents('tr').addClass('table-danger');
    }
}

function show_btnSubmit(){
    var selectedAction = $('#action_do').val();
    if(selectedAction == '') {
        $('#btnSubmit').attr('disabled', true);
    } else {
        $('#btnSubmit').removeAttr('disabled');
    }

    i=0; hasil = false;
    var checkCount = document.getElementsByName('checkID[]').length;
    while(checkCount > i) {
        var checkname = document.getElementById('checkID'+i);
        if(checkname && checkname.checked){ hasil = true; }
        i++;
    }
    if(hasil == true) {
        $('#btnSubmit').removeAttr('disabled');
    } else if(selectedAction == '') {
        $('#btnSubmit').attr('disabled', true);
    }
}

window.show_btnDelete = function(){
    i=0; hasil = false;
    var checkElements = document.getElementsByName('checkID[]');
    while(checkElements.length > i) {
        var checkname = document.getElementById('checkID'+i);
        if(checkname && checkname.checked){ hasil = true; }
        i++;
    }
    if(hasil == true) {
        if($('#btnDelete').length) {
            $('#btnDelete').removeAttr('disabled');
            $('#btnDelete').attr('href','#hapus');
        }
    } else {
        if($('#btnDelete').length) {
            $('#btnDelete').attr('disabled','disabled');
            $('#btnDelete').attr('href','#');
        }
    }
}
show_btnDelete();

$("input:checkbox[name='checkID[]']").click(function(){
    if(this.checked == true){ $(this).parents('tr').addClass('table-danger'); }
    else { $(this).parents('tr').removeClass('table-danger'); }
});

function hapusdata(){
    $.ajax({
        type: "POST",
        url: "{{ url('nilai_usm_pmb/delete') }}",
        data: $("#f_delete_nilai").serialize(),
        success: function(data) {
            $("#hapus").modal("hide");
            $('body').removeClass('modal-open');
            $('.modal-backdrop').remove();
            filter();
            $(".alert-success").show();
            $(".alert-success-content").html("{{ __('app.alert-success-delete') }}");
            window.setTimeout(function() { $(".alert-success").slideUp(); }, 10000);
        }
    });
}
</script>
@endpush
