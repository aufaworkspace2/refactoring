@extends('layouts.template1')
@section('content')
<div class="card">
	<div class="card-body">
		<div class="form-row">
			<div class="col-md-12 mb-3">
				<div class="btn-group">
					<button class="btn btn-primary waves-effect waves-light dropdown-toggle" data-toggle="dropdown">
						<i class="mdi mdi-plus"></i> Tambah Data
						<i class="mdi mdi-chevron-down"></i>
					</button>
					<div class="dropdown-menu">
						<a class="dropdown-item" href="{{ url('konversi/add_internal') }}"><i class="mdi mdi-account"></i> Internal</a>
						<a class="dropdown-item" href="{{ url('konversi/add') }}"><i class="mdi mdi-account"></i> Eksternal</a>
					</div>
				</div>
				<div class="btn-group">
					<button type="button" class="btn btn-info dropdown-toggle waves-effect waves-light" data-toggle="dropdown" aria-expanded="false"><i class="mdi mdi-printer mr-1"></i> Cetak <i class="mdi mdi-chevron-down"></i></button>
					<div class="dropdown-menu">
						<a onclick="pdf()" href="javascript:void(0);" class="dropdown-item"><i class="mdi mdi-printer"></i> PDF</a>
						<a onclick="excel()" href="javascript:void(0);" class="dropdown-item"><i class="mdi mdi-printer"></i> Excel</a>
					</div>
				</div>
				<div class="btn-group">
					<button class="btn btn-success dropdown-toggle waves-effect waves-light" data-toggle="dropdown">
						<i class="fa fa-file"></i> Upload Konversi Eksternal
						<i class="mdi mdi-chevron-down"></i>
					</button>
					<div class="dropdown-menu">
						<a class="dropdown-item" href="#modal_template" data-toggle="modal"> <i class="fa fa-download"></i> Download Template</a>
						<a class="dropdown-item" href="#modal_upload_excel" data-toggle="modal"><i class="fa fa-upload"></i> Upload Excel</a>
					</div>
				</div>
				<button type="button" data-toggle="modal" data-target="#modal_konversi_kolektif" class="btn btn-warning waves-effect waves-light"><i class="mdi mdi-file-cog-outline"></i> Konversi Kolektif</button>
				<button type="button" onclick="hapusSelected()" class="btn btn-danger waves-effect waves-light" id="btnDelete" title="Silahkan pilih data terlebih dahulu."><i class="mdi mdi-delete"></i> Hapus</button>
			</div>
			<div class="form-group col-md-3">
				<label class="col-form-label"><h5 class="mb-0">Program Kuliah</h5></label>
				<select class="programID form-control" id="programID" onchange="filter()">
					<option value="">-- Lihat Semua --</option>
					@foreach(get_all('program') as $row)
						<option value="{{ $row->ID }}" {{ request('programID') == $row->ID ? 'selected' : '' }}>{{ $row->Nama }}</option>
					@endforeach
				</select>
			</div>
			<div class="form-group col-md-3">
				<label class="col-form-label"><h5 class="mb-0">Program Studi</h5></label>
				<select class="prodiID form-control" id="prodiID" onchange="filter()">
					<option value="">-- Lihat Semua --</option>
					@foreach(get_all('programstudi') as $row)
						<option value="{{ $row->ID }}" {{ request('prodiID') == $row->ID ? 'selected' : '' }}>{{ get_field($row->JenjangID, 'jenjang') }} - {{ $row->Nama }}</option>
					@endforeach
				</select>
			</div>
			<div class="form-group col-md-3">
				<label class="col-form-label"><h5 class="mb-0">Angkatan</h5></label>
				<select class="tahunMasuk form-control" id="tahunMasuk" onchange="filter()">
					<option value="">-- Lihat Semua --</option>
					@foreach(DB::table('mahasiswa')->select('TahunMasuk')->distinct()->orderBy('TahunMasuk', 'DESC')->get() as $row)
						@if($row->TahunMasuk)
							<option value="{{ $row->TahunMasuk }}" {{ request('tahunMasuk') == $row->TahunMasuk ? 'selected' : '' }}>{{ $row->TahunMasuk }}</option>
						@endif
					@endforeach
				</select>
			</div>
			<div class="form-group col-md-3">
				<label class="col-form-label"><h5 class="mb-0">Semester Masuk</h5></label>
				<select class="SemesterMasuk form-control" id="SemesterMasuk" onchange="filter()">
					<option value="">-- Lihat Semua --</option>
					<option value="1" {{ request('SemesterMasuk') == '1' ? 'selected' : '' }}>Ganjil</option>
					<option value="2" {{ request('SemesterMasuk') == '2' ? 'selected' : '' }}>Genap</option>
				</select>
			</div>
			<div class="form-group col-md-12">
				<label class="col-form-label"><h5 class="mb-0">Pencarian</h5></label>
				<input type="text" class="keyword form-control" id="keyword" placeholder="Cari berdasarkan kata kunci .." value="{{ request('keyword') ?? '' }}" onkeyup="filter()" />
			</div>
			<div class="form-group col-md-12">
				<button type="button" onclick="filter()" class="btn btn-primary btn-block"><i class="fa fa-search mr-1"></i> Cari</button>
			</div>
		</div>
	</div>
