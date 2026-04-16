@php
	$jenisBobotData = DB::table('jenisbobot')->select(DB::raw('"-" as Persen,jenisbobot.ID as JenisBobotID,jenisbobot.Nama as jenisnama,jenisbobot.Modify'))->where('jenisbobot.ID', '3')->groupBy("jenisbobot.ID")->orderBy('jenisbobot.Urut', 'ASC')->get();

	$arr = [];
	$jumlah = $jenisBobotData->count();
	$th = '';

	foreach ($jenisBobotData as $hasil) {
		$b = get_field($hasil->JenisBobotID, 'jenisbobot');
		$ket_persen = ($hasil->Persen != '-') ? '(' . $hasil->Persen . '%)' : '';
		$width_persen = ($hasil->Persen != '-') ? '15%' : '30%';
		$th .= '<th style="text-align:center; width: ' . $width_persen . ';vertical-align:middle;">&nbsp;&nbsp;' . $b . '&nbsp;&nbsp;' . $ket_persen . '&nbsp;&nbsp;</th>';
	}
@endphp
<div class="col-md-12 text-right">
	<div class="btn-group">
		<button class="btn btn-primary dropdown-toggle waves-effect waves-light" id="dropdown_validasi" data-toggle="dropdown">
			&nbsp; Validasi Dosen
			<span class="mdi mdi-chevron-down"></span>
		</button>
		<div class="dropdown-menu">
			<a href="javascript:void(0);" onclick="PublishAll(1,'ValidasiDosen')" class="dropdown-item"> <i class="fa fa-thumbs-up"></i> Set Validasi Semua</a>
			<a href="javascript:void(0);" onclick="PublishAll(0,'ValidasiDosen')" class="dropdown-item"><i class="fa fa-thumbs-down"></i> Set Batalkan Validasi Semua</a>
		</div>
	</div>

	<div class="btn-group">
		<button class="btn btn-primary dropdown-toggle waves-effect waves-light" id="dropdown_publish" data-toggle="dropdown">
			&nbsp; Publish Nilai
			<span class="mdi mdi-chevron-down"></span>
		</button>
		<div class="dropdown-menu">
			<a href="javascript:void(0);" onclick="PublishAll(1,'Publish')" class="dropdown-item"> <i class="fa fa-thumbs-up"></i> Set Publish Semua</a>
			<a href="javascript:void(0);" onclick="PublishAll(0,'Publish')" class="dropdown-item"><i class="fa fa-thumbs-down"></i> Set Batalkan Publish Semua</a>
		</div>
	</div>
</div>
<div class="col-md-12 mt-2">
	<!-- Categories Table -->
	<div class="table-responsive">
		<table class="table table-bordered tablesorter">
			<thead class="bg-primary text-white">
				<tr>
					<th class="text-center align-middle" style="width:2%;"><input type="checkbox" class="selectAll"></th>
					<th class="text-center align-middle" style="width:2%;">No. </th>
					<th class="text-center align-middle">Nama</th>
					<th class="text-center align-middle" style="width:10%;">Hdr<br>Asli</th>
					{!! $th !!}
					<th class="text-center align-middle">Status Validasi Dosen</th>
					<th class="text-center align-middle">Status Publish</th>
				</tr>
			</thead>
			<tbody>
				@php
					$totalRow = count($query ?? []);
					$dataAllBobot = $jenisBobotData;
					$countAllBobot = $dataAllBobot->count();
					$a = 1;
					$x = 0;
					$jum1 = 0;
				@endphp

				@if($totalRow > 0)
					@foreach($query as $row)
						@php
							$arr_bobot_mahasiswa_all = [];
							foreach ($dataAllBobot as $rowAllBobot) {
								$sql_bobot = "SELECT * FROM bobot_mahasiswa WHERE MhswID='$row->MhswID' AND DetailKurikulumID='$DetailKurikulumID' AND TahunID='$TahunID' AND JenisBobotID='$rowAllBobot->JenisBobotID'";
								$bobot_mahasiswa = DB::select($sql_bobot);
								$bobot_mahasiswa = !empty($bobot_mahasiswa) ? $bobot_mahasiswa[0] : null;
								if ($bobot_mahasiswa && $bobot_mahasiswa->Nilai >= 0) {
									$arr_bobot_mahasiswa_all[$bobot_mahasiswa->JenisBobotID] = $bobot_mahasiswa;
								}
							}
							$count_bobot_mahasiswa_all = count($arr_bobot_mahasiswa_all);

							$row->ValidasiDosen = $arr_bobot_mahasiswa_all[3]->ValidasiDosen ?? '';
							$row->Publish = $arr_bobot_mahasiswa_all[3]->Publish ?? '';
						@endphp
						<tr style="background: {{ ($row->jadwalID ?? '') != $JadwalID ? 'lightgoldenrodyellow' : 'white' }}">
							<td class="center">
								<input type="hidden" id="mhswID{{ $row->MhswID ?? '' }}" style="width: 5%;" name="mhswID[]" value="{{ $row->MhswID ?? '' }}" />
								<input type="hidden" id="rencanastudiID{{ $row->MhswID ?? '' }}" style="width: 5%;" name="rencanastudiID[{{ $row->MhswID ?? '' }}]" value="{{ $row->rencanastudiID ?? '' }}" />
								@if('1' == '1')
									@if(isset($arr_bobot_mahasiswa_all[$hasil->JenisBobotID]) && $arr_bobot_mahasiswa_all[$hasil->JenisBobotID]->Nilai > 0)
										<input type="checkbox" name="checkID[]" style="cursor: pointer;" value="{{ $row->MhswID ?? '' }}" class="checkID" id="checkID{{ $x++ }}" onclick="show_dropdown_publish()">
									@else
										-
									@endif
								@else
									-
									@if(!empty($row->nilaiID))
										<input type="hidden" name="adaNilaiBisaInput[]" value="{{ $row->MhswID ?? '' }}">
									@endif
								@endif
							</td>
							<td class="text-center align-middle">{{ $a }}</td>
							<td class="text-left align-middle" style="{{ $jumlah > 0 ? 'width: 25%;' : '' }}">
								<span id="npm_{{ $row->MhswID ?? '' }}">{{ $row->npm ?? '' }}</span><br/>
								{{ $row->nama ?? '' }}<br>
								Kelas {{ $row->namaKelas ?? '' }}<br>
								<label class="badge badge-info"> {{ $row->namaKurikulum ?? '' }}</label>
							</td>
							<td class="text-center align-middle">
								@php
									$num = DB::select("SELECT SUM(b.Nilai) as s FROM presensimahasiswa a,jenispresensi b WHERE a.MhswID='$row->MhswID' AND a.JadwalID='$JadwalID' AND a.JenisPresensiID=b.ID ORDER BY a.JenisPresensiID");
									$num = !empty($num) ? $num[0] : null;
									$total = DB::select("SELECT * FROM presensimahasiswa WHERE MhswID='$row->MhswID' AND JadwalID='$JadwalID' ORDER BY JenisPresensiID");
									$presensi = ($num && $num->s && $total) ? round(($num->s / count($total)) * 100) : 0;
								@endphp
								{{ (!empty($presensi) ? $presensi : 0) }} %
							</td>
							@php $b = 1; @endphp
							@foreach ($dataAllBobot as $hasil)
								@php
									$ambil = $arr_bobot_mahasiswa_all[$hasil->JenisBobotID] ?? null;
									$readonly_uts = ($row->ValidasiDosen == '1') ? 'readonly' : '';
								@endphp
								<td style="width:12%" class="align-middle">
									{{ $ambil->Nilai ?? '' }}
								</td>
								@php $b++; @endphp
							@endforeach
							<td style="" class="align-middle">
								@if($row->ValidasiDosen == '1')
									<label class="badge badge-success">Sudah</label>
								@else
									<label class="badge badge-danger">Belum</label>
								@endif
							</td>
							<td style="" class="align-middle">
								@if($row->Publish == '1')
									<label class="badge badge-success">Sudah</label>
								@else
									<label class="badge badge-danger">Belum</label>
								@endif
							</td>
						</tr>
						@php
							$jum1 = $jum1 + 1;
							$a++;
						@endphp
					@endforeach
				@endif
			</tbody>
		</table>
	</div>
	<!-- End Categories Table -->
