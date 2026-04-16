@extends('layouts.template1')
@section('content')
<div class="card">
	<div class="card-body">
		<form id="formKonversi">
			<input type="hidden" name="_token" value="{{ csrf_token() }}">
			<input type="hidden" name="ID" id="ID" value="{{ $row->ID ?? '' }}">

			<div class="form-row">
				<div class="form-group col-md-4">
					<label class="col-form-label"><h5 class="mb-0">NPM</h5></label>
					<input type="text" name="NPM" id="NPM" class="form-control" value="{{ $row->NPM ?? '' }}" placeholder="Masukkan NPM" required>
				</div>
				<div class="form-group col-md-4">
					<label class="col-form-label"><h5 class="mb-0">Kode Konversi</h5></label>
					<input type="text" name="KodeKonversi" id="KodeKonversi" class="form-control" value="{{ $row->KodeKonversi ?? '' }}" required>
				</div>
				<div class="form-group col-md-4">
					<label class="col-form-label"><h5 class="mb-0">Alasan</h5></label>
					<input type="text" name="Alasan" id="Alasan" class="form-control" value="{{ $row->Alasan ?? '' }}">
				</div>
			</div>

			<hr>

			<h5>Detail Mata Kuliah Konversi</h5>
			<div class="table-responsive">
				<table class="table table-bordered" id="tableDetail">
					<thead class="bg-primary text-white">
						<tr>
							<th width="5%">No</th>
							<th width="10%">MK Kode Asal</th>
							<th width="20%">Nama MK Asal</th>
							<th width="5%">SKS Asal</th>
							<th width="8%">Nilai Asal</th>
							<th width="10%">MK Kode Tujuan</th>
							<th width="20%">Nama MK Tujuan</th>
							<th width="5%">Semester</th>
							<th width="8%">Nilai Konversi</th>
							<th width="5%">Aksi</th>
						</tr>
					</thead>
					<tbody id="tbodyDetail">
						<tr>
							<td class="text-center">1</td>
							<td><input type="text" name="MKKodeAsal[]" class="form-control form-control-sm" required></td>
							<td><input type="text" name="NamaMKAsal[]" class="form-control form-control-sm" required></td>
							<td><input type="number" name="SKSAsal[]" class="form-control form-control-sm" value="0" min="0"></td>
							<td><input type="text" name="NilaiAsal[]" class="form-control form-control-sm"></td>
							<td><input type="hidden" name="DetailkurikulumID[]" value=""><input type="text" name="MKKodeTujuan[]" class="form-control form-control-sm" id="MKKodeTujuan_0" onkeyup="cariMK(0)"></td>
							<td><input type="text" name="NamaMKTujuan[]" class="form-control form-control-sm" id="NamaMKTujuan_0" readonly></td>
							<td><input type="number" name="Semester[]" class="form-control form-control-sm" id="Semester_0" min="1"></td>
							<td>
								<select name="NilaiKonversi[]" class="form-control form-control-sm" id="NilaiKonversi_0">
									<option value="">-- Pilih --</option>
									@foreach(DB::table('bobot')->select('Nilai')->distinct()->orderBy('Nilai')->get() as $b)
										<option value="{{ $b->Nilai }}">{{ $b->Nilai }}</option>
									@endforeach
								</select>
							</td>
							<td class="text-center">
								<button type="button" class="btn btn-danger btn-sm" onclick="hapusRow(this)"><i class="fa fa-trash"></i></button>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
			<button type="button" class="btn btn-success btn-sm" onclick="tambahRow()"><i class="fa fa-plus"></i> Tambah Baris</button>
			<button type="button" class="btn btn-info btn-sm" onclick="loadNilai()"><i class="fa fa-download"></i> Load Nilai Mahasiswa</button>

			<hr>

			<div class="form-group">
				<button type="button" onclick="saveKonversi({{ $save }})" class="btn btn-primary"><i class="fa fa-save"></i> Simpan</button>
				<a href="{{ url('konversi') }}" class="btn btn-secondary">Batal</a>
			</div>
		</form>
	</div>
</div>

<!-- Modal Cari MK -->
<div id="modalCariMK" class="modal fade" tabindex="-1" role="dialog">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Cari Mata Kuliah</h5>
				<button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
			</div>
			<div class="modal-body">
				<input type="text" id="keywordMK" class="form-control" placeholder="Cari kode atau nama mata kuliah..." onkeyup="searchMK()">
				<div id="resultMK" class="mt-2"></div>
			</div>
		</div>
	</div>
</div>

@push('scripts')
<script type="text/javascript">
var currentRow = 0;

