@extends('layouts.template1')

@section('content')
<div class="card">
    <div class="card-body">
        <form id="f_deposit_mahasiswa" action="{{ route('deposit_mahasiswa.save', $save) }}" method="POST">
            @csrf
            <input type="hidden" name="ID" id="ID" value="{{ $row->ID ?? '' }}">

            <h3>Deposit Mahasiswa</h3>

            @if($save == 1)
                <div class="form-row">
                    <div class="form-group col-md-12">
                        <label class="col-form-label">Pilih Mahasiswa *</label>
                        <div class="controls">
                            <select id="MhswID" class="MhswID form-control" name="MhswID" required>
                            </select>
                        </div>
                    </div>
                    <div class="form-group col-md-12">
                        <label class="col-form-label">Masukan Jumlah Saldo *</label>
                        <div class="controls">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">Rp.</span>
                                </div>
                                <input type="text" id="DepositBaru" required name="DepositBaru" class="form-control currency" />
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            @if($save == 2)
                <div class="form-row">
                    @php
                        $mhsw = DB::table('mahasiswa')->where('ID', $row->MhswID)->first();
                    @endphp
                    <div class="form-group col-md-12">
                        <label class="col-form-label">NIM</label>
                        <div class="controls">
                            <input type="text" readonly disabled value="{{ $mhsw->NPM ?? '' }}" class="form-control" />
                        </div>
                    </div>

                    <div class="form-group col-md-12">
                        <label class="col-form-label">Nama Mahasiswa</label>
                        <div class="controls">
                            <input type="text" readonly disabled value="{{ $mhsw->Nama ?? '' }}" class="form-control" />
                        </div>
                    </div>

                    <div class="form-group col-md-12">
                        <label class="col-form-label">Saldo Saat Ini</label>
                        <div class="controls">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">Rp.</span>
                                </div>
                                <input type="hidden" id="Deposit_lama" name="Deposit_lama" class="form-control" value="{{ $row->Deposit ?? 0 }}" />
                                <input type="text" readonly disabled value="Rp. {{ number_format($row->Deposit ?? 0, 2, ',', '.') }}" class="form-control" />
                            </div>
                        </div>
                    </div>

                    <div class="form-group col-md-12" id="Form_Deposit" style="display: none;">
                        <label class="col-form-label">Masukan Saldo Baru *</label>
                        <div class="controls">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">Rp.</span>
                                </div>
                                <input type="text" id="DepositBaru" name="DepositBaru" class="form-control currency" disabled />
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            @if($save == 2)
                <button onClick="btnEdit({{ $save }}, 1)" type="button" class="btn btn-bordered-success waves-effect width-md waves-light btnEdit">Ubah Data</button>
                <button type="submit" class="btn btn-bordered-primary waves-effect width-md waves-light btnSave" style="display: none;">Simpan Data</button>
                <button type="button" onClick="history.back()" class="btn btn-bordered-danger waves-effect width-md waves-light">Kembali</button>
            @else
                <button type="submit" class="btn btn-bordered-primary waves-effect width-md waves-light">Simpan Data</button>
                <button type="button" onClick="history.back()" class="btn btn-bordered-danger waves-effect width-md waves-light">Kembali</button>
            @endif
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script type="text/javascript">
$.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

$("#Form_Deposit").hide();
$('.currency').mask('#.##0', {reverse: true});
$('.currency').trigger('input');

@if($save == 1)
    $('#MhswID').select2({
        ajax: {
            url: '{{ route("deposit_mahasiswa.jsonMahasiswa") }}',
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return {
                    q: params.term,
                    page: params.page || 1,
                    ProgramID: $(".ProgramID").val(),
                    ProdiID: $(".ProdiID").val()
                };
            },
            processResults: function (data, params) {
                params.page = params.page || 1;
                return {
                    results: data.items,
                    pagination: {
                        more: (params.page * 30) < data.total_count
                    }
                };
            },
            cache: true
        }
    });
@endif

$("#f_deposit_mahasiswa").submit(function(e){
    e.preventDefault();

    var form = $(this);

    $.ajax({
        type: 'POST',
        url: form.attr('action'),
        data: form.serialize(),
        beforeSend: function(){
            swal({
                title: 'Sedang memproses...',
                buttons: false,
                timer: 1000,
                showConfirmButton: false
            });
        },
        success: function(data) {
            if(data.status == 1) {
                alertsuccess(data.message);
                setTimeout(function() {
                    @if($save == 1)
                        window.location = "{{ route('deposit_mahasiswa.index') }}";
                    @else
                        window.location = "{{ route('deposit_mahasiswa.view', $row->ID) }}";
                    @endif
                }, 1000);
            } else {
                swal('Pemberitahuan', data.message, 'error');
            }
        },
        error: function(){
            alertfail('Kesalahan jaringan, cobalah beberapa saat lagi.');
        }
    });
});

function btnEdit(type, checkid) {
    if (checkid == 1) {
        $("input:text").removeAttr('disabled');
        $("select").removeAttr('disabled');
        $(".btnEdit").fadeOut(0);
        $(".btnSave").fadeIn(0);
        $("#Form_Deposit").show();
    }
}

// Only call btnEdit for add mode (save=1), not for view mode
@if($save == 1)
    btnEdit({{ $save }});
@endif
</script>
@endpush
