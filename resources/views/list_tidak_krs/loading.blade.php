@extends('layouts.template1')

@section('content')
<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col-md-12 text-center">
                <div class="alert alert-warning">
                    <h4><i class="fa fa-spin fa-spinner"></i> Proses Update Data Sedang Berjalan...</h4>
                    <p>Silakan tunggu hingga proses update data mahasiswa tidak KRS selesai.</p>
                    <p><small>Anda dapat me-refresh halaman ini untuk melihat status terbaru.</small></p>
                </div>
                <button onclick="location.reload()" class="btn btn-primary">
                    <i class="fa fa-refresh"></i> Refresh
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
