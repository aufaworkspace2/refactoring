@extends('layouts.template1')

@section('content')
<div class="card">
    <div class="card-body">
        <div class="form-row">
            <div class="form-group col-md-4">
                <label class="col-form-label"><h5 class="mb-0">Gelombang</h5></label>
                <select class="gelombang form-control select2" onchange="change_gelombang_detail_pmb()">
                    <option value=""> -- {{ __('app.view_all') }} -- </option>
                    @foreach($data_gelombang as $g)
                        <option value="{{ $g->id }}" {{ $selected_gelombang == $g->id ? 'selected' : '' }}>
                            {{ $g->kode }} || {{ $g->nama }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-8">
                <label class="col-form-label"><h5 class="mb-0">Gelombang Detail</h5></label>
                <select class="gelombang_detail form-control select2" onchange="filter()">
                    <option value=""> -- {{ __('app.view_all') }} -- </option>
                    @foreach($data_gelombang_detail as $gd)
                        <option value="{{ $gd->id }}"
                            {{ $selected_gelombang_detail == $gd->id ? 'selected' : '' }}
                            data-gelombang-id="{{ $gd->gelombang_id }}">
                            {{ $gd->nama }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-6">
                <label class="col-form-label"><h5 class="mb-0">Urutkan dengan</h5></label>
                <div class="row">
                    <div class="col-md-6">
                        <select name="orderby" class="form-control orderby select2" onchange="filter()">
                            <option value="mahasiswa.urutall_lulus_pmb" {{ $selected_orderby == 'mahasiswa.urutall_lulus_pmb' ? 'selected' : '' }}>Urut Set Lulus</option>
                            <option value="mahasiswa.Nama" {{ $selected_orderby == 'mahasiswa.Nama' ? 'selected' : '' }}>Nama</option>
                            <option value="mahasiswa.noujian_pmb" {{ $selected_orderby == 'mahasiswa.noujian_pmb' ? 'selected' : '' }}>No Ujian</option>
                            <option value="mahasiswa.nilai_pmb" {{ $selected_orderby == 'mahasiswa.nilai_pmb' ? 'selected' : '' }}>Nilai</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <select name="descasc" class="form-control descasc select2" onchange="filter()">
                            <option value="DESC" {{ $selected_descasc == 'DESC' ? 'selected' : '' }}>Z-A</option>
                            <option value="ASC" {{ $selected_descasc == 'ASC' ? 'selected' : '' }}>A-Z</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="form-group col-md-6">
                <label class="col-form-label"><h5 class="mb-0">{{ __('app.keyword_legend') }}</h5></label>
                <div class="row">
                    <div class="col-md-3">
                        <select name="viewpage" class="form-control viewpage select2" onchange="filter()">
                            <option value="10" {{ $selected_viewpage == '10' ? 'selected' : '' }}>10</option>
                            <option value="25" {{ $selected_viewpage == '25' ? 'selected' : '' }}>25</option>
                            <option value="50" {{ $selected_viewpage == '50' ? 'selected' : '' }}>50</option>
                            <option value="100" {{ $selected_viewpage == '100' ? 'selected' : '' }}>100</option>
                            <option value="all" {{ $selected_viewpage == 'all' ? 'selected' : '' }}>-- Semua --</option>
                        </select>
                    </div>
                    <div class="col-md-9">
                        <input type="text" class="form-control keyword" onkeyup="filter()" placeholder="{{ __('app.keyword') }} .."/>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="card">
    <div class="card-body">
        <div id="konten"></div>
    </div>
</div>
@endsection

@push('scripts')
<script type="text/javascript">
$.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

// Initialize Select2
$(document).ready(function() {
    $('.select2').select2({
        placeholder: function(){
            $(this).data('placeholder');
        },
        allowClear: true,
        width: '100%'
    });

    filter();
    // Filter gelombang detail based on selected gelombang
    var selectedGelombang = $('.gelombang').val();
    if (selectedGelombang) {
        filterGelombangDetail(selectedGelombang);
    }
});

function change_gelombang_detail_pmb() {
    var selectedGelombang = $('.gelombang').val();
    if (selectedGelombang) {
        filterGelombangDetail(selectedGelombang);
    } else {
        $('.gelombang_detail').html('<option value=""> -- {{ __('app.view_all') }} -- </option>');
        filter();
    }
}

function filterGelombangDetail(gelombang_id) {
    // Filter options that belong to selected gelombang
    var options = $('.gelombang_detail option').filter(function() {
        return !$(this).attr('value') || $(this).data('gelombang-id') == gelombang_id;
    });

    $('.gelombang_detail').empty().append(options);

    // Re-initialize select2 after updating options
    $('.gelombang_detail').trigger('change');
    filter();
}

function filter(url) {
    if(url == null) url = "{{ url('setregistrasiulang/search') }}";
    $.ajax({
        type: "POST",
        url: url,
        data: {
            gelombang : $(".gelombang").val(),
            gelombang_detail : $(".gelombang_detail").val(),
            program : $(".program").val(),
            orderby : $(".orderby").val(),
            descasc : $(".descasc").val(),
            viewpage : $(".viewpage").val(),
            keyword : $(".keyword").val(),
        },
        beforeSend:function(data){
            $('#konten').html('<center><i class="fa fa-spin fa-spinner"></i> Silahkan Tunggu...</center>');
        },
        success: function(data) {
            $("#konten").html(data);
            
            // Re-initialize select2 after AJAX load
            setTimeout(function() {
                $('.select2').select2({
                    placeholder: function(){ $(this).data('placeholder'); },
                    allowClear: true,
                    width: '100%'
                });
            }, 100);

            // Re-bind pagination click handlers
            $(document).off('click', '.pagination a');
            $(document).on('click', '.pagination a', function(e) {
                e.preventDefault();
                var url = $(this).attr('href');
                if(url) {
                    $.ajax({
                        type: "POST",
                        url: url,
                        data: {
                            gelombang : $(".gelombang").val(),
                            gelombang_detail : $(".gelombang_detail").val(),
                            program : $(".program").val(),
                            orderby : $(".orderby").val(),
                            descasc : $(".descasc").val(),
                            viewpage : $(".viewpage").val(),
                            keyword : $(".keyword").val(),
                        },
                        success: function(data) {
                            $("#konten").html(data);
                            // Re-initialize select2
                            setTimeout(function() {
                                $('.select2').select2({
                                    placeholder: function(){ $(this).data('placeholder'); },
                                    allowClear: true,
                                    width: '100%'
                                });
                            }, 100);
                            
                            // Re-trigger form binding
                            bindFormHandler();
                        }
                    });
                }
            });
            
            // Re-trigger form binding after AJAX load
            bindFormHandler();
        }
    });
    return false;
}

// Function to bind form handler (called after AJAX load)
function bindFormHandler() {
    var $form = $('#f_save_setregistrasiulang');
    
    // Remove any existing handlers to prevent duplicates
    $form.off('submit');
    
    $form.on('submit', function(e){
        e.preventDefault();
        e.stopImmediatePropagation();
        
        console.log('Form submit intercepted via AJAX');
        
        $.ajax({
            type: "POST",
            url: $(this).attr('action'),
            data: $(this).serialize(),
            dataType: 'json',
            beforeSend:function(data){
                $('#btnSubmit').prop('disabled',true);
                $('#btnSubmit').html("<i class='fa fa-spin fa-spinner'></i> Loading...");
            },
            success:function(response_json){
                let data = response_json.statuspesan;
                if(data == 'tidak ada tagihan'){
                    swal('Gagal','Tidak Dapat set registrasi ulang karena belum ada biaya yang di setting sesuai gelombang mahasiswa','warning');
                    filter("{{ url('setregistrasiulang/search') }}");
                    return;
                }

                if(data == 'diskon double'){
                    swal('Gagal',response_json.message,'warning');
                    filter("{{ url('setregistrasiulang/search') }}");
                    return;
                }

                $("#hapus").modal("hide");

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
                $(".alert-success-content").html("Data berhasil disimpan");
                window.setTimeout(function() { $(".alert-success").slideUp(); }, 10000);
                filter("{{ url('setregistrasiulang/search') }}");
            },
            error: function(xhr, status, error){
                console.error('AJAX Error:', status, error);
                
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
                $(".alert-error-content").html("Terjadi kesalahan saat menyimpan data");
                window.setTimeout(function() { $(".alert-error").slideUp( "slow" ); }, 6000);
                filter("{{ url('setregistrasiulang/search') }}");
            }
        });
        
        return false;
    });
    
    // Re-initialize button state
    if(typeof show_btnSubmit === 'function') {
        show_btnSubmit();
    }
}

$('.keyword').keyup(fncDelay(function (e) {
    filter();
}, 500));

function checkall(chkAll,checkid) {
    if (checkid != null) {
        if (checkid.length == null) checkid.checked = chkAll.checked;
        else for (i=0;i<checkid.length;i++) checkid[i].checked = chkAll.checked;
        $("input:checkbox[name='checkID[]']").parents('tr').removeClass('table-danger');
        $("input:checkbox[name='checkID[]']:checked").parents('tr').addClass('table-danger');
    }
}

function show_btnSubmit(){
    i=0; hasil = false;
    while(document.getElementsByName('checkID[]').length > i) {
        var checkname = document.getElementById('checkID'+i);
        if(checkname && checkname.checked){ hasil = true; }
        i++;
    }
    var action_do = $('#action_do').val();
    if(hasil == true && action_do != '') {
        $('#btnSubmit').removeAttr('disabled');
        $('#btnSubmit').removeAttr('title');
    }
    else
    {
        $('#btnSubmit').attr('disabled','disabled');
        $('#btnSubmit').attr('title', 'Pilih dahulu data yang akan di simpan');
    }
}

// Initialize checkbox click handlers (global)
$(document).off('click', "input:checkbox[name='checkID[]']");
$(document).on('click', "input:checkbox[name='checkID[]']", function(){
    if(this.checked == true){ $(this).parents('tr').addClass('table-danger'); }
    else { $(this).parents('tr').removeClass('table-danger'); }
    show_btnSubmit();
});

// Initial call
show_btnSubmit();
</script>
@endpush
