<p>{!! $total_row ?? 0 !!}</p>
<form id="f_delete_keterangan_status_mahasiswa" action="{{ url('keterangan_status_mahasiswa/delete') }}">
    @csrf
	<div class="table-responsive">
		<table class="table table-hover table-bordered tablesorter table_data_status">
			<thead class="bg-primary text-white">
				<tr>
					@if ($Delete == 'YA')
						<th width="2%">
							<div class="checkbox checkbox-info">
								<input type="checkbox" name="checkAll" id="checkAll" onClick="checkall(this,document.forms.namedItem('f_delete_keterangan_status_mahasiswa')); show_btnDelete();">
								<label for="checkAll"></label>
							</div>
						</th>
					@endif
					<th class="text-center" width="2%">No.</th>
					<th>Mahasiswa</th>
					<th>NPM</th>
					<th>Status</th>
					<th>Nomor Surat</th>
					@if (($StatusMhswID ?? '') == 2)
						<th>Mulai Semester</th>
						<th>Akhir Semester</th>
					@endif
					<th>Alasan</th>
					<th>Tahun Semester</th>
					<th>Tanggal</th>
					<th width="10%">Re-Active</th>
				</tr>
			</thead>
			<tbody>
				@foreach ($query as $index => $row)
                    @php 
                        $cek_mahasiswa_status = get_field($row['MhswID'], 'mahasiswa', 'StatusMhswID');
                    @endphp
					<tr id="keteranganstatusmahasiswa_{{ $row['ID'] }}">
						@if ($Delete == 'YA')
							<td class="align-middle">
								<div class="checkbox checkbox-info">
									<input type="checkbox" name="checkID[]" id="checkID{{ $index }}" onclick="show_btnDelete()" value="{{ $row['ID'] }}">
									<label for="checkID{{ $index }}"></label>
								</div>
							</td>
						@endif
						<td class="text-center">{{ ++$offset }}.</td>
						<td>
							@if ($row['StatusMahasiswaID'] != 1 && $cek_mahasiswa_status != 3)
								<a href="{{ url('keterangan_status_mahasiswa/view/'.$row['ID']) }}">{{ $row['Nama'] ?? '' }}</a>
							@else
								{{ $row['Nama'] ?? '' }}
							@endif
						</td>
						<td>{{ $row['NPM'] ?? '' }}</td>
						<td>{{ $row['Status'] ?? '' }}</td>
						<td>{{ $row['Nomor_Surat'] ?? '' }}</td>
						@if (($StatusMhswID ?? '') == 2)
							<td>{{ $row['Mulai_Semester'] ?? '' }}</td>
							<td>{{ $row['Akhir_Semester'] ?? '' }}</td>
						@endif
						<td>{{ $row['Alasan'] ?? '' }}</td>
						<td>{{ get_field($row['TahunID'], 'tahun') }}</td>
						<td>{{ !empty($row['Tgl']) ? tgl($row['Tgl'], '02') : '' }}</td>
						<td class="text-center">
							@if ($row['StatusMahasiswaID'] != 1 && $cek_mahasiswa_status != 3)
								<button type="button" onclick="show_modal_reactive({{ $row['ID'] }})" class="btn btn-primary btn-sm waves-effect">
									Re-Active
								</button>
							@else
								-
							@endif
						</td>
					</tr>

					@if ($row['StatusMahasiswaID'] != 1)
						<div id="modal2{{ $row['ID'] }}" class="modal modal_reactive" tabindex="-1" role="dialog" data-keyboard="false" data-backdrop="static">
							<div class="modal-dialog">
								<div class="modal-content">
									<div class="modal-header">
										<h4 class="modal-title">Konfirmasi</h4>
										<button type="button" class="close" data-dismiss="modal" aria-hidden="true" onclick="remove_hl()">×</button>
									</div>
									<div class="modal-body">
										<h4 style="color:red; font-weight:bold;">Peringatan !<br> </h4>
										<b>Jika anda mengaktifkan data tersebut maka semua histori transaksi pada Rencana Studi, Presensi akan dikembalikan aktif secara otomatis oleh sistem.</b>
										<br><br>
										<p>Apakah Anda yakin akan mengaktifkan kembali data <b>{{ $row['Nama'] ?? '' }} ?</b></p>
									</div>
									<div class="modal-footer">
										<button type="button" onclick="reactive({{ $row['ID'] }})" class="btn btn-primary waves-effect">Re-Active</button>
										<button type="button" class="btn btn-danger waves-effect waves-light" onclick="remove_hl()" data-dismiss="modal">Tutup</button>
									</div>
								</div>
							</div>
						</div>
					@endif
				@endforeach
			</tbody>
		</table>
		<div id="hapus" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<h4 class="modal-title">Konfirmasi Hapus</h4>
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
					</div>
					<div class="modal-body">
						<p>Apakah Anda yakin ingin menghapus data yang dipilih?</p>
						<p class="data_name"></p>
					</div>
					<div class="modal-footer">
						<button type="submit" class="btn btn-danger waves-effect">Hapus</button>
						<button type="button" class="btn btn-primary waves-effect waves-light" data-dismiss="modal">Tutup</button>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			{!! $link !!}
		</div>
	</div>
</form>

<script>
	$("#f_delete_keterangan_status_mahasiswa").submit(function(e) {
        e.preventDefault();
		$.ajax({
			type: "POST",
			url: $(this).attr('action'),
			data: $(this).serialize(),
			success: function(data) {
				$('.modal_reactive').modal('hide');
				$('#hapus').modal('hide');
                swal("Berhasil", "Data berhasil dihapus.", "success");
				filter();
			},
			error: function() {
				swal("Gagal", "Data gagal dihapus.", "error");
				filter();
			}
		});
		return false;
	});

	function reactive(table_id) {
		$.ajax({
			type: "POST",
			url: "{{ url('keterangan_status_mahasiswa/reactive') }}",
			data: {
                _token: "{{ csrf_token() }}",
				tableID: table_id
			},
			success: function(data) {
				$('.modal_reactive').modal('hide');
				swal("Berhasil", "Data Mahasiswa Berhasil Diupdate.", "success");
				filter();
			},
			error: function() {
				swal("Gagal", "Data Mahasiswa Gagal Diupdate.", "error");
			}
		});
	}

	function show_modal_reactive(ID) {
		$('#modal2' + ID).modal('show');
	}

	function show_btnDelete() {
        var hasChecked = $('input:checkbox[name="checkID[]"]:checked').length > 0;
		if (hasChecked) {
			$('#btnDelete').removeAttr('disabled').attr('title', '');
		} else {
			$('#btnDelete').attr('disabled', 'disabled').attr('title', 'Pilih dahulu data yang akan di hapus');
		}
	}
	show_btnDelete();

	$("input:checkbox[name='checkID[]']").click(function() {
		if (this.checked) {
			$(this).closest('tr').addClass('table-danger');
		} else {
			$(this).closest('tr').removeClass('table-danger');
		}
	});

    function checkall(chkAll, form) {
        $(form).find('input:checkbox[name="checkID[]"]').prop('checked', chkAll.checked);
        if(chkAll.checked) {
            $(form).find('tbody tr').addClass('table-danger');
        } else {
            $(form).find('tbody tr').removeClass('table-danger');
        }
    }

	function remove_hl() {
		$(".table_data_status tr").removeClass('table-danger');
		$("input:checkbox[name='checkID[]']").prop('checked', false);
	}
</script>
