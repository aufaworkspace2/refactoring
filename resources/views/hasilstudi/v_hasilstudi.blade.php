@extends('layouts.template1')
@section('content')
<div class="card">
    <div class="card-body">
        <div class="form-row">
            <div class="form-group col-md-4">
                <label class="col-form-label">
                    <h5 class="m-0">Tahun Semester</h5>
                </label>
                <select class="TahunID form-control" id="TahunID" onchange="filter()">
                    @foreach(get_all('tahun', 'TahunID', 'DESC') as $raw)
                        <option {{ ($raw->ProsesBuka == '1') ? 'selected' : '' }} value="{{ $raw->ID }}">{{ $raw->Nama }} {{ ($raw->ProsesBuka == '1') ? '(Aktif)' : '' }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-4">
                <label class="col-form-label">
                    <h5 class="m-0">Program Kuliah</h5>
                </label>
                <select class="ProgramID form-control" id="ProgramID" onchange="filter()">
                    <option value=""> -- Lihat Semua -- </option>
                    @foreach(get_all('program') as $row)
                        <option value="{{ $row->ID }}">{{ $row->Nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-4">
                <label class="col-form-label">
                    <h5 class="m-0">Program Studi</h5>
                </label>
                <select class="ProdiID form-control" id="ProdiID" onchange="filter()">
                    <option value=""> -- Lihat Semua -- </option>
                    @foreach(get_all('programstudi') as $row)
                        <option value="{{ $row->ID }}">{{ $row->ProdiID }} || {{ get_field($row->JenjangID, 'jenjang') }} || {{ $row->Nama }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-4">
                <label class="col-form-label">
                    <h5 class="m-0">Tahun Masuk</h5>
                </label>
                <select class="TahunMasuk form-control" id="TahunMasuk" onchange="filter()">
                    <option value=""> -- Lihat Semua -- </option>
                    @foreach(DB::table('mahasiswa')->select('TahunMasuk')->distinct()->orderBy('TahunMasuk', 'DESC')->get() as $row)
                        @if($row->TahunMasuk)
                            <option value="{{ $row->TahunMasuk }}">{{ $row->TahunMasuk }}</option>
                        @endif
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-4">
                <label class="col-form-label">
                    <h5 class="m-0">Semester Masuk</h5>
                </label>
                <select class="SemesterMasuk form-control" id="SemesterMasuk" onchange="filter()">
                    <option value=""> -- Lihat Semua -- </option>
                    <option value="1">Ganjil</option>
                    <option value="2">Genap</option>
                </select>
            </div>

            <div class="form-group col-md-4">
                <label class="col-form-label ">
                    <h5 class="m-0">Pencarian</h5>
                </label>
                <input type="text" class="form-control keyword" id="keyword" onkeyup="filter()" placeholder="Kata Kunci .." />
            </div>
        </div>
    </div>
</div>
<div class="card">
    <div class="card-body">
        <div id="konten"></div>
    </div>
</div>

<div id="div_modal">
	<div class="modal" id="transkrip_modal" tabindex="-1" role="dialog">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
                    <h4 class="modal-title">Cetak KRS</h4>
					<button type="button" class="close" data-dismiss="modal">&times;</button>
				</div>
				<div class="modal-body">
					<div class="card">
						<div class="card-header">
							<strong>Data KRS</strong>
						</div>
						<div class="card-body">
							<div class="form-row">
								<div class="form-group col-md-12">
									<input type="hidden" name="id_mhsw_input" id="id_mhsw_input" value="">
									<label class="col-form-label">
										<h5 class="mb-0">Tanggal Cetak*</h5>
									</label>
									<input type='date' id='tgl_cetak' class='form-control tgl' value="{{ date('Y-m-d') }}">
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-success" onclick="cetakKHSMhsw('KRS')">Cetak KRS</button>
				</div>
			</div>
		</div>
	</div>
</div>

@push('scripts')
<script type="text/javascript">
	function filter(url) {
		if (url == null)
			url = "{{ url('hasilstudi/search') }}";

		$.ajax({
			type: "POST",
			url: url,
			data: {
                _token: "{{ csrf_token() }}",
				ProgramID: $("#ProgramID").val(),
				ProdiID: $("#ProdiID").val(),
				TahunMasuk: $("#TahunMasuk").val(),
				TahunID: $("#TahunID").val(),
				SemesterMasuk: $("#SemesterMasuk").val(),
				keyword: $("#keyword").val()
			},
			success: function(data) {
				$("#konten").html(data);
			}
		});
		return false;
	}

	function checkall(chkAll, checkid) {
		if (checkid != null) {
			if (checkid.length == null) checkid.checked = chkAll.checked;
			else
				for (i = 0; i < checkid.length; i++) checkid[i].checked = chkAll.checked;

			$("input:checkbox[name='checkID[]']").parents('tr').removeClass('checked_tabel');
			$("input:checkbox[name='checkID[]']:checked").parents('tr').addClass('checked_tabel');
		}
	}

    function tampilkan_krs(ID) {
		$('#id_mhsw_input').val(ID)
		$('#transkrip_modal').modal('show');
	}

	function cetakKHSMhsw(jenis) {
		var thn = $("#TahunID").val();
        var id = $('#id_mhsw_input').val();
        var tgl = $('#tgl_cetak').val();
		if (jenis == "KHS") {
			window.open("{{ url('hasilstudi/filterPDF') }}?TahunID=" + thn + "&MhswID=" + id, '_Blank');
		} else {
			window.open("{{ url('hasilstudi/filterPDFKRS') }}?TahunID=" + thn + "&MhswID=" + id + "&tgl_cetak=" + tgl, '_Blank');
		}
	}

	filter();
</script>
@endpush
@endsection
