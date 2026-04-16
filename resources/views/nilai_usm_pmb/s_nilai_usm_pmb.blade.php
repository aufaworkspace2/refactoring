<form id="f_save_nilai_usm_pmb" action="{{ url('nilai_usm_pmb/save') }}">
    <div class="form-row">
        <div class="form-group col-md-4">
            <select name="action_do" id="action_do" class="form-control" onchange="show_btnSubmit()">
                <option value="">Pilih Aksi</option>
                <option value="simpannilai">Simpan Nilai</option>
                <option value="lulus">Set Lulus USM</option>
                <option value="tidaklulus">Set Tidak Lulus USM</option>
                <option value="batallulus">Set Batalkan Lulus USM</option>
                <!-- <option value="luluskesehatan">Set Lulus Kesehatan</option>
                <option value="tidakluluskesehatan">Set Tidak Lulus Kesehatan</option>
                <option value="batalluluskesehatan">Set Batalkan Lulus Kesehatan</option> -->
            </select>
        </div>
        <div class="col-md-8">
            <div class="button-list">
                <button disabled id="btnSubmit" name="act" type="submit" class="btn btn-bordered-success small waves-effect waves-light mt-0"> Submit </button>
                <button href="#modal-table-all" data-toggle="modal" role="button" type="button" class="btn mt-0 btn-bordered-danger waves-effect waves-light">
                    <i class="icon-upload"></i>
                    <span>Import Nilai</span>
                </button>
                <a href="{{ url('nilai_usm_pmb/export') }}" target="_blank" class="btn mt-0 btn-bordered-primary waves-effect waves-light">Export Data</a>
            </div>
        </div>
    </div>

    <p>{!! $total_row ?? '' !!}</p>
    <div class="table-responsive">
        <table class="table table-bordered mb-0 table-hover tablesorter">
            <thead class="bg-primary text-white">
                <tr>
                    @if($Delete == 'YA')
                    <th rowspan="2" class="sorterfalse">
                        <div class="checkbox checkbox-info">
                            <input type="checkbox" name="checkAll" id="checkAll" onClick="checkall(this,document.forms.namedItem('f_save_nilai_usm_pmb')); show_btnSubmit();">
                            <label for="checkAll"></label>
                        </div>
                    </th>
                    @endif
                    <th rowspan="2" class="text-center" width="1%">No.</th>
                    <th rowspan="2" width="7%">No Ujian</th>
                    <th rowspan="2" width="10%">Nama</th>
                    <th rowspan="2" width="14%">Pilihan</th>
                    <th rowspan="2" width="10%">Program</th>
                    <!--th rowspan="2" width="10%">File Surat Kesehatan</th-->
                    <th colspan="{{ count($data_jenisusm ?? []) }}" class="text-center">Nilai</th>
                    <th rowspan="2" width="5%" class="text-center">Lulus USM</th>
                    <!-- <th rowspan="2" width="5%" class="text-center">Lulus Kesehatan</th> -->
                    <th rowspan="2" width="5%" class="text-center">Surat Keterangan Lulus</th>
                </tr>
                <tr>
                    @foreach($data_jenisusm ?? [] as $idj => $j)
                        <th colspan="1" style="text-align: center;">{{ $j['nama'] ?? '' }}</th>
                    @endforeach
                    <th>Akhir</th>
                </tr>
            </thead>
            <tbody>
                @php $no = $offset ?? 0; $i = 0; @endphp
                @foreach($query ?? [] as $row)
                    @php $row = (object) $row; @endphp
                    <tr class="nilai_usm_pmb_{{ $row->ID ?? '' }}">
                        @if($Update == 'YA')
                            @if($row->statuslulus_pmb != 1)
                                <td class="align-middle">
                                    <div class="checkbox checkbox-info">
                                        <input type="checkbox" name="checkID[]" id="checkID{{ $i }}" onclick="show_btnSubmit()" value="{{ $row->ID ?? '' }}">
                                        <label for="checkID{{ $i }}"></label>
                                    </div>
                                </td>
                                @php $i++; @endphp
                            @else
                                <td>-</td>
                            @endif
                        @endif
                        <td class="text-center">{{ ++$no }}.</td>
                        <td class="text-center">{{ $row->noujian_pmb ?? '' }}</td>
                        <td>
                            {{ $row->Nama ?? '' }}
                        </td>
                        <td>
                            @php
                            $pilihanprodilulus = $all_prodi[$row->prodilulus_pmb ?? ''] ?? null;
                            $pilihan1 = $all_prodi[$row->pilihan1 ?? ''] ?? null;
                            $pilihan2 = $all_prodi[$row->pilihan2 ?? ''] ?? null;
                            $pilihan3 = $all_prodi[$row->pilihan3 ?? ''] ?? null;
                            @endphp
                            @if(!empty($row->prodilulus_pmb) && $pilihanprodilulus)
                                Lulus : {{ $pilihanprodilulus->NamaJenjang ?? '' }} {{ $pilihanprodilulus->Nama ?? '' }}<br>
                            @endif
                            1. {{ $pilihan1->NamaJenjang ?? '' }} {{ $pilihan1->Nama ?? '' }}
                            @if(!empty($row->pilihan2) && $pilihan2)
                                <br>2. {{ $pilihan2->NamaJenjang ?? '' }} {{ $pilihan2->Nama ?? '' }}
                            @endif
                            @if(!empty($row->pilihan3) && $pilihan3)
                                <br>3. {{ $pilihan3->NamaJenjang ?? '' }} {{ $pilihan3->Nama ?? '' }}
                            @endif
                        </td>

                        <td>
                            {{ $row->programNama ?? '-' }}
                            <input type="hidden" name="idpend[]" value="{{ $row->ID ?? '' }}" />
                        </td>
                       
                        @foreach($data_jenisusm ?? [] as $idj => $j)
                            <td class="text-center">
                                <input type="hidden" name="idjenisusm[{{ $row->ID ?? '' }}][]" value="{{ $j['id'] ?? '' }}" />
                                <input type="hidden" name="jenisusm[{{ $row->ID ?? '' }}][]" value="{{ $j['jenis'] ?? '' }}" />
                                @if($j['jenis'] == 'online')
                                    @php
                                    $nilai = "";
                                    foreach($data_hasilonline[$j['id']] ?? [] as $idh => $h) {
                                        if($h->idpendaftar == $row->ID) {
                                            $nilai = $h->nilai;
                                            break;
                                        }
                                    }
                                    @endphp
                                    <input class="form-control ace" type="number" name="nilai[{{ $row->ID ?? '' }}][]" value="{{ $nilai }}" style="width:80px;" readonly />
                                @else
                                    @php
                                    $nilai = "";
                                    foreach($data_hasil[$j['id']] ?? [] as $idh => $h) {
                                        if($h->idpendaftar == $row->ID) {
                                            $nilai = $h->nilai;
                                            break;
                                        }
                                    }
                                    @endphp
                                    <input type="number" name="nilai[{{ $row->ID ?? '' }}][]" value="{{ $nilai }}" style="width:80px;" class="ace form-control" />
                                @endif
                            </td>
                        @endforeach

                        <td class="text-center">
                            <input type="text" name="nilaiakhir[]" value="{{ $row->nilai_pmb ?? '' }}" class="form-control" style="width:80px;" readonly />
                        </td>

                        <td class="text-center">
                            {!! $row->statuslulus_str ?? '' !!}<br>
                        </td>
                        <!-- <td class="text-center">
                            {!! $row->statusluluskesehatan_str ?? '' !!}<br>
                        </td> -->
                        <td class="text-center align-middle">
                            <button class="btn btn-danger" type="button" onclick="window.open('{{ url('nilai_usm_pmb/print_skl/' . ($row->ID ?? '')) }}');">Cetak</button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div id="hapus" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="hapus" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="hapus">{{ __('app.confirm_header') }}</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    </div>
                    <div class="modal-body">
                        <p>{{ __('app.confirm_message') }}</p>
                        <p class="data_name"></p>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-danger waves-effect">{{ __('app.delete') }}</button>
                        <button type="button" class="btn btn-primary waves-effect waves-light" data-dismiss="modal">{{ __('app.close') }}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">{!! $link ?? '' !!}</div>
    </div>
