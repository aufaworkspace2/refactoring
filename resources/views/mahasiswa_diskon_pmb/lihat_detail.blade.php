@extends('layouts.template1')

@section('content')
<div class="card">
    <div class="card-header">
        <h4 class="card-title">Detail Diskon Mahasiswa</h4>
        <a href="{{ url('mahasiswa_diskon_pmb') }}" class="btn btn-sm btn-primary float-right">
            <i class="fa fa-arrow-left"></i> Kembali
        </a>
    </div>
    <div class="card-body">
        @if($row)
            <table class="table table-bordered">
                <tr>
                    <th width="30%">Mahasiswa</th>
                    <td>{{ $row->Nama ?? '-' }}</td>
                </tr>
                <tr>
                    <th>No. Ujian</th>
                    <td>{{ $row->noujian_pmb ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Jenis Biaya</th>
                    <td>{{ $row->JenisBiayaNama ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Master Diskon</th>
                    <td>{{ $row->MasterDiskonNama ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Nominal (Rp)</th>
                    <td class="text-right">{{ number_format($row->Nominal ?? 0, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <th>Periode</th>
                    <td>{{ $row->Periode ?? '-' }}</td>
                </tr>
                <tr>
                    <th>Status</th>
                    <td>
                        @if($row->StatusAktif == 1)
                            <span class="badge badge-success">Aktif</span>
                        @else
                            <span class="badge badge-secondary">Tidak Aktif</span>
                        @endif
                    </td>
                </tr>
            </table>
        @else
            <div class="alert alert-info">
                <i class="fa fa-info-circle"></i> Data diskon tidak ditemukan.
            </div>
        @endif
    </div>
</div>
@endsection
