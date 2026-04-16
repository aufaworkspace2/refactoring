@extends('layouts.template1')
@section('content')
<div class="card">
	<div class="card-body">
		<form id="f_hasilstudi" onsubmit="savedata(this); return false;" action="{{ url('hasilstudi/save/' . ($save ?? 1)) }}" enctype="multipart/form-data">
            @csrf
			<input type="hidden" name="ID" id="ID" value="{{ $row->ID ?? '' }}">
			<h3>Hasil Studi</h3>
            <div class="form-row mt-3">
                <div class="form-group col-md-6">
                    <label class="col-form-label" for="TahunID">Tahun ID *</label>
                    <input type="text" id="TahunID" name="TahunID" class="form-control" value="{{ $row->TahunID ?? '' }}" required />
                </div>
                
                <div class="form-group col-md-6">
                    <label class="col-form-label" for="ProgramID">Program ID *</label>
                    <input type="text" id="ProgramID" name="ProgramID" class="form-control" value="{{ $row->ProgramID ?? '' }}" required />
                </div>
                
                <div class="form-group col-md-6">
                    <label class="col-form-label" for="ProdiID">Prodi ID *</label>
                    <input type="text" id="ProdiID" name="ProdiID" class="form-control" value="{{ $row->ProdiID ?? '' }}" required />
                </div>
                
                <div class="form-group col-md-6">
                    <label class="col-form-label" for="NPM">NPM *</label>
                    <input type="text" id="NPM" name="NPM" class="form-control" value="{{ $row->NPM ?? '' }}" required />
                </div>
                
                <div class="form-group col-md-6">
                    <label class="col-form-label" for="StatusMhswID">Status Mhsw ID *</label>
                    <input type="text" id="StatusMhswID" name="StatusMhswID" class="form-control" value="{{ $row->StatusMhswID ?? '' }}" required />
                </div>
                
                <div class="form-group col-md-6">
                    <label class="col-form-label" for="Semester">Semester *</label>
                    <input type="text" id="Semester" name="Semester" class="form-control" value="{{ $row->Semester ?? '' }}" required />
                </div>
                
                <div class="form-group col-md-6">
                    <label class="col-form-label" for="IPS">IPS *</label>
                    <input type="text" id="IPS" name="IPS" class="form-control" value="{{ $row->IPS ?? '' }}" required />
                </div>
                
                <div class="form-group col-md-6">
                    <label class="col-form-label" for="SKSIPS">SKS IPS *</label>
                    <input type="text" id="SKSIPS" name="SKSIPS" class="form-control" value="{{ $row->SKSIPS ?? '' }}" required />
                </div>
                
                <div class="form-group col-md-6">
                    <label class="col-form-label" for="IPK">IPK *</label>
                    <input type="text" id="IPK" name="IPK" class="form-control" value="{{ $row->IPK ?? '' }}" required />
                </div>
                
                <div class="form-group col-md-6">
                    <label class="col-form-label" for="SKSIPK">SKS IPK *</label>
                    <input type="text" id="SKSIPK" name="SKSIPK" class="form-control" value="{{ $row->SKSIPK ?? '' }}" required />
                </div>
            </div>
            
            <div class="button-list">
                <button type="submit" class="btn btn-bordered-primary waves-effect waves-light btnSave">Simpan Data</button>
                <button type="button" onClick="window.history.back()" class="btn btn-bordered-danger waves-effect waves-light">Kembali</button>
            </div>
		</form>
	</div>
</div>

@push('scripts')
<script type="text/javascript">
function savedata(formz) {
	var formData = new FormData(formz);	
	$.ajax({
		type:'POST',
		url: $(formz).attr('action'),
		data:formData,
		cache:false,
		contentType: false,
		processData: false,
		success:function(data){
			alertsuccess("Data Berhasil Disimpan");
            window.location.href = "{{ url('hasilstudi') }}";
		},
		error: function(data){
			alertfail("Gagal menyimpan data");
		}
	});
}
</script>
@endpush
@endsection
