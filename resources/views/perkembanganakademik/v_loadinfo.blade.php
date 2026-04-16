<div class="row-fluid">
    <div class="span12">
        <div class="tab-pane active" id="tab-details">
            <div class="well form-horizontal" id="oke">
                <input type='hidden' value='{{ $ID }}' id='IDDD'>
                <div class="form-group row">
                    <label class="col-sm-4 col-form-label" for="nomor">Nomor Transkrip *</label>
                    <div class="col-sm-8">
                        <input type='text' id='nomor' class='form-control'>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-4 col-form-label" for="tgl">Tanggal Lulus *</label>
                    <div class="col-sm-8">
                        <input type='date' id='tgl' class='form-control' placeholder='YYYY-MM-DD' value="{{ date('Y-m-d') }}">
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-4 col-form-label" for="tgl2">Tanggal Cetak *</label>
                    <div class="col-sm-8">
                        <input type='date' id='tgl2' class='form-control' placeholder='YYYY-MM-DD' value="{{ date('Y-m-d') }}">
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-4 col-form-label" for="Dekan">Dekan *</label>
                    <div class="col-sm-8">
                        <select class='form-control select2' id='Dekan'>
                            <option value="">-- Pilih Dekan --</option>
                            @foreach(DB::table('dosen')->orderBy('Nama')->get() as $r)
                                <option value="{{ ($r->Title ? $r->Title.'. ' : '').$r->Nama.($r->Gelar ? ', '.$r->Gelar : '') }}">
                                    {{ ($r->Title ? $r->Title.'. ' : '').$r->Nama.($r->Gelar ? ', '.$r->Gelar : '') }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-4 col-form-label" for="NIDN">NIDN *</label>
                    <div class="col-sm-8">
                        <input type='text' id='NIDN' class='form-control'>
                    </div>
                </div>
                <div class="form-group row">
                    <label class="col-sm-4 col-form-label" for="Bahasa">Bahasa *</label>
                    <div class="col-sm-8">
                        <select class='form-control' id='Bahasa'>
                            <option value='1'>Indonesia</option>
                            <option value='2'>Inggris</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
if(typeof autocomplete === 'function') {
    autocomplete("Dekan");
} else if($.fn.select2) {
    $('#Dekan').select2({
        dropdownParent: $('#mdla')
    });
}
</script>
