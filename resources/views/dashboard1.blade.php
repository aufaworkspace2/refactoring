@extends('layouts.template1')

@section('content')

@php 
    $modulgrup = session('modulgrup');

    if($modulgrup == 0 || $modulgrup == null){
        $image = asset('assets/images/erp.png');
        $s = 0;
    } else {
        // Asumsi fungsi get_field() dari CI3 sudah dipindah ke GlobalHelper
        $ikon = get_field($modulgrup, 'modulgrup', 'Ikon');
        $image = asset('assets/icon-baru/' . $ikon);
        $s = 1;
    }
@endphp

@if($modulgrup == 0 || $modulgrup == null)
    <div class="container section_dashboard">
        <div class="row justify-content-center">
            
            @if(isset($show_setup_crp) && $show_setup_crp > 0)
            <div class="col-md-12 pl-3 pr-3 mt-3">
                <div class="card card-setup-guide">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <h3 class="mt-0 mb-2">Setup Edufecta</h3>
                                <p class="mb-0 font-size-17">Beberapa langkah lagi untuk memulai Aplikasi Edufecta.</p>
                            </div>
                            <div class="col-md-4">
                                @foreach($setup_crp as $key => $val)
                                    @php
                                        if($val['status_setup'] == 'active') {
                                            $step_aktif = $val['urut'];
                                        } 
                                    @endphp
                                    
                                    @if($val['status_tour'] == '1')
                                        <a href="javascript:void(0);" class="d-block" onclick="clickTour({{ $val['key_modul'] }},'{{ $val['link'] }}')">
                                    @else
                                        <a href="{{ url($val['link']) }}" class="d-block">
                                    @endif
                                        <div class="list-tour {{ $val['status_setup'] }}">
                                            <div class="d-flex">
                                                @if($val['progress'] == '0')
                                                    <span class="badge badge-custom mr-2">STEP {{ $val['urut'] }}</span>
                                                @elseif($val['progress'] < '100')
                                                    <span class="badge badge-custom mr-2">DIPROSES</span>
                                                @else
                                                    @if(isset($step_aktif) && ($step_aktif < $val['urut']))
                                                        <span class="badge badge-custom mr-2">STEP {{ $val['urut'] }}</span>
                                                    @else
                                                        <span class="badge badge-custom mr-2">SELESAI</span>
                                                    @endif
                                                @endif
                                                <p class="mb-0 align-self-center">{{ $val['label'] }}</p>
                                            </div>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                            <div class="col-md-4 text-right">
                                <object data="{{ asset('assets/template1/assets/images/wel3.svg') }}" style="width: 70%;"> </object>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            @if(!isset($show_setup_crp) || $show_setup_crp == 0)
            <div class="col-md-12 pl-3 pr-3 mb-3 mt-3">
                <div class="d-flex justify-content-between">
                    {{-- Pastikan fungsi get_field tersedia di GlobalHelper kamu --}}
                    <h3 class="mb-1 mt-0">Selamat Datang, {{ get_field(session('UserID'), 'user', 'NamaEntity') }}!</h3>
                </div>
            </div>
            @endif

            {{-- Alert Modal Mandatory --}}
            <div class="modal fade" id="alert-progress-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel">Alert Progess Data Mandatory Aplikasi</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div id="accordion" class="mb-3">
                                @if(isset($list_alert_progress))
                                    @foreach($list_alert_progress as $key => $lap)
                                    <div class="card mb-1">
                                        <div class="card-header cursor-pointer" data-toggle="collapse" data-target="#collapse-{{ $key }}" id="headingOne">
                                            <h5 class="m-0">
                                                <a class="text-dark">
                                                    {!! $lap['alert'] !!}
                                                </a>
                                                @if(!empty($lap['detail']))
                                                    <i class="mdi mdi-arrow-down-drop-circle-outline float-right"></i>
                                                @endif
                                            </h5>
                                        </div>
                                        
                                        @if(!empty($lap['detail']))
                                        <div id="collapse-{{ $key }}" class="collapse" aria-labelledby="headingOne" data-parent="#accordion">
                                            <div class="card-body">
                                                {!! $lap['detail'] !!}
                                            </div>
                                        </div>
                                        @endif
                                    </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            {{-- Loop Menu (Data $menus dikirim dari DashboardController) --}}
            <div class="row col-12 px-0">
                @if(isset($menus))
                    @foreach($menus as $row)
                        <div class="col-md-2 col-6 model-menu text-center">
                            {{-- Pindah logika Javascript ke Routing murni backend --}}
                            <a class="d-block" href="{{ route('dashboard.set_modul', ['id' => $row->ID]) }}" id="tour-{{ $row->ID }}">
                                <img src="{{ asset('assets/icon-baru/' . $row->Ikon) }}" width="50" alt="">
                                <p>{{ $row->Nama }}</p>
                            </a>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    </div>

