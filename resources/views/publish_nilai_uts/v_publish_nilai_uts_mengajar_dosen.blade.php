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
						<option value="{{ $row->ID }}">{{ $row->Nama }}</option>
					@endforeach
				</select>
			</div>
			<div class="form-group col-md-3">
				<label class="col-form-label"><h5 class="mb-0">
					Program Kuliah
				</h5></label>
				<select class="ProgramID form-control" id="ProgramID" onchange="changematkul()">
					<option value="">-- Lihat Semua --</option>
					@foreach(get_all('program') as $row)
						<option value="{{ $row->ID }}">{{ $row->Nama }}</option>
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
						<option value="{{ $row->ID }}">{{ get_field($row->JenjangID, 'jenjang') }} - {{ $row->Nama }}</option>
					@endforeach
				</select>
			</div>

			<div class="form-group col-md-3">
				<label class="col-form-label"><h5 class="mb-0">Cari Berdasarkan Dosen</h5></label>
				<select class="DosenID form-control" id="DosenID">
					<option value=""> -- Pilih -- </option>
					@foreach(DB::table('dosen')
						->select('dosen.ID', 'dosen.NIDN', 'dosen.Nama', 'dosen.Title', 'dosen.Gelar')
						->join('jadwal', function($join) {
							$join->on('jadwal.DosenID', '=', 'dosen.ID')
								 ->orWhereRaw('FIND_IN_SET(dosen.ID, jadwal.DosenAnggota)');
						})
						->groupBy('dosen.ID', 'dosen.NIDN', 'dosen.Nama', 'dosen.Title', 'dosen.Gelar')
						->orderBy('dosen.Nama', 'ASC')
						->whereRaw('(dosen.Nama IS NOT NULL AND dosen.Nama != "" AND dosen.Nama != "-")')
						->get() as $row)
						<option value="{{ $row->ID }}">{{ $row->Title }} {{ $row->Nama }}, {{ $row->Gelar }}</option>
					@endforeach
				</select>
			</div>

			<!-- Search & Filter  -->
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

			<div class="form-group col-md-5">
				<label class="col-form-label mt-2"><h5 class="m-0">Mata Kuliah</h5></label>
					<select class="MKID form-control" id="MKID" multiple>
						<option value=""> -- Lihat Semua -- </option>
					</select>
            </div>
			<!-- Search & Filter  -->
			<div class="form-group col-md-1">
				<label class="col-form-label mt-2"><h5 class="m-0">&nbsp;</h5></label>
				<div class="mt-0">
				<button type="button" onclick="filter()" class="btn btn-bordered-success waves-effect waves-light"><i class="fa fa-search"></i> Cari</button>
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
$(document).ready(function() {
	autocomplete('MKID');
	autocomplete('DosenID');
})

$('.textTanggal').datetimepicker({
	format:'Y-m-d',
	timepicker:false,
	scrollInput:false,
	onSelect: function () {
		$(this).trigger("focus").trigger("blur");
	}
});

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
	$.ajax({
		type: "POST",
		url: "{{ url('tahun/changetahun') }}",
		data: {
			ProgramID : $(".ProgramID").val(),
		},
		success: function(data) {
			$(".TahunID").html(data);
		}
	});
	return false;
}
changetahun();


function filter(url) {
	if(url == null)
	url = "{{ url('publish_nilai_uts/search_publish_nilai_uts_mengajar_dosen') }}";

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
changematkul();
function changematkul(){
	$.ajax({
		url:"{{ url('detailkurikulum/changemk_mknew') }}",
		type:"POST",
		data: {
			ProdiID : $("#ProdiID").val(),
			ProgramID : $("#ProgramID").val(),
			NO_VIEW_ALL : 1,
			},
		success: function(data){
			$(".MKID").html(data);
			autocomplete("MKID",'',' -- Pilih Mata Kuliah -- ');
		}
	});
}

function pdf(){
	window.open('{{ url("publish_nilai_uts/pdf_publish_nilai_uts_mengajar_dosen") }}/?DosenID='+$("#DosenID").val()+'&TahunID='+$(".TahunID").val(),"_Blank");
}

function excel(){
	window.open('{{ url("publish_nilai_uts/excel_publish_nilai_uts_mengajar_dosen") }}/?DosenID='+$("#DosenID").val()+'&TahunID='+$(".TahunID").val(),"_Blank");
}

function pdf_sk_kordinator(){
	var DosenID = $("#DosenID").val();
	var TahunID = $("#TahunID").val();
	var ProgramID = $("#ProgramID").val();
	var ProdiID = $("#ProdiID").val();
	var Tanggal = $("#TanggalSKKoordinator").val();

	var link = "&DosenID="+DosenID+
				"&TahunID="+TahunID+
				"&ProgramID="+ProgramID+
				"&ProdiID="+ProdiID+
				"&Tanggal="+Tanggal;

	window.open('{{ url("publish_nilai_uts/pdf_sk_kordinator") }}/?1'+link,'_Blank');
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

	var link = "&DosenID="+DosenID+
				"&TahunID="+TahunID+
				"&ProgramID="+ProgramID+
				"&ProdiID="+ProdiID+
				"&Tanggal="+Tanggal;

	window.open('{{ url("publish_nilai_uts/pdf_sk_mengajar") }}/?1'+link,'_Blank');
}

function pdf_dh_rapat_dosen(){
	var DosenID = $("#DosenID").val();
	var TahunID = $("#TahunID").val();
	var ProgramID = $("#ProgramID").val();
	var ProdiID = $("#ProdiID").val();

	var link = "&DosenID="+DosenID+
				"&TahunID="+TahunID+
				"&ProgramID="+ProgramID+
				"&ProdiID="+ProdiID;

	window.open('{{ url("publish_nilai_uts/pdf_dh_rapat_dosen") }}/?1'+link,'_Blank');
}

function pdf_list_dosen_pengampu(){
	var DosenID = $("#DosenID").val();
	var TahunID = $("#TahunID").val();
	var ProgramID = $("#ProgramID").val();
	var ProdiID = $("#ProdiID").val();

	var link = "&DosenID="+DosenID+
				"&TahunID="+TahunID+
				"&ProgramID="+ProgramID+
				"&ProdiID="+ProdiID;

	window.open('{{ url("publish_nilai_uts/pdf_list_dosen_pengampu") }}/?1'+link,'_Blank');
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
