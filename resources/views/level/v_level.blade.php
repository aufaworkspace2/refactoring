@extends('layouts.template1') 
@section('content') 

<div class="card-box">
	<div class="row">
		<div class="col-md-12">
			<div class="button-list">
				<a href="{{ url(request()->segment(1) . '/add') }}" class="btn btn-bordered-primary waves-effect width-md waves-light"><i class="mdi mdi-plus"></i> Tambah Data</a>
				<button class="btn btn-bordered-danger waves-effect width-md waves-light" id="btnDelete" data-placement="top" title="Silahkan pilih data terlebih dahulu." data-toggle="modal"><i class="mdi mdi-delete"></i> Hapus</button>  	
				<a href="javascript:;" onclick="pdf()" class="btn btn-outline-primary waves-effect waves-light btn-md"><i class="mdi mdi-printer pr-1"></i> Cetak PDF</a>
				<a href="javascript:;" onclick="excel()" class="btn btn-outline-success waves-effect waves-light btn-md"><i class="mdi mdi-printer pr-1"></i> Simpan Excel</a>
			</div>
		</div>
	</div>
	<div class="form-row">
		<div class="form-group col-md-12">
			<label class="col-form-label mt-2"><h4 class="m-0">Pencarian Data</h4></label>
			<input type="text" class="form-control keyword" onkeyup="filter()" placeholder="Masukkan kata kunci pencarian ..">
		</div>
	</div>
</div>
<div class="card-box">
	<div id="konten"></div>
</div>

{{-- Script ditaruh di dalam push agar bisa dirender di bagian bawah body template --}}
@push('scripts')
<script type="text/javascript">	
function filter(url) {
	if(url == null) {
	    url = "{{ url('level/search') }}";
    }
	
	$.ajax({
		type: "POST",
		url: url,
		data: {
			keyword : $(".keyword").val(),
            _token: "{{ csrf_token() }}" // Laravel Wajib CSRF Token
		},
		success: function(data) {
			$("#konten").html(data);
		}
	});
	return false;
}

function pdf(){
    window.open("{{ url('level/pdf') }}?keyword="+$(".keyword").val(), "_Blank");
}
	
function excel(){
    window.open("{{ url('level/excel') }}?keyword="+$(".keyword").val(), "_Blank");
}

function checkall(chkAll,checkid) {
	if (checkid != null) {
		if (checkid.length == null) checkid.checked = chkAll.checked;
        else for (i=0;i<checkid.length;i++) checkid[i].checked = chkAll.checked;
		
		$("input:checkbox[name='checkID[]']").parents('tr').removeClass('table-danger');
		$("input:checkbox[name='checkID[]']:checked").parents('tr').addClass('table-danger');
    }
}

// Otomatis load data saat halaman dibuka
filter();
</script>
@endpush

@endsection