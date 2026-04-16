@extends('layouts.template1')

@section('content')
<div class="card">
	<div class="card-body">
		<form id="f_keterangan_status_mahasiswa" action="{{ url('keterangan_status_mahasiswa/save/'.$save) }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="ID" id="ID" value="{{ $row->ID ?? '' }}">
			<h3>Keterangan Status Mahasiswa</h3>
				<div class="form-row mt-3">
					<div class="form-group col-md-12">
						<label class="col-form-label" for="ProgramID">Program Kuliah</label>
						<div class="controls">
						<select class="ProgramID form-control" name="ProgramID" id="ProgramID" onchange="changetahun()" required>
							@foreach($programs as $raw)
								<option value="{{ $raw->ID }}" {{ ($row->ProgramID ?? '') == $raw->ID ? 'selected' : '' }}>{{ $raw->Nama }}</option>
							@endforeach
						</select>
						</div>
					</div>
					
					<div class="form-group col-md-12">
						<label class="col-form-label" for="TahunID">Tahun Semester</label>
						<div class="controls">
						<select class="TahunID form-control" name="TahunID" id="TahunID" onchange="changeprodi();" required>
						</select>
						</div>
					</div>
					
					<div class="form-group col-md-12">
						<label class="col-form-label" for="ProdiID">Program Studi</label>
						<div class="controls">
						<select class="ProdiID form-control" name="ProdiID" id="ProdiID" onchange="changemhsw();" required>
						</select>
						</div>
					</div>
					
					<div class="form-group col-md-12">
						<label class="col-form-label" for="MhswID">Mahasiswa *</label>
						<div class="controls">
							<select name="MhswID" id="MhswID" class="form-control MhswID" onchange="changestatus();" required>
							</select>
						</div>
					</div>
					<div class="form-group col-md-12">
						<label class="col-form-label" for="StatusMhswID">Status *</label>
						<div class="controls">
							<select name="StatusMhswID" id="StatusMhswID" class="StatusMhswID form-control" onchange="change_status_ui()" required>
							</select>
						</div>
					</div>

					<div class="form-group col-md-12">
						<label class="col-form-label" for="Tgl">Tanggal *</label>
						<div class="controls">
							<input type="date" id="Tgl" name="Tgl" class="form-control" value="{{ !empty($row->Tgl) ? date('Y-m-d', strtotime($row->Tgl)) : date('Y-m-d') }}" required>
						</div>
					</div>
					
					<div class="form-group col-md-12">
						<label class="col-form-label" for="Nomor_Surat">Nomor Surat *</label>
						<div class="controls">
							<input type="text" id="Nomor_Surat" name="Nomor_Surat" class="form-control" value="{{ $row->Nomor_Surat ?? '' }}" required />
						</div>
					</div>
					<div class='kc col-md-12' style="display: none;">
						<div class="form-group ">
							<label class="col-form-label" for="Mulai_Semester">Mulai Semester *</label>
							<div class="row ml-0">
								@php 
                                    $mulai = str_split($row->Mulai_Semester ?? '', 4);
                                    $akhir = str_split($row->Akhir_Semester ?? '', 4);
                                @endphp
								<input type="text" placeholder="Tahun" name="Mulai_Semester[]" class="form-control col-md-3" value="{{ $mulai[0] ?? '' }}" maxlength=4 />
								&nbsp; - &nbsp;
								<input type="text" placeholder="Sem" name="Mulai_Semester[]" class="form-control col-md-2" value="{{ $mulai[1] ?? '' }}" maxlength=1 />
								&nbsp; ex : 2014 - 1
							</div>
						</div>
						
						<div class="form-group">
							<label class="col-form-label" for="Akhir_Semester">Akhir Semester *</label>
							<div class="row ml-0">
								<input type="text" placeholder="Tahun" name="Akhir_Semester[]" class="form-control col-md-3" value="{{ $akhir[0] ?? '' }}" maxlength=4 />
								&nbsp; - &nbsp;
								<input type="text" placeholder="Sem" name="Akhir_Semester[]" class="form-control col-md-2" value="{{ $akhir[1] ?? '' }}" maxlength=1 />
								&nbsp; ex : 2014 - 1
							</div>
						</div>
					</div>
					<div class="form-group col-md-12" id="div_alasan">
						<label class="col-form-label" for="Alasan">Alasan</label>
						<div class="controls">
							<textarea id="Alasan" name="Alasan" class="form-control">{{ $row->Alasan ?? '' }}</textarea>
						</div>
					</div>
			</div>	
			<button type="button" onClick="btnEdit(1)" class="btn btn-bordered-success waves-effect width-md waves-light btnEdit" style="display: none;">Edit Data</button>
			<button type="submit" class="btn btn-bordered-primary waves-effect width-md waves-light btnSave">Simpan Data</button>
			<button type="button" onClick="window.history.back()" class="btn btn-bordered-danger waves-effect width-md waves-light">Kembali</button>
		</form>
	</div>
