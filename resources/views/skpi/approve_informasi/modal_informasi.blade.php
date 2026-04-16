@if($mahasiswa)
    <h5>Data Mahasiswa: {{ $mahasiswa->NPM ?? '' }} - {{ $mahasiswa->Nama ?? '' }}</h5>
    <hr>
@endif

@if(count($informasiList ?? []) > 0)
    <table class="table table-bordered">
        <thead>
            <tr>
                <th width="5%">No.</th>
                <th width="15%">Kode</th>
                <th width="35%">Indonesia</th>
                <th width="35%">Inggris</th>
                <th width="10%">Approve</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @foreach($informasiList as $info)
                <tr>
                    <td>{{ $no++ }}</td>
                    <td>{{ $info['Kode'] ?? '' }}</td>
                    <td>{{ $info['Indonesia'] ?? '' }}</td>
                    <td>{{ $info['Inggris'] ?? '' }}</td>
                    <td class="text-center">
                        @if($info['approve'] == '1')
                            <span class="badge badge-success">Approved</span>
                        @else
                            <form style="display:inline;" onsubmit="approveInformasi(event, {{ $info['ID'] }})">
                                <button type="submit" class="btn btn-sm btn-success">Approve</button>
                            </form>
                            <form style="display:inline;" onsubmit="rejectInformasi(event, {{ $info['ID'] }})">
                                <button type="submit" class="btn btn-sm btn-danger">Reject</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
@else
    <p class="text-center">Belum ada informasi tambahan yang diinput.</p>
@endif

<script>
function approveInformasi(event, id) {
    event.preventDefault();
    $.ajax({
        url: "{{ url('skpi/approveInformasi/approveInformasi') }}",
        type: "POST",
        data: {
            id: id,
            _token: "{{ csrf_token() }}"
        },
        success: function(data) {
            if(data.status === 'success') {
                alert('Informasi berhasil di-approve');
                $('#modal_informasi').modal('hide');
                filter(); // Refresh table
            }
        }
    });
}

function rejectInformasi(event, id) {
    event.preventDefault();
    if(!confirm('Apakah Anda yakin ingin menolak informasi ini?')) {
        return;
    }
    $.ajax({
        url: "{{ url('skpi/approveInformasi/rejectInformasi') }}",
        type: "POST",
        data: {
            id: id,
            _token: "{{ csrf_token() }}"
        },
        success: function(data) {
            if(data.status === 'success') {
                alert('Informasi berhasil ditolak');
                $('#modal_informasi').modal('hide');
                filter(); // Refresh table
            }
        }
    });
}
</script>