</div>
<script>
	$('.selectAll').click(function(e) {
		var table = $(e.target).closest('table');
		$('td input:checkbox', table).prop('checked', $(this).prop("checked"));
		show_dropdown_publish()
	});

	function show_dropdown_publish() {
		i = 0;
		hasil = false;
		while (document.getElementsByName('checkID[]').length > i) {
			var checkname = $('#checkID' + i).is(':checked');

			if (checkname == true) {
				hasil = true;
			}
			i++;
		}
		if (hasil == true) {
			$('#dropdown_validasi').removeAttr('disabled');
			$('#dropdown_validasi').removeAttr('href');
			$('#dropdown_validasi').removeAttr('title');
			$('#dropdown_validasi').attr('href', '#hapus');

			$('#dropdown_publish').removeAttr('disabled');
			$('#dropdown_publish').removeAttr('href');
			$('#dropdown_publish').removeAttr('title');
			$('#dropdown_publish').attr('href', '#hapus');
		} else {
			$('#dropdown_validasi').attr('disabled', 'disabled');
			$('#dropdown_validasi').attr('href', '#');
			$('#dropdown_validasi').attr('title', 'Pilih dahulu data yang akan di Publish');

			$('#dropdown_publish').attr('disabled', 'disabled');
			$('#dropdown_publish').attr('href', '#');
			$('#dropdown_publish').attr('title', 'Pilih dahulu data yang akan di Publish');
		}
	}
	show_dropdown_publish();

	function PublishAll(valid, tipe) {
		var jadwalID = '{{ $JadwalID ?? "" }}';
		var tahunID = '{{ $TahunID ?? "" }}';
		var detailkurikulum = '{{ $DetailKurikulumID ?? "" }}';

		var url = "{{ url('publish_nilai_uts/publish_all_uts') }}";

		var selected = [];
		$('input:checkbox[name="checkID[]"]:checked').each(function() {
			selected.push($(this).val());
		});


		$.ajax({
			type: 'POST',
			dataType: 'JSON',
			url: url,
			data: {
				_token: "{{ csrf_token() }}",
				jadwalID: jadwalID,
				tahunID: tahunID,
				detailkurikulum: detailkurikulum,
				valid: valid,
				tipe: tipe,
				selected: selected,
				periode_penilaian: 'UTS'
			},
			beforeSend: function(r) {
				$('.loadin').fadeIn('fast');
			},
			success: function(data) {
				$('.loadin').fadeOut('fast');
				if (data.status == '1') {
					$('#load_modal_large').modal('hide');
					filter();
					swal('Pemberitahuan', data.message, 'success');
				} else {
					swal('Pemberitahuan', data.message, 'error');
				}
			},
			error: function(data) {
				$('.loadin').fadeOut('fast');
				swal('Pemberitahuan', 'Mohon maaf data set valid disimpan !', 'error');
			}
		});
	}
</script>
