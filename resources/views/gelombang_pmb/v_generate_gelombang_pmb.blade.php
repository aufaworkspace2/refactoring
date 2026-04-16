@extends('layouts.template1')

@section('content')
<div class="card">
	<div class="card-body">
		<div class="row">
			<div class="col-md-12">
				<div class="button-list">
					@if($Create == 'YA')<button class="btn btn-bordered-primary waves-effect  width-md waves-light" id="btnProses" data-placement="top" title="Silahkan pilih data terlebih dahulu." data-toggle="modal"><i class="fa fa-cog"></i> Proses Data</button>@endif
					<a href="{{ url('gelombang_pmb/detail') }}?gelombang_id={{ $gelombang_id ?? '' }}" class="btn btn-bordered-success waves-effect  width-md waves-light"><icon class="mdi mdi-arrow-left"></icon> Kembali</a>
				</div>
			</div>
		</div>
		<div class="form-row">
			<div class="form-group col-md-3"><label class="col-form-label mt-2"><h5 class="mb-0">Program</h5></label><select class="program form-control" onchange="filter()"><option value=""> -- {{ __('app.view_all') }} -- </option>@php $program_list = get_all('program'); @endphp@foreach($program_list as $row)<option value="{{ $row->ID }}">{{ $row->Nama }}</option>@endforeach</select></div>
            <div class="form-group col-md-3"><label class="col-form-label mt-2"><h5 class="mb-0">Prodi</h5></label><select class="prodi form-control" onchange="filter()"><option value=""> -- {{ __('app.view_all') }} -- </option>@php $programstudi_list = get_all('programstudi'); @endphp@foreach($programstudi_list as $row)@php $jenjang = function_exists('get_field') ? get_field($row->JenjangID,'jenjang') : ''; @endphp<option value="{{ $row->ID }}">{{ $jenjang }} || {{ $row->Nama }}</option>@endforeach</select></div>
            <div class="form-group col-md-3"><label class="col-form-label mt-2"><h5 class="mb-0">Jalur</h5></label><select class="jalur form-control" onchange="filter()"><option value=""> -- {{ __('app.view_all') }} -- </option>@php $jalur_list = get_all('pmb_edu_jalur_pendaftaran'); @endphp@foreach($jalur_list as $row)<option value="{{ $row->id }}">{{ $row->nama }}</option>@endforeach</select></div>
			<div class="form-group col-md-3"><label class="col-form-label mt-2"><h5 class="mb-0">Status</h5></label><select class="status form-control" onchange="filter()"><option value=""> -- {{ __('app.view_all') }} -- </option><option value="1"> Sudah Dibuat </option><option value="2"> Belum Dibuat </option></select></div>
		</div>
	</div>
</div>
<div class="card"><div class="card-body"><div id="konten"></div></div></div>

<div id="proses" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="proses" aria-hidden="true">
	<div class="modal-dialog"><div class="modal-content">
		<div class="modal-header"><h4 class="modal-title" id="proses">Konfirmasi Proses Data</h4><button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button></div>
		<div class="modal-body"><p>Apakah anda yakin ingin memproses data ini ?</p><p class="data_name"></p></div>
		<div class="modal-footer"><button type="button" class="btn btn-primary waves-effect" id="btnConfirmProses">Proses</button><button type="button" class="btn btn-default waves-effect" data-dismiss="modal">Batal</button></div>
	</div></div>
</div>
@endsection

@push('scripts')
<script type="text/javascript">
$.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
function filter(url) { if(url == null) url = "{{ url('gelombang_pmb/search_generate_gelombang', [$gelombang_id ?? '']) }}"; $.ajax({ type: "POST", url: url, data: { gelombang_id : '{{ $gelombang_id ?? '' }}', program : $(".program").val(), prodi : $(".prodi").val(), jalur : $(".jalur").val(), status : $(".status").val() }, success: function(data) { $("#konten").html(data); } }); return false; }
filter();
function checkall(chkAll,checkid) { if (checkid != null) { if (checkid.length == null) checkid.checked = chkAll.checked; else for (i=0;i<checkid.length;i++) checkid[i].checked = chkAll.checked; $("input:checkbox[name='checkID[]']").parents('tr').removeClass('table-danger'); $("input:checkbox[name='checkID[]']:checked").parents('tr').addClass('table-danger'); } }
window.show_btnDelete = function(){ i=0; hasil = false; while(document.getElementsByName('checkID[]').length > i){ var el = document.getElementById('checkID'+i); if(el && el.checked){ hasil = true; } i++; } if(hasil == true) { $('#btnProses').removeAttr('disabled'); $('#btnProses').attr('href', '#proses'); } else { $('#btnProses').attr('disabled','disabled'); $('#btnProses').attr('href','#'); } }
show_btnDelete();
$("input:checkbox[name='checkID[]']").click(function(){ if(this.checked == true){ $(this).parents('tr').addClass('table-danger'); } else { $(this).parents('tr').removeClass('table-danger'); } });
$('#btnProses').click(function(){ var checkCount = $("input:checkbox[name='checkID[]']:checked").length; $('.data_name').html(checkCount + ' data akan diproses'); });
$('#btnConfirmProses').click(function(){ $('#proses').modal('hide'); $.ajax({ type: "POST", url: "{{ url('gelombang_pmb/proses_generate_gelombang') }}", data: $("#f_generate_gelombang_pmb").serialize(), dataType: "JSON", beforeSend: function() { silahkantunggu(); }, success: function(respond){ if(respond.status == 1){ berhasil(); alertsuccess(); filter(); }else{ alertfail(); berhasil(); } }, error: function(data){ alertfail('Kesalahan jaringan, cobalah beberapa saat lagi.'); } }); });
</script>
@endpush
