<p>{!! $total_row !!}</p>

<form id="f_delete_deposit_mahasiswa" action="{{ route('deposit_mahasiswa.delete') }}" method="POST">
<div class="row">
    <div class="table-responsive">
        <table width="100%" class="table table-hover table-bordered tablesorter">
            <thead class="bg-primary text-white">
                <tr>
                    <th class="text-center" width="2%" rowspan="2">No.</th>
                    <th rowspan="2" style="width:15%">NPM</th>
                    <th rowspan="2" style="width:25%">Nama Mahasiswa</th>
                    <th rowspan="2" style="width:25%">Saldo Saat Ini</th>
                    <th class="text-center" colspan="2">Update Terakhir</th>
                    <th rowspan="2" class="text-center">Action</th>
                </tr>
                <tr>
                    <th>Tanggal</th>
                    <th>Jam</th>
                </tr>
            </thead>
            <tbody>
                @php $no = $offset; @endphp
                @foreach($query as $row)
                    <tr class="deposit_mahasiswa_{{ $row->ID }}">
                        <td class="text-center">{{ ++$no }}.</td>
                        <td>{{ $row->NPM }}</td>
                        <td>{{ $row->Nama }}</td>
                        <td class="text-right">Rp. {{ number_format($row->Deposit, 2, ',', '.') }}</td>
                        <td>{{ \Carbon\Carbon::parse($row->Tanggal)->format('d/m/Y') }}</td>
                        <td>{{ $row->jam }}</td>
                        <td>
                            <div class="btn-group">
                                <button type="button" class="btn btn-primary dropdown-toggle" data-toggle="dropdown">
                                    Action <span class="mdi mdi-chevron-down"></span>
                                </button>
                                <div class="dropdown-menu" role="menu">
                                    @if($Update == 'YA')
                                        <a class="dropdown-item" href="{{ route('deposit_mahasiswa.view', $row->ID) }}"><i class="mdi mdi-pencil"></i> &nbsp; Ubah Deposit</a>
                                        <a class="dropdown-item" href="{{ route('deposit_mahasiswa.historyDeposit', $row->ID) }}"><i class="fa fa-history"></i> &nbsp; Detail History Deposit</a>
                                    @endif
                                </div>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        {!! $link !!}
    </div>
</div>
</form>

<script>
// Handle form submit with AJAX
$(document).on('submit', '#f_delete_deposit_mahasiswa', function(e){
    e.preventDefault();
    e.stopImmediatePropagation();

    $.ajax({
        type: "POST",
        url: $(this).attr('action'),
        data: $(this).serialize(),
        success: function(data){
            if(data.status == 1) {
                alertsuccess(data.message);
                setTimeout(function() { filter(); }, 500);
            } else {
                swal('Pemberitahuan', data.message, 'error');
            }
        },
        error: function(){
            swal('Pemberitahuan', 'Maaf, data gagal diproses!.', 'error');
        }
    });
    return false;
});
</script>
