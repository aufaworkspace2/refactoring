{{--
FILE: resources/views/layouts/template1.blade.php
Refactoring dari: template1.php (CI 3)

CATATAN PENTING:
- Struktur HTML/CSS/JS 100% sama dengan CI 3
- Hanya syntax PHP diganti dengan Blade syntax
- Semua variable bisa di-pass dari controller
- Semua routes bisa di-define di routes/web.php
--}}

<!DOCTYPE html>
<html>
<head>
    @php
        // Ambil identitas dari database atau config
        $identitas = get_identitas(1);
        $img = $identitas?->Gambar ?? 'no_image.jpg';
        $clientHost = config('app.client_host');
        $assetsHost = config('app.assets_host');
    @endphp

    <title>{{ __('menu.judul') }} - {{ $identitas->SingkatanPT ?? 'Kampus' }}</title>

    {{-- Favicon --}}
    @if(request('dev') == 1)
        <link rel="shortcut icon" href="https://edufecta.com/medias/home/9kzcy.png">
    @else
        <link rel="shortcut icon" href="{{ $assetsHost }}images/{{ $identitas->GambarFavicon ?? 'favicon.ico' }}">
    @endif

    <!-- Meta Tags -->
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow" />

    <!-- Bootstrap Css -->
    <link href="{{ $assetsHost }}template1/assets/css/bootstrap.min.css" id="bootstrap-stylesheet" rel="stylesheet" type="text/css" />

    <!-- Icons Css -->
    <link href="{{ $assetsHost }}template1/assets/css/icons.min.css" rel="stylesheet" type="text/css" />

    <!-- App Css-->
    <link href="{{ $assetsHost }}template1/assets/css/app.min.css" id="app-stylesheet" rel="stylesheet" type="text/css" />
    <link href="{{ $assetsHost }}template1/assets/css/custom.css" id="app-stylesheet" rel="stylesheet" type="text/css" />

    <!-- SELECT2 -->
    <link href="{{ $assetsHost }}template1/assets/libs/select2/select2.min.css" rel="stylesheet" type="text/css" />

    <!-- DATETIMEPICKER -->
    <link href="{{ $assetsHost }}template1/assets/extra-libs/datetimepicker/css/jquery.datetimepicker.min.css" rel="stylesheet" type="text/css" />

    <!-- DATEPICKER -->
    <link href="{{ $assetsHost }}template1/assets/libs/bootstrap-datepicker/bootstrap-datepicker.css" rel="stylesheet" type="text/css" />

    <!-- DATERANGEPICKER -->
    <link href="{{ $assetsHost }}template1/assets/libs/bootstrap-daterangepicker/daterangepicker.css" rel="stylesheet" type="text/css" />

    <!-- COLORPICKER -->
    <link href="{{ $assetsHost }}template1/assets/libs/bootstrap-colorpicker/bootstrap-colorpicker.min.css" rel="stylesheet" type="text/css" />

    <!-- MULTI SELECT -->
    <link href="{{ $assetsHost }}template1/assets/libs/multiselect/multi-select.css" rel="stylesheet" type="text/css" />

    <!-- TOUCHSPIN -->
    <link href="{{ $assetsHost }}template1/assets/libs/bootstrap-touchspin/jquery.bootstrap-touchspin.min.css" rel="stylesheet" type="text/css" />

    <!-- TIMEPICKER -->
    <link href="{{ $assetsHost }}template1/assets/libs/bootstrap-timepicker/bootstrap-timepicker.min.css" rel="stylesheet" type="text/css" />

    <!-- SWITCHERY -->
    <link href="{{ $assetsHost }}template1/assets/libs/switchery/switchery.min.css" rel="stylesheet" type="text/css" />

    <!-- BOOTSTRAP-SWITCH -->
    <link href="{{ $assetsHost }}template1/assets/extra-libs/switch/dist/css/bootstrap4/bootstrap-switch.css" rel="stylesheet" type="text/css" />

    <!-- TOASTR -->
    <link href="{{ $assetsHost }}template1/assets/extra-libs/toastr/toastr.css" rel="stylesheet" type="text/css" />

    <!-- DATATABLES -->
    <link rel="stylesheet" type="text/css" href="{{ $assetsHost }}template1/assets/extra-libs/datatables/datatables.min.css"/>

    <!-- SWAL -->
    <link rel="stylesheet" type="text/css" href="{{ $assetsHost }}template1/assets/extra-libs/swal/dist/sweetalert2.css"/>

    <!-- UI -->
    <link rel="stylesheet" href="{{ $assetsHost }}theme/scripts/jquery-ui/themes/custom-theme/jquery.ui.all.css">

    <!-- TREE-GRID -->
    <link rel="stylesheet" href="{{ $assetsHost }}template1/scripts/tree-grid/jquery.treegrid.css">

    <link rel="stylesheet" href="{{ $assetsHost }}template1/assets/plugin/swiper/swiper-bundle.min.css">

    <!-- FullCalendar New -->
    <link rel="stylesheet" href="{{ $assetsHost }}template1/plugin/fullcalendar/main.min.css">

    <!-- Intro JS New -->
    <link rel="stylesheet" href="{{ $assetsHost }}template1/plugin/introjs/introjs.min.css">

    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,300;1,400;1,500;1,600;1,700;1,800&display=swap" rel="stylesheet">

    <!-- Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-MCW1NBSP7K"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', 'G-MCW1NBSP7K');
    </script>

    @stack('styles')