</div>
@endsection

@push('scripts')
<script type="text/javascript">
$(document).ready(function() {
    changetahun();
    
    @if($save == 2)
        btnEdit(0);
    @endif
});

function changestatus(){
	$.ajax({
		type: "POST",
		url: "{{ url('keterangan_status_mahasiswa/changestatus') }}",
		data: {
            _token: "{{ csrf_token() }}",
			ID : "{{ ($save == 2) ? ($row->StatusMahasiswaID ?? '0') : '0' }}",
			MhswID : $("#MhswID").val(),
		},
		success: function(data) {
			$(".StatusMhswID").html(data);
			change_status_ui();
		}
	});
}

function changemhsw(){
	$.ajax({
		type: "POST",
		url: "{{ url('keterangan_status_mahasiswa/changemhsw') }}",
		data: {
            _token: "{{ csrf_token() }}",
			ID : "{{ ($save == 2) ? ($row->MhswID ?? '0') : '0' }}",
			ProdiID : $(".ProdiID").val(),
			ProgramID : $(".ProgramID").val(),
		},
		success: function(data) {
			$(".MhswID").html(data);
			if ($.fn.select2) {
                $(".MhswID").select2();
            }
			changestatus();
		}
	});
}

function changeprodi(){
	$.ajax({
		type: "POST",
		url: "{{ url('programstudi/changeprodi') }}",
		data: {
            _token: "{{ csrf_token() }}",
			ProdiID : "{{ ($save == 2) ? ($row->ProdiID ?? '0') : '0' }}",
			TahunID : $(".TahunID").val()
		},
		success: function(data) {
			$(".ProdiID").html(data);
			changemhsw();
		}
	});
}

function changetahun() {
	$.ajax({
		type: "POST",
		url: "{{ url('tahun/changetahun') }}",
		data: {
            _token: "{{ csrf_token() }}",
			ProgramID : $(".ProgramID").val(),
			TahunID :  "{{ ($save == 2) ? ($row->TahunID ?? '0') : '0' }}",
		},
		success: function(data) {
			$(".TahunID").html(data);	
			changeprodi();
		}
	});
	return false;
}

function change_status_ui() {
	var cek = $('#StatusMhswID').val();
	if(cek == '2') {
		$('.kc').fadeIn();
	} else {
		$('.kc').fadeOut();
	}
	if(cek == '1'){
		$('#div_alasan').fadeOut();
	} else {
		$('#div_alasan').fadeIn();
	}
}

$("#f_keterangan_status_mahasiswa").submit(function(e){
    e.preventDefault();
	var formData = new FormData(this);
	$.ajax({
		type:'POST',
		url: $(this).attr('action'),
		data:formData,
		cache:false,
		contentType: false,
		processData: false,
		success:function(data){
            swal("Berhasil", "Data berhasil disimpan.", "success").then(() => {
                if("{{ $save }}" == '1') {
                    window.location.href = "{{ url('keterangan_status_mahasiswa') }}";
                } else {
                    location.reload();
                }
            });
		},
		error: function(){
			swal("Gagal", "Terjadi kesalahan saat menyimpan data.", "error");
		}
	});
});

function btnEdit(checkid) {
	if (checkid == 1) {
        $("input, select, textarea, button[type=submit]").removeAttr('disabled');
        $(".btnEdit").hide();
        $(".btnSave").show();
   	} else {
        $("input, select, textarea, button[type=submit]").attr('disabled', true);
        $(".btnEdit").show();
        $(".btnSave").hide();
    }
}
</script>
@endpush