</div>
<div class="card">
	<div class="card-body">
		<div id="konten">
			@include('konversi.s_konversi')
		</div>
	</div>
</div>

<!-- Modal Download Template -->
<div class="modal" id="modal_template">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h4>Download Template</h4>
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
			</div>
			<div class="modal-body">
				<div class="card">
					<div class="card-body">
						<strong class="m-0">Filter Matakuliah</strong>
						<div class="form-group">
							<label class="col-form-label mt-2"><h5 class="m-0">Pilih Program Kuliah</h5></label>
							<select id="ProgramIDCetak" class="form-control">
								@foreach(get_all('program') as $row)
									<option value="{{ $row->ID }}">{{ $row->Nama }}</option>
								@endforeach
							</select>
						</div>
						<div class="form-group">
							<label class="col-form-label mt-2"><h5 class="m-0">Pilih Program Studi</h5></label>
							<select id="ProdiIDCetak" class="form-control" onchange="changeKurikulum()">
								@foreach(get_all('programstudi') as $row)
									<option value="{{ $row->ID }}">{{ $row->ProdiID }} | {{ get_field($row->JenjangID, 'jenjang') }} | {{ $row->Nama }}</option>
								@endforeach
							</select>
						</div>
						<div class="form-group">
							<label class="col-form-label mt-2"><h5 class="m-0">Pilih Kurikulum</h5></label>
							<select id="KurikulumIDCetak" class="form-control">
								<option value="">-- Pilih Kurikulum --</option>
							</select>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<a href="javascript:void(0);" class="btn btn-danger" data-dismiss="modal"><i class="mdi mdi-close"></i> Close</a>
				<button type="button" onclick="downloadTemplate()" class="btn btn-primary"><i class="fa fa-file"></i> Download Template</button>
			</div>
		</div>
	</div>
</div>

<!-- Modal Konversi Kolektif -->
<div class="modal" id="modal_konversi_kolektif">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h4>Konversi Kolektif</h4>
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
			</div>
			<div class="modal-body">
				<div class="card">
					<div class="card-body">
						<div class="form-group">
							<label class="col-form-label mt-2"><h5 class="m-0">Program Kuliah</h5></label>
							<select id="ProgramIDKonversi" class="form-control">
								@foreach(get_all('program') as $row)
									<option value="{{ $row->ID }}">{{ $row->Nama }}</option>
								@endforeach
							</select>
						</div>
						<div class="form-group">
							<label class="col-form-label mt-2"><h5 class="m-0">Program Studi</h5></label>
							<select id="ProdiIDKonversi" class="form-control">
								@foreach(get_all('programstudi') as $row)
									<option value="{{ $row->ID }}">{{ $row->ProdiID }} | {{ get_field($row->JenjangID, 'jenjang') }} | {{ $row->Nama }}</option>
								@endforeach
							</select>
						</div>
						<div class="form-group">
							<label class="col-form-label mt-2"><h5 class="m-0">Angkatan</h5></label>
							<select id="TahunMasukKonversi" class="form-control">
								<option value="">-- Lihat Semua --</option>
								@foreach(DB::table('mahasiswa')->select('TahunMasuk')->distinct()->orderBy('TahunMasuk', 'DESC')->get() as $row)
									@if($row->TahunMasuk)
										<option value="{{ $row->TahunMasuk }}">{{ $row->TahunMasuk }}</option>
									@endif
								@endforeach
							</select>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<a href="javascript:void(0);" class="btn btn-danger" data-dismiss="modal"><i class="mdi mdi-close"></i> Close</a>
				<button type="button" onclick="konversikan()" class="btn btn-primary"><i class="fa fa-file"></i> Konversikan</button>
			</div>
		</div>
	</div>
</div>

<!-- Modal Upload Excel -->
<div class="modal" id="modal_upload_excel">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h4>Upload File Excel</h4>
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
			</div>
			<div class="modal-body">
				<input type="file" id="file_excel" name="file_excel" class="form-control" accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel" />
				<b>Catatan * :<br />
					Ketika Pengisian Nilai Jangan Merubah Header Excel, Karena Akan Mengakibatkan Kegagalan Upload<br />
				</b>
			</div>
			<div class="modal-footer">
				<button type="button" onclick="uploadExcel()" class="btn btn-primary"><i class="mdi mdi-file-upload"></i> Upload</button>
				<a href="javascript:void(0);" class="btn btn-danger" data-dismiss="modal"><i class="mdi mdi-close"></i> Batal</a>
			</div>
		</div>
	</div>
