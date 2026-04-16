@extends('layouts.template1')
@section('content')
<div class="card">
	<div class="card-body">
		<div class="form-row">
			<div class="form-group col-md-3">
				<label class="col-form-label "><h5 class="m-0">Program</h5></label>
				<select class="ProgramID form-control" id="ProgramID" onchange="filter()">
					<option value=""> -- Lihat Semua -- </option>
					@foreach(get_all('program') as $row)
						<option value="{{ $row->ID }}">{{ $row->Nama }}</option>
					@endforeach
				</select>
            </div>
            
            <div class="form-group col-md-3">
				<label class="col-form-label "><h5 class="m-0">Prodi</h5></label>
				<select class="ProdiID form-control" id="ProdiID" onchange="changekelas(); filter();">
					<option value=""> -- Lihat Semua -- </option>
					@foreach(get_all('programstudi') as $row)
						<option value="{{ $row->ID }}">{{ get_field($row->JenjangID, 'jenjang') }} || {{ $row->Nama }}</option>
					@endforeach
				</select>
            </div>
            
            <div class="form-group col-md-3">
				<label class="col-form-label "><h5 class="m-0">Status Mahasiswa</h5></label>
				<select class="StatusMhswID form-control" id="StatusMhswID" onchange="filter()">
					<option value=""> -- Lihat Semua -- </option>
					@foreach(get_all('statusmahasiswa') as $row)
						<option value="{{ $row->ID }}">{{ $row->Nama }}</option>
					@endforeach
				</select>
            </div>
            
            <div class="form-group col-md-3">
				<label class="col-form-label "><h5 class="m-0">Tahun Masuk</h5></label>
				<select class="TahunMasuk form-control" id="TahunMasuk" onchange="filter()">
					<option value=""> -- Lihat Semua -- </option>
					@foreach(DB::table('mahasiswa')->select('TahunMasuk')->distinct()->orderBy('TahunMasuk', 'DESC')->get() as $row)
                        @if($row->TahunMasuk)
						    <option value="{{ $row->TahunMasuk }}">{{ $row->TahunMasuk }}</option>
                        @endif
					@endforeach
				</select>
            </div>

			<div class="form-group col-md-3">
				<label class="col-form-label "><h5 class="m-0">Semester Masuk</h5></label>
				<select class="SemesterMasuk form-control" onchange="filter();" id="SemesterMasuk" >
					<option value=""> -- Lihat Semua -- </option>
					<option value="1">Ganjil</option>
					<option value="2">Genap</option>
				</select>
			</div>
			
			<div class="form-group col-md-3">
				<label class="col-form-label "><h5 class="m-0">Kelas</h5></label>
				<select class="KelasID form-control" onchange="filter();" id="KelasID" >
					<option value=''>-- Pilih Prodi Terlebih Dahulu --</option>	
				</select>
			</div>

            <div class="form-group col-md-6">
				<label class="col-form-label "><h5 class="m-0">Pencarian</h5></label>
				<input type="text" class="form-control keyword" id="keyword" onkeyup="filter()" placeholder="NIM / Nama Mahasiswa .." />
			</div>
		</div>
	</div>
</div>
<div class="card">
	<div class="card-body">
		<div id="konten"></div>
	</div>
</div>

@push('scripts')
<script type="text/javascript">	
function changekelas() {
	$.ajax({
		url: "{{ url('kelas/changekelas') }}",
		type: "POST",
		data: {
            _token: "{{ csrf_token() }}",
			ProdiID : $(".ProdiID").val()
		},
		success: function(data){
			$(".KelasID").html(data);
		}
	});
}

function filter(url = null) {
	if(url == null)
	    url = "{{ url('rencanastudi/searchtranskrip') }}";
	
	$.ajax({
		type: "POST",
		url: url,
		data: {
            _token: "{{ csrf_token() }}",
			ProgramID : $(".ProgramID").val(),
			ProdiID : $(".ProdiID").val(),
			KelasID : $(".KelasID").val(),
			TahunMasuk : $(".TahunMasuk").val(),
			SemesterMasuk : $(".SemesterMasuk").val(),
			StatusMhswID : $(".StatusMhswID").val(),
			keyword : $(".keyword").val()
		},
        beforeSend: function() {
            $("#konten").html('<div class="text-center"><i class="fa fa-spin fa-spinner"></i> Sedang memuat...</div>');
        },
		success: function(data) {
			$("#konten").html(data);
		}
	});
	return false;
}

filter();
</script>
@endpush
@endsection
