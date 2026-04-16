@extends('layouts.template1')

@section('content')
<div class="card">
    <div class="card-body">
        <h4>History Deposit Mahasiswa</h4>
        <hr>
        <div class="row">
            <div class="col-md-6">
                <p><strong>NIM:</strong> {{ $mahasiswa->NPM ?? '-' }}</p>
                <p><strong>Nama:</strong> {{ $mahasiswa->Nama ?? '-' }}</p>
            </div>
            <div class="col-md-6">
                <p><strong>Saldo Saat Ini:</strong> Rp. {{ number_format($row->Deposit ?? 0, 2, ',', '.') }}</p>
            </div>
        </div>

        <hr>
        <h5>Riwayat Perubahan Deposit</h5>
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="bg-primary text-white">
                    <tr>
                        <th>No.</th>
                        <th>Tanggal</th>
                        <th>Status</th>
                        <th>Jumlah Perubahan</th>
                        <th>Jenis Biaya</th>
                        <th>User</th>
                    </tr>
                </thead>
                <tbody>
                    @php $no = 1; @endphp
                    @foreach($detail_history as $history)
                        <tr>
                            <td>{{ $no++ }}</td>
                            <td>{{ \Carbon\Carbon::parse($history->CreatedAt)->format('d/m/Y H:i') }}</td>
                            <td>
                                @if($history->Status == 1)
                                    <span class="badge badge-success">Penambahan</span>
                                @elseif($history->Status == 0)
                                    <span class="badge badge-danger">Pengurangan</span>
                                @else
                                    <span class="badge badge-secondary">-</span>
                                @endif
                            </td>
                            <td class="text-right">Rp. {{ number_format($history->Deposit ?? 0, 2, ',', '.') }}</td>
                            <td>{{ $history->NamaTagihan ?? '-' }}</td>
                            <td>{{ DB::table('user')->where('ID', $history->UserID)->value('NamaUser') ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <button type="button" onClick="history.back()" class="btn btn-bordered-danger waves-effect width-md waves-light">Kembali</button>
    </div>
</div>
@endsection
