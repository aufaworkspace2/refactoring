<!DOCTYPE html>
<html>
<head>
    @php

    $identitas = get_id(1,'identitas');

    if($identitas){
        $img = $identitas->Gambar;
        $img_kecil = $identitas->GambarKecil;
    }else{
        $img='no_image.jpg';
        $img_kecil='no_image.jpg';
    }
    @endphp
    <title>Sistem Informasi Akademik - {{ $identitas->SingkatanPT }}</title>
	<link rel="shortcut icon" href="{{ $identitas->UrlGambar }}">
	<!-- Bootstrap -->
	<meta charset="utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="robots" content="noindex, nofollow" />


    <!-- Bootstrap Css -->
    <link href="{{ asset('assets/template1/assets/css/bootstrap.min.css') }}" id="bootstrap-stylesheet" rel="stylesheet" type="text/css" />

    <!-- Icons Css -->
    <link href="{{ asset('assets/template1/assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />

    <!-- App Css-->
    <link href="{{ asset('assets/template1/assets/css/app.min.css') }}" id="app-stylesheet" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/template1/assets/css/custom.css') }}" id="app-stylesheet" rel="stylesheet" type="text/css" />

    <!-- SELECT2 -->
    <link href="{{ asset('assets/template1/assets/libs/select2/select2.min.css') }}" rel="stylesheet" type="text/css" />

    <!-- DATETIMEPICKER -->
    <link href="{{ asset('assets/template1/assets/extra-libs/datetimepicker/css/jquery.datetimepicker.min.css') }}" rel="stylesheet" type="text/css" />

    <!-- DATEPICKER -->
    <link href="{{ asset('assets/template1/assets/libs/bootstrap-datepicker/bootstrap-datepicker.css') }}" rel="stylesheet" type="text/css" />

    <!-- DATERANGEPICKER -->
    <link href="{{ asset('assets/template1/assets/libs/bootstrap-daterangepicker/daterangepicker.css') }}" rel="stylesheet" type="text/css" />

    <!-- COLORPICKER -->
    <link href="{{ asset('assets/template1/assets/libs/bootstrap-colorpicker/bootstrap-colorpicker.min.css') }}" rel="stylesheet" type="text/css" />

    <!-- MULTI SELECT -->
    <link href="{{ asset('assets/template1/assets/libs/multiselect/multi-select.css') }}" rel="stylesheet" type="text/css" />

    <!-- TOUCHSPIN -->
    <link href="{{ asset('assets/template1/assets/libs/bootstrap-touchspin/jquery.bootstrap-touchspin.min.css') }}" rel="stylesheet" type="text/css" />

    <!-- TIMEPICKER -->
    <link href="{{ asset('assets/template1/assets/libs/bootstrap-timepicker/bootstrap-timepicker.min.css') }}" rel="stylesheet" type="text/css" />

    <!-- SWITCHERY -->
    <link href="{{ asset('assets/template1/assets/libs/switchery/switchery.min.css') }}" rel="stylesheet" type="text/css" />

    <!-- BOOTSTRAP-SWITCH -->
    <link href="{{ asset('assets/template1/assets/extra-libs/switch/dist/css/bootstrap4/bootstrap-switch.css') }}" rel="stylesheet" type="text/css" />

    <!-- TOASTR -->
    <link href="{{ asset('assets/template1/assets/extra-libs/toastr/toastr.css') }}" rel="stylesheet" type="text/css" />

    <!-- DATATABLES -->
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/template1/assets/extra-libs/datatables/datatables.min.css') }}"/>

    <!-- SWAL -->
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/template1/assets/extra-libs/swal/dist/sweetalert2.css') }}"/>

    <!-- UI -->
    <link rel="stylesheet" href="{{ asset('assets/theme/scripts/jquery-ui/themes/custom-theme/jquery.ui.all.css') }}">

    <!-- TREE-GRID -->
    <link rel="stylesheet" href="{{ asset('assets/template1/scripts/tree-grid/jquery.treegrid.css') }}">

    <link rel="stylesheet" href="{{ asset('assets/template1/assets/plugin/swiper/swiper-bundle.min.css') }}">

    <!-- FullCalendar New -->
    <link rel="stylesheet" href="{{ asset('assets/template1/plugin/fullcalendar/main.min.css') }}">

    <!-- Intro JS New -->
    <link rel="stylesheet" href="{{ asset('assets/template1/plugin/introjs/introjs.min.css') }}">

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

    <!-- <div class="loading modal-backdrop" id="preloader">
        <div class="status-loading">
            <div class="spinner">Loading...</div>
        </div>
    </div> -->

    <!-- Begin page -->
    <div id="wrapper">

        <!-- Header -->
        @include('layouts.header1')
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
                                                    <img src="{{ asset('assets/icon-baru/{{ $menu->Ikon ') }}" alt="" width="30">
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
                            @php
                                $yearNow = date('Y');
                            @endphp
						    &copy; Copyright  {{ $identitas->SingkatanPT }}
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
    <script src="{{ asset('assets/template1/assets/js/vendor.min.js') }}"></script>

	<!-- knob plugin -->
	<script src="{{ asset('assets/template1/assets/libs/jquery-knob/jquery.knob.min.js') }}"></script>

	<script src="{{ asset('assets/template1/assets/js/app.min.js') }}"></script>

	<script src="{{ asset('assets/template1/assets/js/popper.min.js') }}"></script>

	<!-- moment js -->
	<script src="{{ asset('assets/template1/assets/libs/moment/moment.js') }}"></script>

	<!--Morris Chart-->
	<script src="{{ asset('assets/template1/assets/libs/morris-js/morris.min.js') }}"></script>
	<script src="{{ asset('assets/template1/assets/libs/raphael/raphael.min.js') }}"></script>

	<!-- Validate -->
	<script src="{{ asset('assets/template1/assets/extra-libs/jquery-validation/dist/jquery.validate.js') }}"></script>

	<!-- SELECT2 -->
	<script src="{{ asset('assets/template1/assets/libs/select2/select2.min.js') }}"></script>

	<!-- DATETIMEPICKER -->
	<script src="{{ asset('assets/template1/assets/extra-libs/datetimepicker/js/jquery.datetimepicker.full.min.js') }}"></script>

	<!-- DATEPICKER -->
	<script src="{{ asset('assets/template1/assets/libs/bootstrap-datepicker/bootstrap-datepicker.min.js') }}"></script>

	<!-- DATEPICKER -->
	<script src="{{ asset('assets/template1/assets/libs/bootstrap-daterangepicker/daterangepicker.js') }}"></script>

	<!-- COLORPICKER -->
	<script src="{{ asset('assets/template1/assets/libs/bootstrap-colorpicker/bootstrap-colorpicker.min.js') }}"></script>

	<!-- MULTISELECT -->
	<script src="{{ asset('assets/template1/assets/libs/multiselect/jquery.multi-select.js') }}"></script>

	<!-- TOUCHSPIN -->
	<script src="{{ asset('assets/template1/assets/libs/bootstrap-touchspin/jquery.bootstrap-touchspin.min.js') }}"></script>

	<!-- TIMEPICKER -->
	<script src="{{ asset('assets/template1/assets/libs/bootstrap-timepicker/bootstrap-timepicker.min.js') }}"></script>

	<!-- MAXLENGTH -->
	<script src="{{ asset('assets/template1/assets/libs/bootstrap-maxlength/bootstrap-maxlength.min.js') }}"></script>

	<!-- WIZARD -->
	<script src="{{ asset('assets/template1/assets/libs/bootstrap-wizard/bootstrap-wizard.min.js') }}"></script>

	<!-- SWITCHERY -->
	<script src="{{ asset('assets/template1/assets/libs/switchery/switchery.min.js') }}"></script>

	<!-- BOOTSTRAP-SWITCH -->
	<script src="{{ asset('assets/template1/assets/extra-libs/switch/dist/js/bootstrap-switch.js') }}"></script>

	<!-- TABLESORTER -->
	<script src="{{ asset('assets/template1/assets/extra-libs/tablesorter/jquery.tablesorter.js') }}"></script>


	<!-- TOASTR -->
	<script src="{{ asset('assets/template1/assets/extra-libs/toastr/toastr.js') }}"></script>

	<!-- DATATABLES -->
	<script src="{{ asset('assets/template1/assets/extra-libs/datatables/datatables.min.js') }}"></script>

	<!-- SWAL -->
	<script src="{{ asset('assets/template1/assets/extra-libs/swal/dist/sweetalert2.min.js') }}"></script>

	<!-- Dashboard init js-->
	<script src="{{ asset('assets/template1/assets/js/pages/dashboard.init.js') }}"></script>

	<script src="{{ asset('assets/template1/assets/js/pages/form-advanced.init.js') }}"></script>


	<script src="{{ asset('assets/tinymceV4/jquery.tinymce.min.js') }}"></script>
	<script src="{{ asset('assets/tinymceV4/tinymce.min.js') }}"></script>

	<!-- Jquery Mask -->
	<script src="{{ asset('assets/template1/scripts/jquery.mask.min.js') }}"></script>

	<!-- Jquery UI -->
	<script src="{{ asset('assets/template1/scripts/jquery-ui/ui/jquery.ui.core.js') }}"></script>
	<script src="{{ asset('assets/template1/scripts/jquery-ui/ui/jquery.ui.widget.js') }}"></script>
	<script src="{{ asset('assets/template1/scripts/jquery-ui/ui/jquery.ui.datepicker.js') }}"></script>
    <script src="{{ asset('assets/template1/scripts/jquery-ui/ui/i18n/jquery.ui.datepicker-id.js') }}"></script>
	<script src="{{ asset('assets/template1/scripts/jquery-ui/ui/jquery.ui.button.js') }}"></script>
	<script src="{{ asset('assets/template1/scripts/jquery-ui/ui/jquery.ui.spinner.js') }}"></script>
	<script src="{{ asset('assets/template1/scripts/jquery-ui/ui/jquery.ui.mouse.js') }}"></script>
	<script src="{{ asset('assets/template1/scripts/jquery-ui/ui/jquery.ui.draggable.js') }}"></script>
	<script src="{{ asset('assets/template1/scripts/jquery-ui/ui/jquery.ui.sortable.js') }}"></script>

	<script  src="{{ asset('assets/template1/scripts/jquery-ui/ui/jquery.ui.position.js') }}"></script>
	<script  src="{{ asset('assets/template1/scripts/jquery-ui/ui/jquery.ui.droppable.js') }}"></script>





	<!-- SORTABLE -->
	<script type="text/javascript" src="{{ asset('assets/sort/sortable.js') }}"></script>

	<!-- HIGHCHART -->
	<script src="{{ asset('assets/HC/js/highcharts.js') }}"></script>
    <script src="{{ asset('assets/HC/js/highcharts-3d.js') }}"></script>

	<!-- CHART -->
    <script src="{{ asset('assets/template1/scripts/Chart.bundle.min.js') }}"></script>

	<!-- TREE-GRID -->
	<script src="{{ asset('assets/template1/scripts/tree-grid/jquery.treegrid.js') }}"></script>

	<script src="{{ asset('assets/template1/assets/plugin/swiper/swiper-bundle.min.js') }}"></script>

	<script src="{{ asset('assets/template1/assets/js/chartjs-plugin-datalabels.min.js') }}"></script>

	<script src="{{ asset('assets/template1/plugin/introjs/intro.min.js') }}"></script>

	<script src="https://unpkg.com/axios/dist/axios.min.js"></script>

	<!-- FullCalendar New -->
	<script src="{{ asset('assets/template1/plugin/fullcalendar/main.min.js') }}"></script>

    <script>

		var introAlur = introJs();

		introAlur.setOptions({
			showStepNumbers: false,
			showBullets: false,
			doneLabel: 'Lanjut',
			steps: [{
				element: document.querySelector('#btn-alur'),
				intro: 'Anda dapat melihat diagram alur sistem pada tombol pencarian diatas'
			}]
		});

		var introAlurModal = introJs();

		introAlurModal.setOptions({
			showStepNumbers: false,
			showBullets: false,
			doneLabel: 'Selesai',
			steps: [{
				element: document.querySelector('#search-data'),
				intro: 'Mulai dengan mencari gambaran alur yang ingin Anda ketahui'
			}]
		});

		$(document).ready(function() {
			introAlur.onexit(function() {
			}).oncomplete(function() {
				$('#search-modal').modal('show');
				setTimeout(function() {
					if ($('#search-modal').is(':visible') == true) {
						introAlurModal.start();
						$('#alert-alur').fadeOut();
					}
				}, 500);
			});
			if (getCookie("tour_alur") != "") {
				$('#alert-alur').hide();
			}
		});

		function clickTourAlur() {
			introAlur.start();
		}

		function clickTourRedaksi(id){
			modul(id);
			setCookie("tour_redaksi_pmb","","-1");
			setCookie("tour_redaksi_pmb_detail","","-1");

		}

		var lokasifile = "<?=getenv('WEB_URL')?>/medias/alursistem"

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

		$(document).keydown(function (e) {
			$('#search-data').focus();
			if (e.ctrlKey && e.which === 75) {
				$('.form-search-modal').removeAttr('disabled');
				$('#search-modal').modal('show');
				event.preventDefault();
			}
		});

		function showPhoto(id){
			$('#view-photo-modal').modal('show');

			var image_next  = $('#file_'+id).val();
			var text_next   = $('#nama_'+id).val();

			$('#drive-view').html(`<img class="mx-auto d-block img-fluid" src="${lokasifile}/${image_next}">`);
			$('#file-title').html(`<img width="16" height="16" class="mr-2 align-self-center" src="${lokasifile}/jpeg.png" alt=""> ${text_next}`);
			$('.download').attr(`href`, `${lokasifile}/${image_next}`);

		}
	</script>
	<script type="text/javascript">




		$('[data-toggle="tooltip"]').tooltip()

		var app_min_js = 0;

		$(window).on('load', function() {
			$.ajaxSetup({
		        statusCode: {
		            303: function(){
		                location.reload();
		            }
		        }
		    });
		})

		$(document).ready(function(){
			$('#klikdisini-akademik').on('click', function(){
				$('body').removeClass('top-bar-active');
				$('#alert-after-akademik').hide();
			});
		});


		$(document).ready(function(){

			$.ajaxSetup({
		        statusCode: {
		            303: function(){
		                location.reload();
		            }
		        }
		    });

			$(".modal-backdrop, #load_modal .close, #load_modal .btn").on("click", function() {
				$("#load_modal iframe").attr("src", $("#load_modal iframe").attr("src"));
			});
		});

		function showHideToggle(isOpen){
			if(isOpen == 1){
				$("#top_toggle").show();
			}else{
				$("#top_toggle").hide();
			}
		}

		$(".mainMenuWrapper").click(function(){
			$(".startmenu").css("visibility", "hidden");
		});
		$("a.show_big_thumbnails").click(function(){
			$(".startmenu").css("visibility", "visible !important");
		});



		function load_modal(header,body,footer)
		{
			$('#load_modal .modal-title').html(header);
			$('#load_modal .modal-body').load("{{ url('') }}/"+body);

			if(footer != null)
			{
				$('#load_modal .modal-footer').html(footer);
			}

			$('#load_modal_large').modal('hide');
			$('#load_modal').modal('show');

		}
		function loadhelp(l)
		{
			$('#load_modal_large .modal-title').html("Informasi Modul");
			$('#load_modal_large .modal-body').load("{{ url('welcome/help') }}/"+l);


			$('#load_modal').modal('hide');
			$('#load_modal_large').modal('show');

		}
		function load_modalLarge(header,body,footer)
		{
			$('#load_modal_large .modal-title').html(header);
			$('#load_modal_large .modal-body').load("{{ url('') }}/"+body);

			if(footer != null)
			{
				$('#load_modal_large .modal-footer').html(footer);
			}

			$('#load_modal').modal('hide');
			$('#load_modal_large').modal('show');

		}
		function load_modalExtraLarge(header,body,footer)
		{
			$('#load_modal_xtralarge .modal-title').html(header);
			$('#load_modal_xtralarge .modal-body').load("{{ url('') }}/"+body);

			if(footer != null)
			{
				$('#load_modal_xtralarge .modal-footer').html(footer);
			}

			$('#load_modal').modal('hide');
			$('#load_modal_xtralarge').modal('show');

		}

		function colorpicker(id){
			$('#'+id).ColorPicker({
				onSubmit: function(hsb, hex, rgb, el) {
					$(el).val(hex);
					$(el).ColorPickerHide();
				},
				onBeforeShow: function () {
					$(this).ColorPickerSetColor(this.value);
				}
			})
			.bind('keyup', function(){
				$(this).ColorPickerSetColor(this.value);
			});
		}

		function autocomplete(cls,maxselect,placeholder='')
		{
			if(placeholder == ''){
				placeholder = "Pilih data";
			}
			if(maxselect)
			{
				$("#"+cls).select2({
					placeholder: placeholder,
					allowClear: true,
					maximumSelectionLength: maxselect
				});
			}
			else
			{
				$("#"+cls).select2({
					placeholder: placeholder,
					allowClear: true
				});
			}
		}

		function autocompletebyclass(cls,maxselect,placeholder='')
		{
			if(placeholder == ''){
				placeholder = "Pilih data";
			}
			if(maxselect)
			{
				$("."+cls).select2({
					placeholder: placeholder,
					allowClear: true,
					maximumSelectionLength: maxselect
				});
			}
			else
			{
				$("."+cls).select2({
					placeholder: placeholder,
					allowClear: true
				});
			}
		}

		function autocomplete_remote(id)
		{
			$("#"+id).select2({
				placeholder: "Pilih data",
				minimumInputLength: 3,
				ajax: {
					url: "https://api.github.com/search/repositories",
					dataType: 'json',
					quietMillis: 250,
					data: function (term, page) { // page is the one-based page number tracked by Select2
						return {
							q: term, //search term
							page: page // page number
						};
					},
					results: function (data, page) {
						var more = (page * 30) < data.total_count; // whether or not there are more results available

						// notice we return the value of more so Select2 knows if more results can be loaded
						return { results: data.items, more: more };
					}
				},

				formatResult: repoFormatResult, // omitted for brevity, see the source of this page
				formatSelection: repoFormatSelection, // omitted for brevity, see the source of this page
				dropdownCssClass: "bigdrop", // apply css that makes the dropdown taller
				escapeMarkup: function (m) { return m; } // we do not want to escape markup since we are displaying html in results
			});
		}


		function tablesorter()
		{
			$(function() {
				$(".tablesorter").tablesorter({
					headers : { '.sorterfalse' : { sorter: false } }
				}
				);
			});
		}
		tablesorter();

		function kalender(id,vTime,format='d/m/Y') {

			if(format == null){
				format = 'd/m/Y';
			}
			$(function() {
				if(vTime == null){
					vTime = false;
				}

				if(vTime === true){
					$('#'+id).datetimepicker({
						format:format,
						mask:'39/19/2099',
						required :true
					});
				}
				else
				{
					$('#'+id).datetimepicker({
						format:format,
						timepicker:false,
						mask:'39/19/2099',
						required :true
					});
				}

			});
		}

		function spinner(id,type,value,maxi,mini,stepi)
		{
			// $("#"+id).css("padding-left","10px");
			// $("#"+id).css("margin","-3px");
			// $("#"+id).css("border","none");
			// $("#"+id).css("font-size","18px");

			if(value==null)
			value='';

			if(maxi == null)
			maxi='';

			if(mini==null)
			mini='';

			if(stepi==null)
			stepi='';

			if(type == 'default')
			{
				$(function() {
					$( "#"+id ).spinner({
						min:mini,
						max:maxi,
						step: stepi
					});
				});
			}

			if(type == 'percent')
			{
				$.widget( "ui.pcntspinner", $.ui.spinner, {
					_format: function(value) { return value + '%'; },

					_parse: function(value) { return parseInt(value); }
				});

				$("#"+id).pcntspinner({
					min: 0,
					max: 100,
				step: 10 });
			}

			if(type == 'decimal'){
				$(function() {
					$( "#"+id ).spinner({
						min:mini,
						max:maxi,
						step: stepi,
						numberFormat: "n"
					});

					var current = $( "#"+id ).spinner( "value" );
					Globalize.culture( 'in-ID' );
					$( "#"+id ).spinner( "value", current );
				});
			}

			if(type == 'time')
			{
				$.widget( "ui.timespinner", $.ui.spinner, {
					options: {
						// seconds
						step: 60 * 1000,
						// hours
						page: 60
					},

					_parse: function( value ) {
						if ( typeof value === "string" ) {
							// already a timestamp
							if ( Number( value ) == value ) {
								return Number( value );
							}
							return +Globalize.parseDate( value );
						}
						return value;
					},

					_format: function( value ) {
						return Globalize.format( new Date(value), "t" );
					}
				});

				$(function() {
					$( "#"+id ).timespinner();

					var current = $( "#"+id ).timespinner( "value" );
					Globalize.culture( 'id-ID' );
					$( "#"+id ).timespinner( "value", current );
				});
			}

			if(type == 'currency')
			{
				$(function() {
					$("#"+id ).spinner({
						min: mini,
						max: maxi,
						step: stepi,
						start: 5000,
						numberFormat: "C"
					});
					Globalize.culture('id-ID');
				});
			}

			$("#"+id).val(value);
		}

		function back()
		{
			window.history.back();
		}

		function change_language(language,i18) { // fungsi untuk ubah language
        $('#language').load("{{ url('welcome/change_language') }}/"+language+"/"+i18+"?url={{ url()->current() }}");
		}

		function fixscroll_sidemenu(){
			setTimeout(function() {
				let heightauto_scroll 	= $('.left-side-menu').height();
				if(heightauto_scroll != "0"){
					var countheightreal 	= parseInt(heightauto_scroll)-50;

					var heighttostring		= countheightreal.toString();
					// #Replace Height scroll
					$('.slimscroll-menu').css("height", heighttostring+"px");
				}
			}, 1000);
		}

		$(window).on("resize", function(){
			fixscroll_sidemenu();
		});

		function load_content(url) {
            $("#content_tutorial").html('');

            var cek_logout;

            $(".loading").fadeIn('fast', function () {

                $.ajax({
                    url: "{{ url('welcome/cek_logout') }}",
                    type: "POST",
                    data: {
                        uri: url,
                        _token: "{{ csrf_token() }}"
                    },
                    dataType: "json",
                    success: function (data) {
                        cek_logout = data;
                    }
                })
                .done(function () {

                    if (cek_logout.status == 'login') {

                        if (cek_logout.akses == 'TIDAK') {
                            url = 'c_block';
                        }

                        $(".load_content").load("{{ url('/') }}/" + url, function () {

                            fixscroll_sidemenu();

                            $(".loading").fadeOut('fast');

                            var abc = $('.submenu').val();

                            $("#content_tutorial").load("{{ url('welcome/content_tutorial') }}/" + url);

                        });

                    }
                    else if (cek_logout.status == "migrasi") {

                        window.location.href = "{{ url('/') }}/" + cek_logout.link_migrasi;

                    }
                    else {

                        window.location.href = "{{ url('/') }}";

                    }

                });

            });

            if (url == 'dashboard') {
                //$('body').addClass('bg-white');
            }
            else {
                //$('body').removeClass('bg-white');
            }
        }

		$(document).ready(function() { // fungsi hash untuk load data dengan fungsi load_content

			$(window).on('hashchange', function(e){

				e.preventDefault();
				var hash = window.location.hash.substr(1);
				if(hash)
				{
					load_content(hash);
					if(hash == 'dashboard') showHideToggle(0);
					else showHideToggle(1);
				}
				else{
					load_content('dashboard');
					$("#top_toggle").hide();
				}
			});
			var hash = window.location.hash.substr(1);
			if(hash)
			{
				load_content(hash);
				if(hash == 'dashboard') showHideToggle(0);
				else showHideToggle(1);
			}
			else{
				load_content('dashboard');
				$("#top_toggle").hide();
			}
		});

		function sub(id){
			$('.submenu').val(id);
		}

		function home(){
			let hash = window.location.hash.substr(1);
			if(hash != 'dashboard'){
				$(".mainMenu").hide(1);
				$('.mainContent').removeClass('col-md-10');
				$('.mainContent').removeClass('col-md-12');
				$('.mainContent').addClass('col-md-12');


				$('.metismenu li').removeClass('mm-active');
				$('.metismenu li:first-child').addClass('mm-active');
				$('ul').removeClass('mm-show');

			}
		}
		function homeopen(){
			$(".mainMenu").show(1);
			$('.mainContent').removeClass('col-md-10');
			$('.mainContent').removeClass('col-md-12');
			$('.mainContent').addClass('col-md-10');
		}


		function modul(id)
		{
			$('.mainMenu').empty();
			$(".mainMenu").show(1);
			$('.mainContent').removeClass('col-md-10');
			$('.mainContent').removeClass('col-md-12');
			$('.mainContent').addClass('col-md-10');
			$('a').removeClass('aktif');
			$('#menu'+id).addClass('aktif');

			let hash = window.location.hash.substr(1);

			$('.mainMenu').load( '<?=base_url()?>index.php/welcome/menu/'+id,function(){
				load_content('dashboard');
				// if(id == 0 && hash == 'dashboard'){
				// 	load_content('dashboard');
				// }
			});

			// 2 = akademik
			if(id == '2'){
				$('body').removeClass('top-bar-active');
				$('#alert-after-akademik').hide();
			}
		}
		function cariuser(id){
			$(".mainMenu").hide(1);
			$('.mainContent').removeClass('col-md-10');
			$('.mainContent').removeClass('col-md-12');
			$('.mainContent').addClass('col-md-12');
			$(".mainContent").load( "<?=base_url()?>index.php/welcome/cariuser/"+id);

		}

		function alertfail(note){
			if(note == null)
			var note="Tidak boleh input data yang sudah ada!";

			$( ".alert-error" ).animate({
				backgroundColor: "#ec9b9b"
			}, 1000 );
			$( ".alert-error" ).animate({
				backgroundColor: "#df3d3d"
			}, 1000 );
			$( ".alert-error" ).animate({
				backgroundColor: "#ec9b9b"
			}, 1000 );
			$( ".alert-error" ).animate({
				backgroundColor: "#df3d3d"
			}, 1000 );

			$(".alert-error").show();
			$(".alert-error-content").html(note);
			window.setTimeout(function() { $(".alert-error").slideUp( "slow" ); }, 6000);
		}
		function alertsuccess(note){
			if(note == null)
			var note="Data berhasil disimpan.";

			$( ".alert-success" ).animate({
				backgroundColor: "#dff0d8"
			}, 1000 );
			$( ".alert-success" ).animate({
				backgroundColor: "#b6ef9e"
			}, 1000 );
			$( ".alert-success" ).animate({
				backgroundColor: "#dff0d8"
			}, 1000 );
			$( ".alert-success" ).animate({
				backgroundColor: "#b6ef9e"
			}, 1000 );


			$(".alert-success").show();
			$(".alert-success-content").html(note);
			window.setTimeout(function() { $(".alert-success").slideUp(); }, 10000);
		}

		function silahkantunggu(){
			$(".btnSave").html('<i class="fa fa-spinner fa-spin"></i> Silahkan tunggu...');
			$(".btnSave").attr("disabled","disabled");
		}

		function berhasil(){
			$(".btnSave").html('<?=$this->lang->line('save')?> Data <icon class="icon-check icon-white-t"></icon>');
			$(".btnSave").removeAttr("disabled");
			$("#mdl_body").html("");
		}

		function hitungipkmhs(mhs,t){
			$.ajax({
				type: "POST",
				url: "<?=base_url()?>index.php/c_nilaikrs/hitungipkmhs",
				data: {
					MhswID: mhs,
					TahunID: t
				},
				success: function(data) {
				}
			});
			return false;


		}
		function hitungipk(det,tahun){
			$.ajax({
				type: "POST",
				url: "<?=base_url()?>index.php/c_nilai/hitungipk",
				data: {
					DetailKurikulumID: det,
					TahunID: tahun
				},
				success: function(data) {
				}
			});
			return false;


		}

		function modul_refresh(id)
		{
			$('a').removeClass('aktif');
			$('#menu'+id).addClass('aktif');
			$('.mainMenu').load( '<?=base_url()?>index.php/welcome/menu/'+id	);
		}

		modul_refresh('<?=$this->session->userdata('modulgrup')?>');

		function addZero(i) {
			if (i < 10) {
				i = "0" + i;
			}
			return i;
		}

		function fd() {
			$.ajax({
				type: "POST",
				url: "<?=base_url()?>index.php/welcome/fd",
				success: function(data) {
				}
			});
			return false;
		}

		function rupiah(hasil)
		{
			//format rp;
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
			for(let i = 0; i <ca.length; i++) {
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
	</script>
    <script type="text/javascript">
		function ubahpass(){
			$('#modaljudul').html('<i class="fa fa-lock"></i> Ubah Password');
			$('#modalbody').load( "<?=base_url()?>index.php/welcome/ubahpass");
			$('#load_modal2').modal('show');
		}

		$('#upkeyword').keypress(function (e) {
		var a=$("#upkeyword").val();
		if (e.which == 13) {
			carimodul();
		}
		});
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