</div>

@push('scripts')
<script type="text/javascript">
$(document).ready(function() {
	// Check all checkbox
	$(document).on('click', '#checkAll', function() {
		$('input[name="checkID[]"]').prop('checked', this.checked);
	});

	// Load kurikulum on page load
	changeKurikulum();
});

function filter(url) {
	if(url == null)
	url = "{{ url('konversi/search') }}";

	$.ajax({
		type: "POST",
		url: url,
		data: {
			_token: "{{ csrf_token() }}",
			programID : $("#programID").val(),
			prodiID : $("#prodiID").val(),
			tahunMasuk : $("#tahunMasuk").val(),
			SemesterMasuk : $("#SemesterMasuk").val(),
			keyword : $("#keyword").val(),
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

function hapusSelected() {
	var checkID = [];
	$('input[name="checkID[]"]:checked').each(function() {
		checkID.push($(this).val());
	});
	if (checkID.length == 0) {
		swal("Info", "Pilih data yang akan dihapus", "info");
		return;
	}
	swal({
		title: "Konfirmasi",
		text: "Apakah Anda yakin ingin menghapus data ini?",
		type: "warning",
		showCancelButton: true,
		confirmButtonColor: "#DD6B55",
		confirmButtonText: "Ya, Hapus!",
		cancelButtonText: "Batal"
	}).then(function(isConfirm) {
		if (isConfirm.value) {
			$.ajax({
				type: "POST",
				url: "{{ url('konversi/delete') }}",
				data: { _token: "{{ csrf_token() }}", checkID: checkID },
				success: function(data) {
					if (data.status == 1 || data.status == '1') {
						swal("Berhasil!", data.message, "success");
						filter();
					} else {
						swal("Gagal!", data.message, "error");
					}
				}
			});
		}
	});
}

function pdf() {
	var programID = $('#programID').val();
	var prodiID = $('#prodiID').val();
	var tahunMasuk = $('#tahunMasuk').val();
	var keyword = $('#keyword').val();
	var SemesterMasuk = $('#SemesterMasuk').val();
	var link = '?programID=' + programID +
				'&prodiID=' + prodiID +
				'&tahunMasuk=' + tahunMasuk +
				'&SemesterMasuk=' + SemesterMasuk +
				'&keyword=' + keyword;

	window.open('{{ url("konversi/pdf") }}' + link, "_Blank");
}

function excel() {
	var programID = $('#programID').val();
	var prodiID = $('#prodiID').val();
	var tahunMasuk = $('#tahunMasuk').val();
	var keyword = $('#keyword').val();
	var SemesterMasuk = $('#SemesterMasuk').val();
	var link = '?programID=' + programID +
				'&prodiID=' + prodiID +
				'&tahunMasuk=' + tahunMasuk +
				'&SemesterMasuk=' + SemesterMasuk +
				'&keyword=' + keyword;

	window.open('{{ url("konversi/excel") }}' + link, "_Blank");
}

function changeKurikulum() {
	$.ajax({
		url: "{{ url('detailkurikulum/changekonsentrasi') }}",
		type: "POST",
		data: {
			ProdiID: $("#ProdiIDCetak").val(),
			ProgramID: $("#ProgramIDCetak").val(),
		},
		success: function(data) {
			$("#KurikulumIDCetak").html(data);
		}
	});
}

function downloadTemplate() {
	var link = "?ProgramID=" + $("#ProgramIDCetak").val() +
		"&ProdiID=" + $("#ProdiIDCetak").val() +
		"&KurikulumID=" + $("#KurikulumIDCetak").val();
	window.open('{{ url("konversi/excelNilai") }}' + link, "_Blank");
	$('#modal_template').modal('hide');
}

function konversikan() {
	swal({
		title: "Konfirmasi",
		text: "Apakah Anda yakin ingin melakukan konversi kolektif untuk semua data?",
		type: "warning",
		showCancelButton: true,
		confirmButtonColor: "#DD6B55",
		confirmButtonText: "Ya, Konversi!",
		cancelButtonText: "Batal"
	}).then(function(isConfirm) {
		if (isConfirm.value) {
			$.ajax({
				url: "{{ url('konversi/konversi_all') }}",
				type: "POST",
				dataType: 'json',
				data: {
					_token: "{{ csrf_token() }}",
					ProdiID: $("#ProdiIDKonversi").val(),
					ProgramID: $("#ProgramIDKonversi").val(),
					TahunMasuk: $("#TahunMasukKonversi").val(),
				},
				success: function(data) {
					if (data.status == 1 || data.status == '1') {
						swal('Berhasil!', data.message, 'success');
						$('#modal_konversi_kolektif').modal('hide');
					} else {
						swal('Gagal!', data.message, 'error');
						$('#modal_konversi_kolektif').modal('hide');
					}
					filter();
				},
				error: function() {
					swal('Error!', 'Terjadi kesalahan!', 'error');
				}
			});
		}
	});
}

function uploadExcel() {
	var fileInput = document.getElementById('file_excel');
	if (fileInput.files.length == 0) {
		swal('Info', 'Pilih file excel terlebih dahulu!', 'info');
		return;
	}

	var formData = new FormData();
	formData.append('file_excel', fileInput.files[0]);
	formData.append('_token', '{{ csrf_token() }}');

	swal({
		title: 'Uploading...',
		text: 'Mohon tunggu sebentar',
		allowOutsideClick: false,
		onOpen: function() {
			swal.showLoading();
		}
	});

	$.ajax({
		type: 'POST',
		dataType: 'JSON',
		url: '{{ url("konversi/uploadExcel") }}',
		data: formData,
		cache: false,
		contentType: false,
		processData: false,
		success: function(data) {
			swal.close();
			if (data.status == '1' || data.status == 1) {
				$('#modal_upload_excel').modal('hide');
				swal('Pemberitahuan !!!', data.message, 'success');
				filter();
			} else {
				swal('Pemberitahuan !!!', data.message, 'error');
			}
			$('#file_excel').val('');
		},
		error: function(xhr) {
			swal.close();
			swal('Error!', 'Terjadi kesalahan saat upload!', 'error');
		}
	});
}

function genKonversi(id) {
	console.log('genKonversi called with ID:', id);
	swal({
		title: 'Konfirmasi !',
		text: 'Apa anda yakin akan melakukan Konversi Nilai untuk mahasiswa ini ?',
		type: 'question',
		showCancelButton: true,
		showLoaderOnConfirm: true,
		preConfirm: function() {
			return new Promise(function(resolve, reject) {
				$.ajax({
					url: "{{ url('konversi/genKonversi') }}",
					type: "post",
					dataType: 'JSON',
					data: {
						_token: "{{ csrf_token() }}",
						KonversiID: id
					},
					success: function(a) {
						var matkulSuccess = '';
						var matkulFail = '';
						var nomor = 1;
						var message = '';
						if (a.status == '1') {
							if (a.matkulSuccess && a.matkulSuccess.length > 0) {
								$.each(a.matkulSuccess, function(key, item) {
									matkulSuccess += nomor + '. Kode ' + item + '\n';
									nomor++;
								});
							} else {
								matkulSuccess = 'Tidak ada';
							}
							if (a.matkulFail && a.matkulFail.length > 0) {
								nomor = 1;
								$.each(a.matkulFail, function(key, item) {
									matkulFail += nomor + '. Kode ' + item + '\n';
									nomor++;
								});
							} else {
								matkulFail = 'Tidak ada';
							}

							message += 'Daftar Mata Kuliah yang berhasil dikonversi : \n';
							message += matkulSuccess;
							message += '\nDaftar Mata Kuliah yang gagal dikonversi : \n';
							message += matkulFail;

							swal('Pemberitahuan', message, 'info').then(function() {
								filter();
							});
							resolve();
						} else {
							swal('Pemberitahuan', a.message, 'warning');
							reject();
						}
					},
					error: function(data) {
						swal.close();
						swal('Error', 'Maaf, data gagal diproses !.', 'error');
						reject();
					}
				});
			});
		},
		allowOutsideClick: false
	});
}

function batalKonversi(id) {
	console.log('batalKonversi called with ID:', id);
	swal({
		title: 'Konfirmasi !',
		text: 'Apa anda yakin akan membatalkan Konversi Nilai untuk mahasiswa ini ?',
		type: 'question',
		showCancelButton: true,
		showLoaderOnConfirm: true,
		preConfirm: function() {
			return new Promise(function(resolve, reject) {
				$.ajax({
					url: "{{ url('konversi/batalKonversi') }}",
					type: "post",
					dataType: 'JSON',
					data: {
						_token: "{{ csrf_token() }}",
						KonversiID: id
					},
					success: function(a) {
						if (a.status == '1') {
							swal('Pemberitahuan', 'Berhasil membatalkan konversi nilai', 'info').then(function() {
								filter();
							});
							resolve();
						} else {
							swal('Pemberitahuan', a.message, 'warning');
							reject();
						}
					},
					error: function(data) {
						swal.close();
						swal('Error', 'Maaf, data gagal diproses !.', 'error');
						reject();
					}
				});
			});
		},
		allowOutsideClick: false
	});
}
</script>
@endpush
@endsection
