@extends('layouts.template1')
@section('content')
<div class="card">
	<div class="card-body">
		<div class="form-row">
			<div class="form-group col-md-3">
				<label class="col-form-label"><h5 class="mb-0">
					Tahun Akademik
				</h5></label>
				<select class="TahunID form-control" id="TahunID">
					@foreach(get_all('tahun') as $row)
						<option value="{{ $row->ID }}" {{ ($row->ID == ($TahunID ?? '')) ? 'selected' : '' }}>{{ $row->Nama ?? '' }}</option>
					@endforeach
				</select>
			</div>
			<div class="form-group col-md-3">
				<label class="col-form-label"><h5 class="mb-0">
					Program
				</h5></label>
				<select class="ProgramID form-control" id="ProgramID" onchange="changematkul()">
				<option value="">-- Lihat Semua --</option>
				@foreach(get_all('program') as $row)
					<option value="{{ $row->ID }}" {{ ($row->ID == ($ProgramID ?? '')) ? 'selected' : '' }}>{{ $row->Nama ?? '' }}</option>
				@endforeach
			</select>
			</div>
			<div class="form-group col-md-3">
				<label class="col-form-label"><h5 class="mb-0">
					Program Studi
				</h5></label>
				<select name='ProdiID' required class="form-control ProdiID" id="ProdiID" onchange="changematkul()">
					<option value="">-- Lihat Semua --</option>
					@foreach(get_all('programstudi') as $row)
						<option value="{{ $row->ID }}" {{ ($row->ID == ($ProdiID ?? '')) ? 'selected' : '' }}>{{ get_field($row->JenjangID, 'jenjang') }} - {{ $row->Nama ?? '' }}</option>
					@endforeach
				</select>
			</div>
			
			<div class="form-group col-md-3">
				<label class="col-form-label"><h5 class="mb-0">Cari Berdasarkan Dosen</h5></label>
				<select class="DosenID form-control" id="DosenID">
					<option value=""> -- Pilih -- </option>
					@foreach($list_dosen as $row)
						<option value="{{ $row->ID }}" {{ ($row->ID == ($DosenID ?? '')) ? 'selected' : '' }}>{{ $row->Title ?? '' }} {{ $row->Nama ?? '' }}, {{ $row->Gelar ?? '' }}</option>
					@endforeach
				</select>
			</div>
			<div class="form-group col-md-3">
				<label class="col-form-label"><h5 class="mb-0">Status Input</h5></label>
				<select class="Input form-control" id="Input" style="width: 100%">
					<option value=""> -- Pilih -- </option>
					<option value="1">Sudah Input Nilai</option>
					<option value="2">Belum Input Nilai</option>
				</select>
			</div>

			<div class="form-group col-md-3">
				<label class="col-form-label"><h5 class="mb-0">Status Publish</h5></label>
				<select class="Publish form-control" id="Publish" style="width: 100%">
					<option value=""> -- Pilih -- </option>
					<option value="1">Sudah Terpublish Semua</option>
					<option value="2">Belum Terpublish Semua</option>
				</select>
			</div>

			<div class="form-group col-md-4">
				<label class="col-form-label mt-2"><h5 class="m-0">Mata Kuliah</h5></label>
					<select class="MKID form-control" id="MKID" multiple>
						<option value=""> -- Lihat Semua -- </option>
					</select>
            </div>
			<!-- Search & Filter  -->
			<div class="form-group col-md-2">
				<label class="col-form-label mt-2"><h5 class="m-0">&nbsp;</h5></label>
				<div class="mt-0">
				<button type="button" onclick="filter()" class="btn btn-bordered-success waves-effect waves-light"><i class="fa fa-search"></i> Cari</button>
				<a href="{{ url('publish_nilai_uas') }}" class="btn btn-bordered-danger waves-effect waves-light"><i class="fa fa-refresh"></i> Reset Filter</a>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="card">
	<div class="card-body">
		<div id="konten">
			<center>
				<h4> --- PILIH DOSEN LALU KLIK TOMBOL CARI ---</h4>
			</center>
		</div>
	</div>
</div>

@push('scripts')
<script type="text/javascript">	

if(typeof autocomplete === 'function') {
    autocomplete('DosenID');
}

if($.fn.datetimepicker) {
    $('.textTanggal').datetimepicker({
        format:'Y-m-d',
        timepicker:false,
        scrollInput:false,
        onSelect: function () {
            $(this).trigger("focus").trigger("blur");
        }
    });
}

function modal_pdf_sk_kordinator()
{
	$('#modal_pdf_sk_kordinator').modal('show');
}

function modal_pdf_sk_mengajar()
{
	var DosenID = $("#DosenID").val();
	if(!DosenID){
		swal('Info','Silahkan Memilih Dosen Terlebih Dahulu Sebelum Cetak SK Tugas Mengajar','info');
		return;
	}

	$('#modal_pdf_sk_mengajar').modal('show');
}