@if(isset($row->ID))
$(document).ready(function() {
	loadKonversiDetail({{ $row->ID }});
});
@endif

function tambahRow() {
	var rowCount = $('#tbodyDetail tr').length + 1;
	var newRow = `
		<tr>
			<td class="text-center">${rowCount}</td>
			<td><input type="text" name="MKKodeAsal[]" class="form-control form-control-sm" required></td>
			<td><input type="text" name="NamaMKAsal[]" class="form-control form-control-sm" required></td>
			<td><input type="number" name="SKSAsal[]" class="form-control form-control-sm" value="0" min="0"></td>
			<td><input type="text" name="NilaiAsal[]" class="form-control form-control-sm"></td>
			<td><input type="hidden" name="DetailkurikulumID[]" value=""><input type="text" name="MKKodeTujuan[]" class="form-control form-control-sm" id="MKKodeTujuan_${rowCount}" onkeyup="cariMK(${rowCount})"></td>
			<td><input type="text" name="NamaMKTujuan[]" class="form-control form-control-sm" id="NamaMKTujuan_${rowCount}" readonly></td>
			<td><input type="number" name="Semester[]" class="form-control form-control-sm" id="Semester_${rowCount}" min="1"></td>
			<td>
				<select name="NilaiKonversi[]" class="form-control form-control-sm" id="NilaiKonversi_${rowCount}">
					<option value="">-- Pilih --</option>
					@foreach(DB::table('bobot')->select('Nilai')->distinct()->orderBy('Nilai')->get() as $b)
						<option value="{{ $b->Nilai }}">{{ $b->Nilai }}</option>
					@endforeach
				</select>
			</td>
			<td class="text-center">
				<button type="button" class="btn btn-danger btn-sm" onclick="hapusRow(this)"><i class="fa fa-trash"></i></button>
			</td>
		</tr>
	`;
	$('#tbodyDetail').append(newRow);
}

function hapusRow(btn) {
	$(btn).closest('tr').remove();
	// Re-number rows
	$('#tbodyDetail tr').each(function(i) {
		$(this).find('td:first').text(i + 1);
	});
}

function cariMK(row) {
	currentRow = row;
	$('#modalCariMK').modal('show');
	$('#keywordMK').focus();
}

