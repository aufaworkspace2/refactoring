<form id="f_save_setregistrasiulang" action="{{ url('setregistrasiulang/save') }}" >

<div class="form-row">
    <div class="form-group col-md-4">
        <select name="action_do" id="action_do" class="form-control select2" onchange="show_btnSubmit()"  >
            <option value="">Pilih Aksi</option>
            <option value="registrasi">Set Tagihan</option>
            <option value="tidakregistrasi">Set Tidak Dapat tagihan</option>
            <option value="batalregistrasi">Batal Set Tagihan </option>
        </select>
    </div>
    <div class="col-md-8">
        <button disabled id="btnSubmit" name="act" type="submit" class="btn btn-bordered-success waves-effect waves-light small"> Submit </button>
    </div>
</div>
<p>{!! $total_row !!}</p>
<div class="table-responsive">
    <table class="table table-bordered mb-0 table-hover tablesorter">
        <thead class="bg-primary text-white">
            <tr>
            @if($Update == 'YA')
                <th width="2%">
                    <div class="checkbox checkbox-info">
                        <input type="checkbox" name="checkAll" id="checkAll" onClick="checkall(this,document.forms.namedItem('f_save_setregistrasiulang')); show_btnSubmit();">
                        <label for="checkAll"></label>
                    </div>
                </th>
            @endif
                <th class="text-center" width="1%">No.</th>
                <th width="15%">No Ujian</th>
                <th width="30%">Nama</th>
                <th width="14%">Pilihan</th>
                <th width="10%">Program</th>
                <th width="10%">Jumlah Tagihan</th>
                <th class="text-center">Lulus</th>
                <th class="text-center">Status Posting Tagihan</th>
            </tr>
        </thead>
        <tbody>
            @php $no=$offset; $i=0; @endphp
            @foreach($query as $row)
                @php $row = (object) $row; @endphp
                <tr class="setregistrasiulang_{{ $row->ID ?? '' }}">
                @if($Update == 'YA')
                    <td class="align-middle">
                        @if(empty($row->cek_cicilan_registrasiulang))
                        <div class="checkbox checkbox-info">
                            <input type="checkbox" name="checkID[]" id="checkID{{ $i }}" onclick="show_btnSubmit()" value="{{ $row->ID ?? '' }}" >
                            <label for="checkID{{ $i }}"></label>
                        </div>
                        @php $i++; @endphp
                        @else
                        -
                        @endif
                    </td>
                @endif
                    <td class="text-center">{{ ++$no }}.</td>
                    <td class="text-center">{{ $row->noujian_pmb ?? '' }}</td>
                    <td>
                        {{ $row->Nama ?? '' }}
                    </td>
                    <td>
                        @if(!empty($row->prodilulus_pmb))
                        Lulus : {{ ($all_prodi[$row->prodilulus_pmb]['NamaJenjang'] ?? '') }} {{ ($all_prodi[$row->prodilulus_pmb]['Nama'] ?? '') }}<br>
                        @endif
                        1. {{ ($all_prodi[$row->pilihan1]['NamaJenjang'] ?? '') }} {{ ($all_prodi[$row->pilihan1]['Nama'] ?? '') }}
                        @if(!empty($row->pilihan2))
                        <br>2. {{ ($all_prodi[$row->pilihan2]['NamaJenjang'] ?? '') }} {{ ($all_prodi[$row->pilihan2]['Nama'] ?? '') }}
                        @endif
                        @if(!empty($row->pilihan3))
                        <br>3. {{ ($all_prodi[$row->pilihan3]['NamaJenjang'] ?? '') }} {{ ($all_prodi[$row->pilihan3]['Nama'] ?? '') }}
                        @endif
                    </td>

                    <td>
                        {{ $row->programNama ?? '-' }}
                        <input type="hidden" name="idpend[]" value="{{ $row->ID ?? '' }}" />
                    </td>

                    <td>
                        {{ number_format($row->JumlahTagihan ?? 0, 0, ',', '.') }}
                    </td>

                <td>
                {!! $row->statuslulus_str ?? '' !!} <br>
                </td>

                <td>
                {!! $row->statusregistrasi_str ?? '' !!} <br>
                </td>

                </tr>
            @endforeach

        </tbody>
    </table>
    <div id="hapus" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="hapus" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="hapus">Konfirmasi</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                </div>
                <div class="modal-body">
                    <p>Apakah Anda yakin akan mengubah data ini?</p>
                    <p class="data_name"></p>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-danger waves-effect" >Simpan</button>
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
<div id="modal-table-all" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modal-table-all" aria-hidden="true">
    <form id="f_uploadexcel" action="{{ url('setregistrasiulang/upload_excel') }}" enctype="multipart/form-data">
    <div class="modal-body">
        <table style="border:none">
            <tr>
                <td style="width:30%">Download Template</td>
                <td style="width:10%">:</td>
                <td style="width:60%">
                    <a target="_blank" href="{{ url('setregistrasiulang/template/' . $linkurlpage) }}" >Download</a>
                </td>
            </tr>
            <tr>
                <td style="width:30%">Upload Template Excel</td>
                <td style="width:10%">:</td>
                <td style="width:60%"><input type="file" name="fileUpload" id="fileUpload" /></td>
            </tr>
        </table>
    </div>
    <div class="modal-footer">

        <button class="btn btn-small btn-success btnUpload" type="button" onclick="$('#f_uploadexcel').submit()" id="btnUpload">
            <i class="icon-upload"></i>
            Upload
        </button>

        &nbsp; &nbsp; &nbsp;
        <button class="btn btn-small btn-danger" type="button" data-dismiss="modal">
            <i class="icon-remove"></i>
            Batal
        </button>
    </div>
</form>
</div>

<script>
// Initialize tablesorter
if(typeof tablesorter === 'function') {
    tablesorter();
}

// Initialize button state
if(typeof show_btnSubmit === 'function') {
    show_btnSubmit();
}

// Initialize checkbox click handlers
$(document).on('click', "input:checkbox[name='checkID[]']", function(){
    if(this.checked == true){
        $(this).parents('tr').addClass('table-danger');
    }
    else
    {
        $(this).parents('tr').removeClass('table-danger');
    }
    if(typeof show_btnSubmit === 'function') {
        show_btnSubmit();
    }
});
</script>
