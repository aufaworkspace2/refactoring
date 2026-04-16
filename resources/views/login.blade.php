<!DOCTYPE html>
@php
    $identitas = null;
    $background = null;
    $masterDb = env('DB_MASTER_AIS_NAME');
    
    try {
        $identitas = DB::table('identitas')->where('ID', 1)->first();
        // setup_app ada di database master
        $background = DB::table($masterDb . '.setup_app')->where('tipe_setup', 'setup_custom_background')->first();
    } catch (\Exception $e) {
        // Fallback jika database/table belum tersedia
    }

    if ($background && isset($background->metadata)) {
        $imageBg = json_decode($background->metadata)->ais ?? '';
    }
@endphp

<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="author" content="{{ $identitas->SingkatanPT ?? 'EduCampus' }}">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sistem Informasi Akademik - {{ $identitas->SingkatanPT ?? 'EduCampus' }}</title>
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    
    <!-- Bootstrap CSS -->
    <link href="{{ asset('assets/template1/assets/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/template1/assets/css/custom.css') }}" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&amp;display=swap" rel="stylesheet">
    
    @if(!empty($background) && !empty($imageBg))
    <style>
        body {
            background-image: url('{{ asset('assets/' . $imageBg) }}');
            background-size: cover;
            background-position: center;
        }
        .f-login {
            margin-top: 350px;
        }
        @media (max-width: 720px) {
            .image-hp { display: inline; }
            .f-login { margin-top: 400px; }
        }
    </style>
    @endif
</head>

<body class="my-login-page authentication-bg content-login">
    <div class="account-pages pt-sm-3">
        <div class="container">
            <div class="row justify-content-md-center f-login">
                <div class="col-md-5">
                    <div class="card">
                        <div class="card-body">
                            <div class="text-center">
                                <img class="mb-2 image-hp" src="{{ asset('images/' . ($identitas->Gambar ?? 'logo.png')) }}" width="70" onerror="this.style.display='none'">
                                <h4 class="mt-0" style="font-size: 1.35rem; font-weight: 500;">Login</h4>
                                <p style="font-size: 13px; margin-bottom: -30px;">Masuk dengan akun anda</p>
                            </div>
                            <div class="p-2 mt-4">
                                @if(session('error'))
                                    <div class="alert alert-danger text-left">{{ session('error') }}</div>
                                @endif
                                <form action="{{ route('welcome.login') }}" method="post">
                                    @csrf
                                    <input type="hidden" name="hash" id="id_hash" />
                                    <input type="hidden" name="token" value="{{ $token }}" />
                                    
                                    <div class="mb-3">
                                        <label class="form-label text-muted" for="username">Username</label>
                                        <input type="text" class="form-control" id="username" name="username" placeholder="Enter username" required style="border-radius: 8px;">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label text-muted" for="userpassword">Password</label>
                                        <input type="password" class="form-control" id="userpassword" name="password" placeholder="Enter password" required style="border-radius: 8px;">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <div class="text-center">
                                            <img src="{{ $imageCaptcha }}" style="max-width: 100%;" alt="Captcha" onerror="this.style.display='none'; this.nextElementSibling.style.display='block';" />
                                            <span style="display:none; color: red; font-size: 12px;">Captcha tidak tersedia. Silakan hubungi admin.</span>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <input id="captcha" type="text" class="form-control" name="captcha" value="" placeholder="Masukan kode diatas" required autofocus autocomplete="off" style="border-radius: 8px;">
                                    </div>
                                    
                                    <div class="mt-3 text-right">
                                        <button class="btn btn-primary w-sm waves-effect waves-light" type="submit" style="border-radius: 8px; background: #28a382; border-color: #28a382;">
                                            <b>Log In</b>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 text-center">
                        <p>Copyright &copy; {{ date('Y') }} &mdash; {{ $identitas->SingkatanPT ?? 'EduCampus' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- jQuery (CDN) -->
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>

    <script>
        var hash = window.location.hash.substr(1);
        $('#id_hash').val(hash);
    </script>
</body>
</html>
