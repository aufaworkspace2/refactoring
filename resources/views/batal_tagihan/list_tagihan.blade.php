<p>{!! $total_row !!}</p>

<form id="f_delete_mahasiswa" action="{{ route('batal_tagihan.delete') }}" method="POST">
<div class="row">
    <div class="table-responsive">
        <button type="button" class="btn btn-danger float-right" id="btnDelete" data-placement="top" title="Silahkan pilih data terlebih dahulu." data-toggle="modal" disabled><i class="mdi mdi-trash-can"></i> Hapus</button>

        <table width="100%" class="table table-hover table-bordered table-responsive block tablesorter">
            <thead class="bg-primary text-white">
                <tr>
                    <th class="sorterfalse" width="3%">
                        <div class="checkbox checkbox-info">
                            <input type="checkbox" name="checkAll" id="checkAll" onClick="checkall(this,document.getElementsByName('checkID[]')); show_btnDelete();">
                            <label for="checkAll"></label>
                        </div>
                    </th>
                    <th class="center" width="2%">No.</th>
                    <th class="center" width="10%">NPM</th>
                    <th class="center" width="30%">Nama</th>
                    <th class="center" width="9%">Tahun Masuk</th>
                    <th class="center" width="20%">Jenis Biaya</th>
                    <th class="center" width="9%">Jumlah</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $no = $offset;
                    $i = 0;
                    $total_tagihan = 0;
                @endphp
                @foreach($query as $data)
                    <tr class="mahasiswa_{{ $data->ID }}">
                        <td class="align-middle">
                            <div class="checkbox checkbox-info">
                                <input type="checkbox" name="checkID[]" id="checkID{{ $i }}" onclick="show_btnDelete()" value="{{ $data->ID }}">
                                <label for="checkID{{ $i++ }}"></label>
                            </div>
                        </td>
                        <td class="center">{{ ++$no }}.</td>
                        <td class="center">{{ $data->NPM }}</td>
                        <td>{{ $data->Nama }}</td>
                        <td class="center"><span class="badge badge-secondary">{{ $data->TahunMasuk }}</span></td>
                        <td>{{ $data->JenisBiaya }}</td>
                        <td style="text-align:right">{{ number_format($data->Jumlah, 0, ',', '.') }}</td>
                    </tr>
                    @php $total_tagihan += $data->Jumlah; @endphp
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="6" style="text-align: right;">Total Tagihan Di Page {{ $offset + 1 }}</td>
                    <td style="text-align: right;font-weight: bold;">{{ number_format($total_tagihan, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        {!! $link !!}
    </div>
    <div class="clearfix"></div>
    <div class="separator bottom"></div>
</div>

<div class="modal" id="hapus">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4>Konfirmasi Hapus</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            </div>
            <div class="modal-body">
                Apakah Anda yakin ingin menghapus data tagihan yang dipilih?
                <p class="data_name"></p>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary"><i class="mdi mdi-trash"></i> Hapus</button>
                <a href="javascript:void(0);" class="btn btn-danger" data-dismiss="modal"><i class="mdi mdi-close"></i> Tutup</a>
            </div>
        </div>
    </div>
</div>
</form>