</head>
<body class="main left-menu sticky_footer">

    <div class="loading modal-backdrop" id="preloader">
        <div class="status-loading">
            <div class="spinner">Loading...</div>
        </div>
    </div>

    <!-- Begin page -->
    <div id="wrapper">

        <!-- Header -->
        @include('partials.header1')
        <!-- End Header -->

        <div class="menu_kiri mainMenu" id="menu_kiri"></div>
        <input type="hidden" value="" class="submenu">
        <!-- Left Sidebar End -->

        <!-- Start Content -->
        <div class="content-page">
            <div class="content">
                <!-- Start Content-->
                <div class="container-fluid">
                    <div class="col-md-12 mt-2">
                        <!-- Success Alert -->
                        <div class="alert alert-success alert-block alert-custom" style="display:none;z-index:9999999;">
                            <button onclick="$('.alert-success').hide();" class="close close-sm" type="button">&times;</button>
                            <h4>Success!</h4>
                            <p class="alert-success-content">You successfully read this successful alert message.</p>
                        </div>

                        <!-- Error Alert -->
                        <div class="alert alert-error alert-danger alert-block alert-custom" style="display:none;z-index:9999999;">
                            <button onclick="$('.alert-error').hide();" class="close close-sm" type="button">&times;</button>
                            <h4>Maaf!</h4>
                            <p class="alert-error-content">Hey, you have some error here...</p>
                        </div>

                        <!-- Warning Alert -->
                        <div class="alert alert-warning alert-custom" style="display:none;">
                            <button data-dismiss="alert" class="close close-sm" type="button">&times;</button>
                            <h4>Warning!</h4>
                            <p class="alert-warning-content">Best check yo self, you're not...</p>
                        </div>

                        <!-- Info Alert -->
                        <div class="alert alert-info alert-custom" style="display:none;">
                            <button data-dismiss="alert" class="close close-sm" type="button">&times;</button>
                            <h4>Info!</h4>
                            <p class="alert-info-content">This alert needs your attention, but it's not super important.</p>
                        </div>

                        <div class="load_content"></div>
                    </div>
                </div>

                <!-- Main Content Area - Konten Dinamis di-load di sini -->
                <div id="isi_load">
                    @yield('content')
                </div>
            </div>

            <!-- Footer -->
            <footer class="footer bg-white" style="position:fixed">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-12 d-flex justify-content-between align-items-center">
                            <a href="javascript:void(0);" class="btn btn-outline-secondary btn-menu-footer waves-effect waves-light show_big_thumbnails d-mobile-none" onclick="$('.erp').fadeToggle('fast');">
                                <i class="fas fa-list mr-1"></i> Menu
                            </a>
                            <div class="menu-footer erp" style="display:none;width: 485px;">
                                <div class="row">
                                    <div class="col-md-12 pl-0 pr-0">
                                        <div class="bg-secondary p-2 mb-1">
                                            <h5 class="text-white mb-0 mt-0"><i class="fas fa-grip-horizontal mr-2"></i> Pilih Menu</h5>
                                        </div>
                                    </div>
                                    @forelse($menus ?? [] as $menu)
                                        <div class="col-md-2 align-self-center pl-0 pr-0">
                                            <div class="list-menu-fot" style="margin-bottom: 12px;padding: 0px;">
                                                <a id="menu{{ $menu->ID }}" href="#" onclick="modul('{{ $menu->ID }}'); $('.erp').fadeToggle('slow');" class="d-block waves-effect waves-light">
                                                    <img src="{{ $assetsHost }}icon-baru/{{ $menu->Ikon }}" alt="" width="30">
                                                    <p class="text-dark pt-1 mb-0">{{ $menu->Nama }}</p>
                                                </a>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="col-md-12">
                                            <p class="text-muted">No menus available</p>
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                            &copy; Copyright {{ $identitas->SingkatanPT ?? 'Kampus' }}
                        </div>
                    </div>
                </div>
            </footer>

            <!-- Search Modal -->
            <div class="modal fade" id="search-modal" tabindex="-1" role="dialog" aria-labelledby="search-modal" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-body p-0">
                            <div class="box-search d-flex justify-content-between">
                                <span class="align-self-center pr-1 text-dark font-size-18">/</span>
                                <input type="text" class="form-control form-search-modal" id="search-data" placeholder="Ketik apa yang ingin kamu cari..." autocomplete="off">
                                <i class="fe-search noti-icon align-self-center font-size-25" style="opacity: 0.6;"></i>
                            </div>
                            <hr class="mb-0 mt-0">
                            <p class="not-found mb-0" style="padding: 8px 20px;font-size: 13px;display:none;">
                                <i>Keyword tidak di temukan.</i>
                            </p>
                            <div class="data-list" style="max-height: 400px;overflow: auto;"></div>
                        </div>
                        <div class="modal-footer d-block" style="padding: 8px 20px; font-size: 13px;">
                            <p class="m-0"><span style="color: #377d82;font-weight: 600;">TIP</span> — open me anywhere with <span style="color: #377d82;font-weight: 600;">Ctrl + K</span></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- View Photo Modal -->
            <div class="modal fade modal-custom" id="view-photo-modal" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <img src="" alt="Photo" style="width: 100%;">
                        </div>
                    </div>
                </div>
            </div>

        </div>
        <!-- End Content Page -->

    </div>
    <!-- End page -->

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="{{ $assetsHost }}template1/assets/js/bootstrap.bundle.min.js"></script>
    <script src="{{ $assetsHost }}template1/assets/libs/select2/select2.min.js"></script>
    <script src="{{ $assetsHost }}template1/assets/extra-libs/datatables/datatables.min.js"></script>
    <script src="{{ $assetsHost }}template1/assets/extra-libs/swal/dist/sweetalert2.js"></script>
    <script src="{{ $assetsHost }}template1/assets/extra-libs/toastr/toastr.min.js"></script>

    <script>
        // Base URLs untuk AJAX
        const baseUrl = "{{ url('/') }}";
        const assetsUrl = "{{ $assetsHost }}";
        const userId = "{{ auth()->user()->id ?? '' }}";
        const userModulGrup = "{{ auth()->user()->modul_grup ?? '' }}";

        // Fungsi untuk memanggil modul
        function modul(id) {
            // Load menu dari route
            $.ajax({
                url: "{{ route('menu.load') }}",
                type: 'GET',
                data: { modul_id: id },
                success: function(data) {
                    $('#isi_load').html(data);
                }
            });
        }

        // Refresh modul menu
        function modul_refresh(id) {
            $('a').removeClass('aktif');
            $('#menu' + id).addClass('aktif');
            $('.mainMenu').load("{{ route('menu.sidebar') }}?id=" + id);
        }

        // Initialize modul pada page load
        $(function() {
            if(userModulGrup) {
                modul_refresh(userModulGrup);
            }
        });

        // Helper Functions
        function addZero(i) {
            if (i < 10) {
                i = "0" + i;
            }
            return i;
        }

        function rupiah(hasil) {
            num = hasil;
            num = num.toString().replace(/\Rp|/g,'');
            if(isNaN(num))
                num = "0";
            sign = (num == (num = Math.abs(num)));
            num = Math.floor(num*100+0.50000000001);
            cents = num%100;
            num = Math.floor(num/100).toString();
            if(cents<10)
                cents = "0" + cents;
            for (var i = 0; i < Math.floor((num.length-(1+i))/3); i++)
                num = num.substring(0,num.length-(4*i+3))+'.'+
                num.substring(num.length-(4*i+3));
            return ((sign)?'':'-') + 'Rp ' + num ;
        }

        function setCookie(cname, cvalue, exdays = "36525") {
            const d = new Date();
            d.setTime(d.getTime() + (exdays*24*60*60*1000));
            let expires = "expires="+ d.toUTCString();
            document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
        }

        function getCookie(cname) {
            let name = cname + "=";
            let decodedCookie = decodeURIComponent(document.cookie);
            let ca = decodedCookie.split(';');
            for(let i = 0; i < ca.length; i++) {
                let c = ca[i];
                while (c.charAt(0) == ' ') {
                    c = c.substring(1);
                }
                if (c.indexOf(name) == 0) {
                    return c.substring(name.length, c.length);
                }
            }
            return "";
        }

        function fncDelay(callback, ms = 0) {
            var timer = 0;
            return function() {
                var context = this, args = arguments;
                clearTimeout(timer);
                timer = setTimeout(function () {
                    callback.apply(context, args);
                }, ms);
            };
        }

        // Ubah Password Modal
        function ubahpass(){
            $('#modaljudul').html('<i class="fa fa-lock"></i> Ubah Password');
            $('#modalbody').load("{{ route('profile.change-password-form') }}");
            $('#load_modal2').modal('show');
        }

        // Search keyword dengan Enter
        $('#upkeyword').keypress(function (e) {
            if (e.which == 13) {
                carimodul();
            }
        });

        // Fungsi cari modul
        function carimodul() {
            let keyword = $('#upkeyword').val();
            if(keyword.length > 0) {
                $.ajax({
                    url: "{{ route('search.modul') }}",
                    type: 'GET',
                    data: { keyword: keyword },
                    success: function(data) {
                        // Handle search result
                    }
                });
            }
        }
    </script>

    <!-- Modals -->
    <div class="modal" tabindex="-1" role="dialog" id="load_modal">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Header</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Body</p>
                </div>
                <div class="modal-footer" id="load_modal_footer">
                    &nbsp;
                </div>
            </div>
        </div>
    </div>

    <div class="modal modalLarge" tabindex="-1" role="dialog" id="load_modal_large">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Header</h5>
                    <button type="button" onclick="$('#mdldlmvid').html('loading..'); $('#myModalawal').remove();" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="mdldlmvid">
                    <p>Body</p>
                </div>
                <div class="modal-footer" id="load_modal_large_footer">
                    &nbsp;
                </div>
            </div>
        </div>
    </div>

    <div class="modal" tabindex="-1" role="dialog" id="load_modal2">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modaljudul">Header</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="modalbody">
                    <p><i class="fa fa-spin fa-spinner"></i> Loading..</p>
                </div>
                <div class="modal-footer"></div>
            </div>
        </div>
    </div>

    <div class="modal modalLarge" tabindex="-1" role="dialog" id="load_modal_xtralarge">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Header</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                </div>
                <div class="modal-body">
                    <p>Body</p>
                </div>
                <div class="modal-footer">
                    &nbsp;
                </div>
            </div>
        </div>
    </div>

    <div id="last_script"></div>

    @stack('scripts')

</body>
</html>
