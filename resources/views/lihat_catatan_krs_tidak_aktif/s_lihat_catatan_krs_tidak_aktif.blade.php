<p>{{ $total_row ?? '' }}</p>
<form id="f_lihat_catatan_krs_tidak_aktif">
<div class="row">
    <div class="table-responsive">
        <table width="100%" class="table table-hover table-bordered">
            <thead class="bg-primary text-white">
                <tr>
                    <th class="sorterfalse" width="2%">
                        <div class="checkbox checkbox-info">
                            <input type="checkbox" name="checkAll" id="checkAll" onClick="checkall(this,document.getElementsByName('checkID[]')); show_btnApprove();" />
                            <label for="checkAll"></label>
                        </div>
                    </th>
                    <th class="text-center" width="2%">No.</th>
                    <th class="text-center" width="10%">Mahasiswa</th>
                    <th class="text-center" width="10%">Catatan</th>
                    <th class="text-center" width="10%">Tanggal Buat</th>
                    <th class="text-center" width="10%">Tanggal Reminder</th>
                    <th class="text-center" width="10%">Tanggal Akan Bayar</th>
                    <th class="text-center" width="10%">Sisa Tagihan</th>
                    <th class="text-center" width="10%">Catatan<br>Admin</th>
                    <th class="text-center" width="10%">Status</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $no = $offset ?? 0;
                    $i = 0;
                @endphp
                @foreach ($query ?? [] as $row)
                    <tr class="lihat_catatan_krs_tidak_aktif_{{ $row->ID }}">
                        <td class="align-middle">
                            <div class="checkbox checkbox-info">
                                <input type="checkbox" name="checkID[]" id="checkID{{ $i }}" onclick="show_btnApprove()" value="{{ $row->ID }}">
                                <label for="checkID{{ $i++ }}"></label>
                            </div>
                        </td>
                        <td class="text-center align-middle">{{ ++$no }}</td>
                        <td class="align-middle">
                            <b>{{ $row->NPM }}</b><br>
                            {{ $row->Nama }}
                        </td>
                        <td class="align-middle">{{ $row->Catatan }}</td>
                        <td class="text-center align-middle">{{ \Carbon\Carbon::parse($row->TanggalBuat)->format('d/m/Y') }}</td>
                        <td class="text-center align-middle">{{ \Carbon\Carbon::parse($row->TanggalReminder)->format('d/m/Y') }}</td>
                        <td class="text-center align-middle">{{ \Carbon\Carbon::parse($row->TanggalAkanBayar)->format('d/m/Y') }}</td>
                        <td class="text-right align-middle">{{ number_format(intval(($row->TotalJumlah ?? 0) - ($row->TotalBayar ?? 0)), 2, ',', '.') }}</td>
                        <td class="align-middle">{{ $row->CatatanAdmin }}</td>
                        <td class="text-center align-middle">
                            @if($row->SetKRSYa == 1)
                                <label class="badge badge-success">Sudah Bisa KRS</label>
                            @else
                                <label class="badge badge-secondary">Tidak Bisa KRS</label>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        {!! $link ?? '' !!}
    </div>
    <div class="clearfix"></div>
    <div class="separator bottom"></div>
</div>
</form>
<script>
show_btnApprove();
</script>