function changetahun() {
	var url = "{{ url('tahun/changetahun') }}";
	
	$.ajax({
		type: "POST",
		url: url,
		data: {
            _token: "{{ csrf_token() }}",
			ProgramID : $(".ProgramID").val(),
		},
		success: function(data) {
			$(".TahunID").html(data);	
		}
	});
	return false;
}
// changetahun(); // Commented out if not needed initially or if it causes issues


function filter(url = null) {
	if(url == null)
	    url = "{{ url('publish_nilai_uas/search') }}";
	
	$.ajax({
		type: "POST",
		url: url,
		data: {
            _token: "{{ csrf_token() }}",
			DosenID : $("#DosenID").val(),
			TahunID : $("#TahunID").val(),
			ProgramID : $("#ProgramID").val(),
			ProdiID : $("#ProdiID").val(),
			Publish : $("#Publish").val(),
			Input : $("#Input").val(),
			JadwalID : '{{ $JadwalID ?? '' }}',
			MKID : $("#MKID").val(),
		},
		beforeSend: function(data) {
			$("#konten").html("<center><h3><i class='fa fa-spin fa-spinner'></i> Sedang Memuat Data.. </h3></center>");
		},
		success: function(data) {
			$("#konten").html(data);
		}
	});
	return false;
}

@if(!empty($JadwalID))
filter();
@endif

changematkul();
function changematkul(){	
	$.ajax({
		url:"{{ url('detailkurikulum/changemk_mknew') }}",
		type:"POST",
		data: {
            _token: "{{ csrf_token() }}",
			ProdiID : $("#ProdiID").val(),
			ProgramID : $("#ProgramID").val(),
			NO_VIEW_ALL : 1,
			},
		success: function(data){
			$("#MKID").html(data);
            if(typeof autocomplete === 'function') {
			    autocomplete("MKID",'',' -- Pilih Mata Kuliah -- ');
            }
		}
	});
}

function pdf(){
		window.open("{{ url('publish_nilai_uas/pdf') }}?DosenID="+$("#DosenID").val()+'&TahunID='+$(".TahunID").val(),"_Blank");
}
	
function excel(){
		window.open("{{ url('publish_nilai_uas/excel') }}?DosenID="+$("#DosenID").val()+'&TahunID='+$(".TahunID").val(),"_Blank");
}

function pdf_sk_kordinator(){
	var DosenID = $("#DosenID").val();
	var TahunID = $("#TahunID").val();
	var ProgramID = $("#ProgramID").val();
	var ProdiID = $("#ProdiID").val();
	var Tanggal = $("#TanggalSKKoordinator").val();

	var link = "DosenID="+DosenID+
				"&TahunID="+TahunID+
				"&ProgramID="+ProgramID+
				"&ProdiID="+ProdiID+
				"&Tanggal="+Tanggal;

	window.open("{{ url('publish_nilai_uas/pdf_sk_kordinator') }}?"+link,'_Blank');
}

function pdf_sk_mengajar(){
	var DosenID = $("#DosenID").val();
	var TahunID = $("#TahunID").val();
	var ProgramID = $("#ProgramID").val();
	var ProdiID = $("#ProdiID").val();
	var Tanggal = $("#TanggalSKMengajar").val();

	if(!DosenID){
		swal('Info','Silahkan Memilih Dosen Terlebih Dahulu Sebelum Cetak SK Tugas Mengajar','info');
		return;
	}

	var link = "DosenID="+DosenID+
				"&TahunID="+TahunID+
				"&ProgramID="+ProgramID+
				"&ProdiID="+ProdiID+
				"&Tanggal="+Tanggal;

	window.open("{{ url('publish_nilai_uas/pdf_sk_mengajar') }}?"+link,'_Blank');
}

function pdf_dh_rapat_dosen(){
	var DosenID = $("#DosenID").val();
	var TahunID = $("#TahunID").val();
	var ProgramID = $("#ProgramID").val();
	var ProdiID = $("#ProdiID").val();
	
	var link = "DosenID="+DosenID+
				"&TahunID="+TahunID+
				"&ProgramID="+ProgramID+
				"&ProdiID="+ProdiID;

	window.open("{{ url('publish_nilai_uas/pdf_dh_rapat_dosen') }}?"+link,'_Blank');
}

function pdf_list_dosen_pengampu(){
	var DosenID = $("#DosenID").val();
	var TahunID = $("#TahunID").val();
	var ProgramID = $("#ProgramID").val();
	var ProdiID = $("#ProdiID").val();

	var link = "DosenID="+DosenID+
				"&TahunID="+TahunID+
				"&ProgramID="+ProgramID+
				"&ProdiID="+ProdiID;

	window.open("{{ url('publish_nilai_uas/pdf_list_dosen_pengampu') }}?"+link,'_Blank');
}


function checkall(chkAll,checkid) {
	if (checkid != null) 
	{
		if (checkid.length == null) checkid.checked = chkAll.checked;
        else for (i=0;i<checkid.length;i++) checkid[i].checked = chkAll.checked;
		
		$("input:checkbox[name='checkID[]']").parents('tr').removeClass('checked_tabel');
		$("input:checkbox[name='checkID[]']:checked").parents('tr').addClass('checked_tabel');
    }
}
</script>
@endpush
@endsection
