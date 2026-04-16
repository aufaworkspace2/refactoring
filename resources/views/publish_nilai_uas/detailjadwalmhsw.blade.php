@php
    $th = [];
    $th_cols = [];
    foreach($dataAllBobot as $hasil) {
        $b = get_field($hasil->JenisBobotID, 'jenisbobot');
        $ket_persen = ($hasil->Persen != '-') ? '('.$hasil->Persen.'%)' : '';
        $width_persen = ($hasil->Persen != '-') ? '15%' : '30%';
        
        if(!isset($th[$hasil->KategoriJenisBobotID])) $th[$hasil->KategoriJenisBobotID] = '';
        $th[$hasil->KategoriJenisBobotID] .= '<th style="text-align:center; width: '.$width_persen.';vertical-align:middle;">&nbsp;&nbsp;'.$b.'&nbsp;&nbsp;'.$ket_persen.'&nbsp;&nbsp;</th>';
        
        if(!isset($th_cols[$hasil->KategoriJenisBobotID])) $th_cols[$hasil->KategoriJenisBobotID] = 0;
        $th_cols[$hasil->KategoriJenisBobotID] += 1;
    }
@endphp

<div class="col-md-12">
	<p class="text-danger">* Nilai yang sudah bisa di publish adalah nilai yang sudah di validasi oleh dosen</p>
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
	<div class="table-responsive">
        <table class="table table-bordered tablesorter">
			<thead class="bg-primary text-white">
				<tr>
					<th rowspan="2" class="text-center align-middle" width="2%"><input type="checkbox" class="selectAll"></th>
					<th rowspan="2" class="text-center align-middle" width="2%">No. </th>
					<th rowspan="2" class="text-center align-middle">Nama</th>
					<th rowspan="2" class="text-center align-middle" width="10%">Hdr<br>Asli</th>
					@foreach($kategori_jenisbobot as $kat_jb)
						@if(($th_cols[$kat_jb->ID] ?? 0) > 0)
							<th style="text-align: center;vertical-align: middle;" colspan="{{ $th_cols[$kat_jb->ID] }}">
							    {{ $kat_jb->Nama ?? '' }}
							</th>
						@endif
					@endforeach
					<th rowspan="2" class="text-center align-middle">Nilai Akhir</th> 
					<th rowspan="2" class="text-center align-middle">Nilai Huruf</th> 
					<th rowspan="2" class="text-center align-middle">Status Validasi Dosen</th> 
					<th rowspan="2" class="text-center align-middle" >Status Publish</th> 
				</tr>
				<tr>
					@foreach($kategori_jenisbobot as $kat_jb)
					    {!! $th[$kat_jb->ID] ?? '' !!}
					@endforeach
				</tr>
			</thead>
			<tbody>
			@forelse($query as $index => $row)
                @php
                    $publishStatus = ($row->PublishKHS == 1 && $row->PublishTranskrip == 1) ? 1 : 0;
                    $sumPresensi = $presensiData['sum'][$row->MhswID] ?? 0;
                    $totalPresensi = $presensiData['total'][$row->MhswID] ?? 0;
                    $persenPresensi = $totalPresensi > 0 ? round(($sumPresensi / $totalPresensi) * 100) : 0;
                @endphp
                <tr style="background: {{ ($row->jadwalID ?? '') != ($JadwalID ?? '') ? 'lightgoldenrodyellow' : 'white' }}">
                    <td class="text-center align-middle">
                        <input type="hidden" name="mhswID[]" value="{{ $row->MhswID ?? '' }}" />
                        <input type="hidden" name="rencanastudiID[{{ $row->MhswID ?? '' }}]" value="{{ $row->rencanastudiID ?? '' }}" />
                        <input type="checkbox" name="checkID[]" style="cursor: pointer;" value="{{ $row->MhswID ?? '' }}" class="checkID" id="checkID{{ $index }}" onclick="show_dropdown_publish()">
                    </td>
                    <td class="text-center align-middle">{{ $index + 1 }}</td>
                    <td class="text-left align-middle" style="{{ count($dataAllBobot) > 0 ? 'width: 25%;' : '' }}">
                        <span>{{ $row->npm ?? '' }}</span><br>
                        {{ $row->nama ?? '' }}<br>
                        Kelas {{ $row->namaKelas ?? '' }}<br>
                        <label class="badge badge-info">{{ $row->namaKurikulum ?? '' }}</label>
                    </td>
                    <td class="text-center align-middle">
                        {{ $persenPresensi }} %
                    </td>
                    @foreach($dataAllBobot as $hasil)
                        @php
                            $gradeRow = $arr_bobot_mahasiswa_all[$row->MhswID][$hasil->JenisBobotID] ?? null;
                        @endphp
                        <td style="text-align:center;vertical-align:middle;width:12%">
                            {{ $gradeRow->Nilai ?? '0' }}
                        </td>
                    @endforeach
                    <td class="align-middle text-center">{{ $row->akhirNilai ?? '0' }}</td>
                    <td class="align-middle text-center">{{ $row->hurufNilai ?? '-' }}</td>
                    <td class="align-middle text-center">
                        @if(($row->ValidasiDosen ?? '') == '1')
                            <label class="badge badge-success">Sudah</label>
                        @else
                            <label class="badge badge-danger">Belum</label>
                        @endif
                    </td>
                    <td class="align-middle text-center">
                        @if($publishStatus == '1')
                            <label class="badge badge-success">Sudah</label>
                        @else
                            <label class="badge badge-danger">Belum</label>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="20" class="text-center">Data tidak ditemukan</td>
                </tr>
            @endforelse
			</tbody>
		</table>
	</div>
</div>

<script>
$('.selectAll').click(function(e){
	var table = $(e.target).closest('table');
	$('td input:checkbox', table).prop('checked', $(this).prop("checked"));
	show_dropdown_publish();
});	

function show_dropdown_publish(){
    var hasChecked = $('input:checkbox[name="checkID[]"]:checked').length > 0;
	if(hasChecked) {
		$('#dropdown_validasi').removeAttr('disabled');
		$('#dropdown_publish').removeAttr('disabled');
	} else {
		$('#dropdown_validasi').attr('disabled','disabled');
		$('#dropdown_publish').attr('disabled','disabled');
	}
}
show_dropdown_publish();

function PublishAll(valid, tipe){
	var jadwalID = '{{ $JadwalID ?? '' }}';
	var tahunID = '{{ $TahunID ?? '' }}';
	var detailkurikulum = '{{ $DetailKurikulumID ?? '' }}';
	var url = "{{ url('publish_nilai_uas/publish_all_uas') }}";

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
			periode_penilaian: 'UTS' // Keeping UTS as per CI3 source, maybe it's a typo in CI3 but following rules
		},	
		beforeSend: function(){
			$('.loadin').fadeIn('fast');
		},
		success: function(data){
			$('.loadin').fadeOut('fast');
			if (data.status == '1') {
				$('#load_modal_large').modal('hide');
				filter();
				swal('Pemberitahuan', data.message, 'success');
			} else {
				swal('Pemberitahuan', data.message, 'error');
			}
		},
		error: function(){
			$('.loadin').fadeOut('fast');
			swal('Pemberitahuan', 'Mohon maaf data gagal disimpan !', 'error');
		}
	});
}
</script>
