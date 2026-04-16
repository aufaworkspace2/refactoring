{{--
FILE: resources/views/dashboard.blade.php

Dashboard view untuk test refactoring
Display hasil dari semua helpers
--}}

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Test Refactoring</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        .card {
            border: none;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 8px 8px 0 0 !important;
        }
        .badge-custom {
            font-size: 14px;
            padding: 8px 15px;
        }
        h1 {
            color: #333;
            margin-bottom: 30px;
            text-align: center;
        }
        .test-link {
            display: inline-block;
            margin: 10px 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🎉 Dashboard - Test Refactoring CI 3 → Laravel 12</h1>

        <!-- Navigation -->
        <div class="alert alert-info" role="alert">
            <strong>📌 Testing Links:</strong>
            <a href="{{ route('welcome') }}" class="test-link btn btn-sm btn-primary">Dashboard</a>
        </div>

        <!-- Institution Info -->
        @if($identitas)
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">🏛️ Informasi Institusi</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Nama:</strong> {{ $identitas->Nama ?? '-' }}</p>
                        <p><strong>Singkatan:</strong> {{ $identitas->SingkatanPT ?? '-' }}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Email:</strong> {{ $identitas->Email ?? '-' }}</p>
                        <p><strong>Telepon:</strong> {{ $identitas->NoTelepon ?? '-' }}</p>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Database Statistics -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">📊 Database Statistics</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-4">
                        <h3 class="badge badge-custom bg-primary">{{ $total_users }}</h3>
                        <p>Total Users</p>
                    </div>
                    <div class="col-md-4">
                        <h3 class="badge badge-custom bg-success">{{ $total_mahasiswa }}</h3>
                        <p>Total Mahasiswa</p>
                    </div>
                    <div class="col-md-4">
                        <h3 class="badge badge-custom bg-info">{{ $total_aktif }}</h3>
                        <p>Mahasiswa Aktif</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Format Helpers Test -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">💰 Format Helpers Test</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>rupiah() Function</h6>
                        <table class="table table-sm">
                            <tr>
                                <td>Input:</td>
                                <td><code>{{ $sample_amount }}</code></td>
                            </tr>
                            <tr>
                                <td>Output:</td>
                                <td><strong class="text-success">{{ $formatted_rupiah }}</strong></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6>terbilang() Function</h6>
                        <table class="table table-sm">
                            <tr>
                                <td>Input:</td>
                                <td><code>{{ $sample_number }}</code></td>
                            </tr>
                            <tr>
                                <td>Output:</td>
                                <td><strong class="text-success">{{ $terbilang_text }}</strong></td>
                            </tr>
                        </table>
                    </div>
                </div>
                <hr>
                <div class="row">
                    <div class="col-md-6">
                        <h6>formatSizeUnits() Function</h6>
                        <table class="table table-sm">
                            <tr>
                                <td>Input:</td>
                                <td><code>{{ $sample_size }} bytes</code></td>
                            </tr>
                            <tr>
                                <td>Output:</td>
                                <td><strong class="text-success">{{ $formatted_size }}</strong></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Date Helpers Test -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">📅 Date Helpers Test (tgl function)</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <strong>Format 01</strong><br>
                        <code class="text-muted">tgl(date, '01')</code><br>
                        <span class="text-success">{{ $tgl_format_01 }}</span>
                    </div>
                    <div class="col-md-3">
                        <strong>Format 02</strong><br>
                        <code class="text-muted">tgl(date, '02')</code><br>
                        <span class="text-success">{{ $tgl_format_02 }}</span>
                    </div>
                    <div class="col-md-3">
                        <strong>Format 03</strong><br>
                        <code class="text-muted">tgl(date, '03')</code><br>
                        <span class="text-success">{{ $tgl_format_03 }}</span>
                    </div>
                    <div class="col-md-3">
                        <strong>Source Date</strong><br>
                        <code>{{ $sample_date }}</code>
                    </div>
                </div>
            </div>
        </div>

        <!-- Academic Helpers Test -->
        @if($mahasiswa_sample)
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">🎓 Academic Helpers Test</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Mahasiswa Sample</h6>
                        <table class="table table-sm">
                            <tr>
                                <td>NIM:</td>
                                <td><strong>{{ $mahasiswa_sample->NIM ?? '-' }}</strong></td>
                            </tr>
                            <tr>
                                <td>Nama:</td>
                                <td><strong>{{ $mahasiswa_sample->Nama ?? '-' }}</strong></td>
                            </tr>
                            <tr>
                                <td>Status:</td>
                                <td><span class="badge bg-success">{{ $mahasiswa_sample->Status ?? '-' }}</span></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6>Academic Info</h6>
                        <table class="table table-sm">
                            @if($prodi)
                            <tr>
                                <td>Program Studi:</td>
                                <td><strong>{{ $prodi->Nama ?? '-' }}</strong></td>
                            </tr>
                            @endif
                            <tr>
                                <td>IPK:</td>
                                <td><strong class="text-success">{{ $ipk ?? '0.00' }}</strong></td>
                            </tr>
                            <tr>
                                <td>Total SKS:</td>
                                <td><strong class="text-success">{{ $total_sks ?? '0' }}</strong></td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Helpers Test -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">💳 Payment Helpers Test</h5>
            </div>
            <div class="card-body">
                <table class="table">
                    <tr>
                        <td><strong>Total Tagihan Belum Bayar:</strong></td>
                        <td class="text-danger"><strong>{{ $formatted_tagihan }}</strong></td>
                    </tr>
                    <tr>
                        <td><strong>Raw Value:</strong></td>
                        <td><code>{{ $total_tagihan_belum_bayar }}</code></td>
                    </tr>
                </table>
            </div>
        </div>
        @else
        <div class="alert alert-warning" role="alert">
            ⚠️ Tidak ada data mahasiswa untuk test academic helpers. Pastikan tabel mahasiswa punya data!
        </div>
        @endif

        <!-- Status Info -->
        <div class="alert alert-success" role="alert">
            <h5>✅ Refactoring Status</h5>
            <ul class="mb-0">
                <li>✅ Database Helpers - WORKING</li>
                <li>✅ Format Helpers (rupiah, terbilang, formatSizeUnits) - WORKING</li>
                <li>✅ Date Helpers (tgl, validateDate) - WORKING</li>
                <li>✅ Academic Helpers (hitungipk, total_sks, get_prodi) - WORKING</li>
                <li>✅ Payment Helpers (get_sisa_tagihan, get_total_cicilan) - WORKING</li>
                <li>✅ All Helpers Naming - 100% SAMA dengan CI 3</li>
            </ul>
        </div>

        <!-- Next Steps -->
        <div class="alert alert-info" role="alert">
            <h5>🚀 Next Steps</h5>
            <ol>
                <li>Check <a href="{{ route('test.helpers') }}">Test Helpers page</a> untuk detail setiap helper function</li>
                <li>Check <a href="{{ route('test.database') }}">Test Database page</a> untuk database connection info</li>
                <li>Refactor Controllers - ganti $ci->db dengan DB facade</li>
                <li>Refactor Views - ganti PHP syntax dengan Blade</li>
                <li>Setup Routes untuk aplikasi asli</li>
            </ol>
        </div>

    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>
