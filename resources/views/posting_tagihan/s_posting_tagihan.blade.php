<input type="hidden" id="valTahunID" value="{{ $tahunID }}">
<p>{!! $total_row !!}</p>
<form id="f_posting_tagihan" action="{{ route('posting_tagihan.index') }}" >
    <div class="table-responsive">
        <table class="table table-bordered mb-0 table-hover tablesorter">
            <thead class="bg-primary text-white">
                <tr>
                    @if($Update == 'YA')
                        <th width="2%">
                            <div class="checkbox checkbox-info">
                                <input type="checkbox" name="checkAll" id="checkAll" onClick="checkall(this,document.forms.namedItem('f_posting_tagihan')); show_btnDelete();">
                                <label for="checkAll"></label>
                            </div>
                        </th>
                    @endif
                    <th rowspan="1" class="text-center" style="width:2%">No.</th>
                    <th rowspan="1" style="width: 5%;" class="text-center">Nama Mahasiswa</th>
                    <th rowspan="1" style="width: 3%;" class="text-center">Tahun<br>Semester</th>
                    <th rowspan="1" style="width: 3%;" class="text-center">Program</th>
                    <th rowspan="1" style="width: 5%;" class="text-center">Prodi</th>
                    <th rowspan="1" style="width: 9%;" class="text-center">Jumlah Tagihan</th>
                    <th rowspan="1" style="width: 3%;" class="text-center">Status Draft</th>
                    <th rowspan="1" style="width: 3%;" class="text-center">Status Posting</th>
                    <th rowspan="1" style="width: 3%;" class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $nomor = $offset;
                    $i = 0;
                @endphp
                @if(count($query) > 0)
                    @foreach ($query as $value)
                        <tr>
                            @if($Update == 'YA')
                                <td style="width:2%">
                                    @if($value->StatusPosting != 1)
                                        <div class="checkbox checkbox-info">
                                            <input type="checkbox" name="checkID[]" id="checkID{{ $i }}" onclick="show_btnDelete()" value="{{ $value->ID }}">
                                            <label for="checkID{{ $i }}"></label>
                                        </div>
                                        @php $i++; @endphp
                                    @else
                                        -
                                    @endif
                                </td>
                            @endif

                            <td style="width:2%" class="text-center">{{ ++$nomor }}.</td>
                            <td style="width:10%">
                                <b>{{ $value->npm }}</b><br>
                                {{ $value->namaMahasiswa }}
                            </td>
                            <td style="width:3%">
                                <span class='badge badge-secondary'>{{ $value->KodeTahun }}</span>
                            </td>
                            <td style="width:3%" class="text-center">
                                <span class='badge badge-info'>{{ $value->namaProgram }}</span>
                            </td>
                            <td style="width:3%" class="text-center">
                                <span class='badge badge-warning'>{{ $value->namaProdi }}</span>
                            </td>

                            <td style="width:9%" class="text-center">
                                <a href="javascript:void(0);" class="badge badge-light text-dark font-16">
                                    {{ number_format($value->jumlahBiaya, 0, ',', '.') }}
                                </a>
                            </td>

                            <td class="text-center" style="width:4%">
                                @if($value->statusDraft == 1)
                                    <span class="badge badge-success">Sudah</span>
                                @else
                                    <span class="badge badge-secondary">Belum</span>
                                @endif
                            </td>

                            <td class="text-center" style="width:4%">
                                @if($value->StatusPosting == 1)
                                    <span class="badge badge-success">Sudah</span>
                                @else
                                    <span class="badge badge-secondary">Belum</span>
                                @endif
                            </td>
                            <td style="width:10%" class="text-center">
                                @if($value->StatusPosting != 1)
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-info dropdown-toggle waves-effect waves-light btn-sm" data-toggle="dropdown" aria-expanded="false">
                                            Action <i class="mdi mdi-chevron-down"></i>
                                        </button>
                                        <div class="dropdown-menu">
                                            <a onclick="posting('{{ $value->ID }}','{{ $value->Periode }}', 1);" href="javascript:void(0);" class="dropdown-item">
                                                <i class="mdi mdi-publish"></i>&nbsp;Posting
                                            </a>
                                            <a onclick="posting('{{ $value->ID }}','{{ $value->Periode }}', 0);" href="javascript:void(0);" class="dropdown-item">
                                                <i class="mdi mdi-trash-can"></i>&nbsp;Hapus
                                            </a>
                                        </div>
                                    </div>
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="10" class="text-center">Tidak ada data Sesuai Filter diatas</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
</form>
<div class="row">
    <div class="col-md-12">
        {!! $link !!}
    </div>
</div>

<script type="text/javascript">
    function show_btnDelete(){
        i = 0;
        hasil = false;
        while(document.getElementsByName('checkID[]').length > i) {
            var checkname = document.getElementById('checkID' + i).checked;

            if(checkname == true) {
                hasil = true;
            }
            i++;
        }
        if(hasil == true) {
            $('#btn_posting_all').removeAttr('disabled');
            $('#btn_posting_all').removeAttr('title');
            $('#btn_create_all_draft').removeClass('disabled');
            $('#btn_delete_all_draft').removeClass('disabled');
        } else {
            $('#btn_create_all_draft').addClass('disabled');
            $('#btn_delete_all_draft').addClass('disabled');
            $('#btn_posting_all').attr('disabled', 'disabled');
            $('#btn_posting_all').attr('title', 'Pilih dahulu data yang akan di setujui semua');
        }
    }
    show_btnDelete();

    $("input:checkbox[name='checkID[]']").click(function(){
        if(this.checked == true){
            $(this).parents('tr').addClass('table-danger');
        } else {
            $(this).parents('tr').removeClass('table-danger');
        }
    });
</script>