@else
    {{-- Tampilan Jika Bukan Grup Menu 0 --}}
    <div class="row mt-5">
        <div class="col-md-3">
            <center><img class="intro w-75" src="{{ $image }}"></center>
        </div>
        <div class="col-md-7">
            <h4 class="modul text-left">"Modul {{ ucwords(get_field($modulgrup, 'modulgrup')) }}"</h4>
            <p class="modul text-left">{{ get_field($modulgrup, 'modulgrup', "Keterangan") }}</p>

            @if(ucwords(get_field($modulgrup, 'modulgrup')) == "Data PMB")
                <a href="{{ env('PMB_URL') }}" class="btn btn-bordered-primary" target="_blank">GO TO WEBSITE</a>
            @elseif(ucwords(get_field($modulgrup, 'modulgrup')) == "Pusat Karir")
                <a href="{{ env('ALUMNI_URL') }}" class="btn btn-bordered-primary" target="_blank">GO TO WEBSITE</a>
            @endif
        </div>
    </div>
@endif

{{-- Pisahkan Semua Script ke @push agar dirender di Footer oleh template1 --}}
@push('scripts')
<script>
    var app_min_js = 0;

    @if($modulgrup == 0 || $modulgrup == null)
        if(app_min_js == 1){
            $( document ).ready(function() {
                if(!$(document.body).hasClass('enlarged')){
                    $(document.body).addClass('enlarged');
                    $(document.body).removeClass('sidebar-enabled');
                }
            });
        }
        app_min_js = 1;
    @else
        if(app_min_js == 1){
            $( document ).ready(function() {
                if($(document.body).hasClass('enlarged')){
                    $(document.body).removeClass('enlarged');
                }
            });
        }
    @endif

    // Logika Tour Intro.js
    @if($modulgrup != 0) 
        @if($modulgrup == 81)
            var introModulLog = introJs();
            introModulLog.setOptions({
                showStepNumbers: true,
                showBullets: false,
                nextLabel: 'Lanjut',
                prevLabel: 'Kembali',
                doneLabel: 'Lanjut',
                steps: [
                    { element: document.querySelector('#menu_354'), intro: 'Ini merupakan aksi untuk tahun akademik.' },
                    { element: document.querySelector('#submenu_107'), intro: 'Ini merupakan aksi untuk mengatur tahun akademik yang sedang aktif.' }
                ]
            });
            $(document).ready(function(){
                introModulLog.onexit(function(){ setCookie("tour_dashboard_tahun","1"); })
                .oncomplete(function () {
                    setCookie("tour_dashboard_tahun","1");
                    location.href = "{{ url('c_tahun') }}";
                });
                if(getCookie("tour_dashboard_tahun") == ""){
                    introModulLog.start();
                    introModulLog.onchange(function(el) {
                        if($(el).hasClass("submenu_107")){ $('#menu_354').click(); }
                    });
                }
            });

        @elseif($modulgrup == 70)
            // ... (dst, logika javascript tour di CI3 tetap dibiarkan sama)
            // Cukup gunakan syntax "{{ url('nama_rute') }}" jika ada perpindahan halaman
        @endif
    @endif

    $('.hidemodal-alert').on('click', function(){
        $('#alert-progress-modal').modal('hide');
    });
    
    function clickTour(id, datahash=''){
        if(getCookie(`tour_dashboard_${id}`) == ''){
            var textalert = "";
            if(id == 81) textalert = "Ini merupakan modul untuk mengatur tahun akademik & kalender akademik, klik untuk melihat.";
            else if(id == 70) textalert = "Ini merupakan modul untuk mengatur data KRS & Nilai.";
            else if(id == 2) textalert = "Ini merupakan modul untuk mengatur jadwal perkuliahan.";

            var introModulStep = introJs();
            introModulStep.setOptions({
                doneLabel: 'Lanjut',
                steps: [{ element: document.querySelector('#tour-'+id), intro: textalert, position: 'right' }]
            }).onexit(function(){
                setCookie(`tour_dashboard_${id}`,"1");
                modul(id);
            }).oncomplete(function () {
                setCookie(`tour_dashboard_${id}`,"1");
                modul(id);
                location.href = "{{ url('/') }}#";
            }).start();
        } else {
            modul(id);
            showHideToggle(1);
            $('#top_toggle').click();
            window.location.href = `{{ url('/') }}/${datahash}`;
        }
    }

    // Trigger visual Dashboard
    @if($s == 1)
        homeopen();
        showHideToggle(1);
    @else
        home();
        showHideToggle(0);
        $('#top_toggle').click();
    @endif
</script>
@endpush

@endsection