function searchMK() {
	var keyword = $('#keywordMK').val();
	if (keyword.length < 2) {
		$('#resultMK').html('');
		return;
	}

	$.ajax({
		type: "POST",
		url: "{{ url('detailkurikulum/search') }}",
		data: {
			_token: "{{ csrf_token() }}",
			keyword: keyword
		},
		success: function(data) {
			var html = '<div class="list-group">';
			$(data).each(function(i, mk) {
				html += '<a href="javascript:void(0)" class="list-group-item list-group-item-action" onclick="pilihMK(' + mk.ID + ', \'' + mk.MKKode.replace(/'/g, "\\'") + '\', \'' + mk.Nama.replace(/'/g, "\\'") + '\', ' + mk.Semester + ')">' + mk.MKKode + ' - ' + mk.Nama + ' (Sem ' + mk.Semester + ')</a>';
			});
			html += '</div>';
			$('#resultMK').html(html);
		}
	});
}

function pilihMK(id, kode, nama, semester) {
	$('#MKKodeTujuan_' + currentRow).val(kode);
	$('#NamaMKTujuan_' + currentRow).val(nama);
	$('#Semester_' + currentRow).val(semester);
	$('input[name="DetailkurikulumID[]"]', $('#tbodyDetail tr').eq(currentRow - 1)).val(id);
	$('#modalCariMK').modal('hide');
}

function loadNilai() {
	var npm = $('#NPM').val();
	if (!npm) {
		swal("Info", "Masukkan NPM terlebih dahulu", "info");
		return;
	}

	// Get MhswID from NPM
	$.ajax({
		type: "POST",
		url: "{{ url('konversi/json_nilai') }}",
		data: {
			_token: "{{ csrf_token() }}",
			NPM: npm
		},
		success: function(data) {
			// Clear existing rows
			$('#tbodyDetail').empty();

			$.each(data, function(i, item) {
				var rowCount = i + 1;
				var newRow = `
					<tr>
						<td class="text-center">${rowCount}</td>
						<td><input type="text" name="MKKodeAsal[]" class="form-control form-control-sm" value="${item.MKKodeAsal || ''}" required></td>
						<td><input type="text" name="NamaMKAsal[]" class="form-control form-control-sm" value="${item.NamaMKAsal || ''}" required></td>
						<td><input type="number" name="SKSAsal[]" class="form-control form-control-sm" value="${item.SKSAsal || 0}" min="0"></td>
						<td><input type="text" name="NilaiAsal[]" class="form-control form-control-sm" value="${item.NilaiAsal || ''}"></td>
						<td><input type="hidden" name="DetailkurikulumID[]" value=""><input type="text" name="MKKodeTujuan[]" class="form-control form-control-sm" id="MKKodeTujuan_${rowCount}" onkeyup="cariMK(${rowCount})"></td>
						<td><input type="text" name="NamaMKTujuan[]" class="form-control form-control-sm" id="NamaMKTujuan_${rowCount}" readonly></td>
						<td><input type="number" name="Semester[]" class="form-control form-control-sm" id="Semester_${rowCount}" min="1"></td>
						<td>
							<select name="NilaiKonversi[]" class="form-control form-control-sm" id="NilaiKonversi_${rowCount}">
								<option value="">-- Pilih --</option>
								@foreach(DB::table('bobot')->select('Nilai')->distinct()->orderBy('Nilai')->get() as $b)
									<option value="{{ $b->Nilai }}">{{ $b->Nilai }}</option>
								@endforeach
							</select>
						</td>
						<td class="text-center">
							<button type="button" class="btn btn-danger btn-sm" onclick="hapusRow(this)"><i class="fa fa-trash"></i></button>
						</td>
					</tr>
				`;
				$('#tbodyDetail').append(newRow);
			});
		}
	});
}

function loadKonversiDetail(id) {
	$.ajax({
		type: "POST",
		url: "{{ url('konversi/json_konversi') }}",
		data: {
			_token: "{{ csrf_token() }}",
			KonversiID: id
		},
		success: function(data) {
			$('#tbodyDetail').empty();

			$.each(data, function(i, item) {
				var rowCount = i + 1;
				var newRow = `
					<tr>
						<td class="text-center">${rowCount}</td>
						<td><input type="text" name="MKKodeAsal[]" class="form-control form-control-sm" value="${item.MKKodeAsal || ''}" required></td>
						<td><input type="text" name="NamaMKAsal[]" class="form-control form-control-sm" value="${item.NamaMKAsal || ''}" required></td>
						<td><input type="number" name="SKSAsal[]" class="form-control form-control-sm" value="${item.SKSAsal || 0}" min="0"></td>
						<td><input type="text" name="NilaiAsal[]" class="form-control form-control-sm" value="${item.NilaiAsal || ''}"></td>
						<td><input type="hidden" name="DetailkurikulumID[]" value="${item.DetailkurikulumID || ''}"><input type="text" name="MKKodeTujuan[]" class="form-control form-control-sm" value="${item.MKKode || ''}" id="MKKodeTujuan_${rowCount}" onkeyup="cariMK(${rowCount})"></td>
						<td><input type="text" name="NamaMKTujuan[]" class="form-control form-control-sm" value="${item.NamaMK || ''}" id="NamaMKTujuan_${rowCount}" readonly></td>
						<td><input type="number" name="Semester[]" class="form-control form-control-sm" value="${item.Semester || ''}" id="Semester_${rowCount}" min="1"></td>
						<td>
							<select name="NilaiKonversi[]" class="form-control form-control-sm" id="NilaiKonversi_${rowCount}">
								<option value="">-- Pilih --</option>
								@foreach(DB::table('bobot')->select('Nilai')->distinct()->orderBy('Nilai')->get() as $b)
									<option value="{{ $b->Nilai }}" ${item.NilaiKonversi == '{{ $b->Nilai }}' ? 'selected' : ''}>{{ $b->Nilai }}</option>
								@endforeach
							</select>
						</td>
						<td class="text-center">
							<button type="button" class="btn btn-danger btn-sm" onclick="hapusRow(this)"><i class="fa fa-trash"></i></button>
						</td>
					</tr>
				`;
				$('#tbodyDetail').append(newRow);
			});
		}
	});
}

function saveKonversi(saveType) {
	var formData = new FormData($('#formKonversi')[0]);

	$.ajax({
		type: "POST",
		url: "{{ url('konversi/save') }}/" + saveType,
		data: formData,
		contentType: false,
		processData: false,
		success: function(response) {
			if (response.status == 1) {
				swal("Berhasil!", "Data berhasil disimpan", "success");
				window.location.href = response.url;
			} else {
				var msg = response.message || "Terjadi kesalahan";
				if (response.matkul && response.matkul.length > 0) {
					msg += "\n" + response.matkul.join(', ');
				}
				swal("Gagal!", msg, "error");
			}
		},
		error: function() {
			swal("Error", "Terjadi kesalahan saat menyimpan data", "error");
		}
	});
}
</script>
@endpush
@endsection
