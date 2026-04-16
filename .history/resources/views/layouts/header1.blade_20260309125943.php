<!-- Topbar Start -->
<div class="top-bar-alert" id="alert-after-akademik" style="display: none;">
    <p class="mb-0 text-alert">
        <i class="fa fa-exclamation-triangle mr-2"></i>
        Selanjutnya kamu harus setting jadwal perkuliahan, klik
        <a href="#dashboard" onclick="modul('2')" class="text-warning" id="klikdisini-akademik">disini</a>
        <a href="javascript:void(0);" class="btn-close-alert" onclick="closeAlertTop('akademik')">
            <i class="fa fa-times"></i>
        </a>
    </p>
</div>

<div class="navbar-custom bg-white">

    <ul class="list-unstyled topnav-menu float-right mb-0 d-flex">

        <li class="dropdown notification-list">

            <a class="nav-link dropdown-toggle nav-user mr-0 waves-effect"
                data-toggle="dropdown"
                href="#"
                role="button">

                <img src="{{ asset('assets/template1/assets/images/bell2.gif') }}" width="30">
                <span class="text-header-button">What's New ?</span>

            </a>

            <div class="dropdown-menu dropdown-menu-end dropdown-lg">

                <div class="dropdown-item noti-title">
                    <h5 class="m-0">
                        Yang terbaru dari Edufecta
                    </h5>
                </div>

                <div class="noti-scroll" style="overflow-y:auto;max-height:250px">

                    @php

                    if(session('LevelUser')){
                    $check_akses_modul_494 =
                    DB::table('levelmodul')
                    ->where('ModulID',494)
                    ->where('Read','YA')
                    ->whereIn('LevelID', explode(',',session('LevelUser')))
                    ->count();
                    }else{
                    $check_akses_modul_494 = null;
                    }

                    @endphp


                    @if($check_akses_modul_494 > 0)

                    <a href="#"
                        onclick="clickTourRedaksi('87')"
                        class="dropdown-item notify-item">

                        <div class="notify-icon bg-success">
                            <i class="mdi mdi-newspaper"></i>
                        </div>

                        <p class="notify-details">Setting Redaksi PMB</p>

                        <p class="text-muted mb-0 user-msg">
                            <small>Sekarang anda dapat merubah beberapa redaksi yang ada di portal PMB</small>
                        </p>

                    </a>

                    @endif


                    <a href="javascript:void(0);"
                        onclick="clickTourAlur()"
                        class="dropdown-item notify-item">

                        <div class="notify-icon bg-warning">
                            <i class="mdi mdi-newspaper"></i>
                        </div>

                        <p class="notify-details">Diagram Alur Sistem Edufecta</p>

                        <p class="text-muted mb-0 user-msg">
                            <small>Tidak perlu bingung dengan cara menggunakan sistem Edufecta.</small>
                        </p>

                    </a>


                    <a href="javascript:void(0);"
                        onclick="clickTourAlur()"
                        class="dropdown-item notify-item">

                        <div class="notify-icon bg-primary">
                            <i class="mdi mdi-newspaper"></i>
                        </div>

                        <p class="notify-details">Yang Baru Dari Edufecta</p>

                        <p class="text-muted mb-0 user-msg">
                            <small>Anda akan mendapat informasi <b>update sistem</b> Edufecta disini.</small>
                        </p>

                    </a>

                </div>
            </div>
        </li>


        <li class="dropdown notification-list">

            <a href="javascript:void(0);"
                class="nav-link waves-effect waves-light"
                data-toggle="modal"
                data-target="#search-modal"
                onclick="$('.form-search-modal').removeAttr('disabled');">

                <span id="btn-alur">
                    <i class="fe-search noti-icon mr-1"></i>
                    <span class="text-header-button">Alur Sistem</span>
                </span>

            </a>

        </li>

        @php

        $arrAkses = count(array_filter([
        session("akses_crp"),
        session("akses_sdm"),
        session("akses_accounting"),
        session("akses_elearning"),
        session("akses_student"),
        session("akses_lecturer"),
        session("akses_executive")
        ]));

        @endphp


        @if($arrAkses > 0)

        <li class="dropdown notification-list">
            <a href="javascript:void(0);"
                class="nav-link right-bar-toggle waves-effect waves-light">
                <i class="fe-grid noti-icon"></i>
            </a>
        </li>

        @endif


        @php

        if(session('tipeuser') == 2 && session('EntityID') != NULL){
        $user = get_id(session('EntityID'),'dosen');
        }else{
        $user = get_id(session('EntityID'),'karyawan');
        }

        if($user){
        $sql_user = DB::select("
        SELECT c.Nama as Level
        FROM user a
        join leveluser b on a.ID=b.UserID
        join level c on b.LevelID=c.ID
        where a.EntityID=?
        ",[$user->ID]);

        $sql_user = $sql_user ? $sql_user[0] : null;
        }

        @endphp


        <li class="dropdown notification-list">

            <a class="nav-link dropdown-toggle nav-user mr-0 waves-effect"
                data-toggle="dropdown"
                href="#">

                @php

                if(session('EntityID') == 0){
                $user = new stdClass();
                $user->NIP = 0;
                $user->Foto = null;
                $user->Kelamin = null;
                }

                @endphp


                {!! get_photo($user->NIP,$user->Foto,$user->Kelamin,'karyawan','rounded-circle') !!}

            </a>


            <div class="dropdown-menu dropdown-menu-right profile-dropdown">

                <a href="#" class="dropdown-item notify-item">

                    <i class="fe-user"></i>

                    <span>

                        @php

                        if(session('EntityID') == 0){

                        echo 'Admin (Administrator)';

                        }else if(!empty($user) && !empty($sql_user)){

                        echo $user->Nama;

                        if($sql_user->Level){
                        echo " ( ".$sql_user->Level." ) ";
                        }

                        }

                        @endphp

                    </span>

                </a>


                <a href="javascript:void(0);" onclick="ubahpass()" class="dropdown-item notify-item">
                    <i class="fe-settings"></i>
                    <span>Ubah Pasword</span>
                </a>


                <div class="dropdown-divider"></div>


                <a href="{{ url('welcome/logout') }}" class="dropdown-item notify-item">
                    <i class="fe-log-out"></i>
                    <span>{{ __('keluar') }}</span>
                </a>

            </div>
        </li>

    </ul>


    <div class="logo-box">

        @php

        <!-- $identitas = get_id(1,'identitas'); -->

        if($identitas){
        $img = $identitas->Gambar;
        $img_kecil = $identitas->GambarKecil;
        }else{
        $img='no_image.jpg';
        $img_kecil='no_image.jpg';
        }

        @endphp


        @if(request('dev') == 1)

        <a href="{{ url('/') }}#dashboard"
            onclick="home(); modul(0);"
            class="brand logo logo-dark text-center">

            <span class="logo-lg">
                <img src="https://edufecta.com/medias/home/34yzj.png" height="36">
            </span>

            <span class="logo-sm">
                <img src="https://edufecta.com/medias/home/9kzcy.png" height="50">
            </span>

        </a>

        @else

        <a href="{{ url('/') }}#dashboard"
            onclick="home(); modul(0);"
            class="brand logo logo-dark text-center">

            <span class="logo-lg">
                <img src="{{ $CLIENT_HOST }}images/{{ $img }}" height="36">
            </span>

            <span class="logo-sm">
                <img src="{{ $CLIENT_HOST }}images/{{ $img_kecil }}" height="18">
            </span>

        </a>

        @endif

    </div>

</div>