</form>

<script>
// Call tablesorter if function exists
if(typeof tablesorter === 'function') {
    tablesorter();
}

$(document).ready(function() {
    show_btnSubmit();

    $("input:checkbox[name='checkID[]']").click(function(){
        if(this.checked == true){ $(this).parents('tr').addClass('table-danger'); }
        else { $(this).parents('tr').removeClass('table-danger'); }
        show_btnSubmit();
    });
});

function show_btnSubmit() {
    i = 0;
    hasil = false;
    while (document.getElementsByName('checkID[]').length > i) {
        var checkname = document.getElementById('checkID' + i);
        
        if (checkname && checkname.checked) {
            hasil = true;
        }
        i++;
    }
    var action_do = $('#action_do').val();
    if (hasil == true && action_do != '') {
        $('#btnSubmit').removeAttr('disabled');
        $('#btnSubmit').removeAttr('title');
    } else {
        $('#btnSubmit').attr('disabled', 'disabled');
        $('#btnSubmit').attr('title', 'Pilih dahulu data yang akan di simpan');
    }
}

function checkall(chkAll,checkid) {
    if (checkid != null) {
        if (checkid.length == null) checkid.checked = chkAll.checked;
        else for (i=0;i<checkid.length;i++) checkid[i].checked = chkAll.checked;
        $("input:checkbox[name='checkID[]']").parents('tr').removeClass('table-danger');
        $("input:checkbox[name='checkID[]']:checked").parents('tr').addClass('table-danger');
    }
}

// disable mousewheel on a input number field when in focus
$('input[type=number]').on('wheel', function(e) {
    return false;
});
</script>
