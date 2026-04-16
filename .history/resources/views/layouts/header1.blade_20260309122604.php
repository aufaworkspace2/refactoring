
<div class="navbar-custom top-bar">
    <div class="container-fluid">
        <div class="row align-items-center">

            <div class="col-md-9">
                <div class="d-flex align-items-center justify-content-end">
                    {{-- Search Bar --}}
                    <div class="search-box" style="flex: 1; max-width: 400px;">
                        <div class="input-group input-group-sm">
                            <input type="text" class="form-control" id="upkeyword" placeholder="Cari..." data-toggle="modal" data-target="#search-modal">
                            <span class="input-group-append">
                                <button class="btn btn-light" type="button">
                                    <i class="fe-search"></i>
                                </button>
                            </span>
                        </div>
                    </div>

                    {{-- Notifications --}}
                    <div class="dropdown d-inline-block ml-3">
                        <button class="btn btn-header waves-effect waves-light" id="notif-dropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fe-bell noti-icon"></i>
                            <span class="badge badge-danger badge-pulse" id="badge-notif" style="display:none;">1</span>
                        </button>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="notif-dropdown" style="width: 350px;">
                            <div class="dropdown-header">
                                <h5 class="dropdown-title m-0">
                                    <i class="fe-bell mr-2"></i>Notifikasi
                                </h5>
                            </div>
                            <div class="slimscroll" style="max-height: 240px;">
                                <div id="notif-list">
                                    {{-- Notification items di-load via AJAX --}}
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- User Profile Dropdown --}}
                    <div class="dropdown d-inline-block ml-3">
                        <button class="btn btn-header waves-effect waves-light" id="user-dropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <img class="rounded-circle header-profile-user" src="{{ $assets_host}}avatar/{{ $user->avatar ?? 'default.jpg' }}" alt="Header Avatar">
                            <span class="d-none d-xl-inline-block ml-2 mr-1">{{ $user->name ?? 'User' }}</span>
                            <i class="fe-chevron-down d-none d-xl-inline-block"></i>
                        </button>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="user-dropdown">
                            <h6 class="dropdown-header">Profil</h6>
                            <a class="dropdown-item" href="{{ route('profile.edit') }}">
                                <i class="fe-user mr-1"></i>Profil Saya
                            </a>
                            <a class="dropdown-item" href="javascript:void(0)" onclick="ubahpass()">
                                <i class="fe-lock mr-1"></i>Ubah Password
                            </a>
                            <div class="dropdown-divider m-0"></div>
                            <a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                <i class="fe-log-out mr-1"></i>Keluar
                            </a>
                        </div>
                    </div>

                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                        @csrf
                    </form>

                    {{-- Sidebar Toggle --}}
                    <div class="ml-3">
                        <button type="button" class="btn btn-header waves-effect waves-light" id="btnSidebarToggle">
                            <i class="fe-menu"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
