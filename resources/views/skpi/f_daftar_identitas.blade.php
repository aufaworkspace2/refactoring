@extends('layouts.template1')

@section('content')
@php
    $row = $row ?? [];
    if(is_object($row)) {
        $row = (array) $row;
    }

    $defaultData = [
        'ID' => '',
        'MhswID' => $MhswID ?? '',
        'Persyaratan' => '',
        'Bahasa' => 'Bahasa Indonesia',
        'PendidikanLanjut' => '',
        'StatusProfesi' => '',
        'SistemPenilaian' => '',
        'TanggalKelulusan' => '',
        'NoIjazah' => '',
        'Gelar' => '',
        'LamaStudi' => '',
        'IPK' => '0',
        'SKS' => '0',
    ];

    $row = array_merge($defaultData, $row);

    $save = $save ?? 1;
    $btn = $save == 1 ? 'Tambah' : 'Ubah';
@endphp

<br>
<form id="f_wisudawan" action="{{ url('skpi/save/'.$save) }}" enctype="multipart/form-data" method="POST">
    @csrf
    <input type="hidden" name="MhswID" id="MhswID" value="{{ $MhswID ?? '' }}">
    <input type="hidden" name="ID" id="ID" value="{{ $row['ID'] ?? '' }}">

    <div class="row-fluid">
        <!-- Tabs -->
        <div class="span3">
            <!-- Foto -->
            <div class="row-fluid">
                <div class="span12">
                    <div class="well">
                        @if(!empty($row['NPM']))
                            <img src="{{ asset('assets/theme/images/default-photo.png') }}" alt="{{ $row['Nama'] ?? '' }}" style="width: 100%; height: auto;">
                        @else
                            <img src="{{ asset('assets/theme/images/default-photo.png') }}" alt="Default Photo" style="width: 100%; height: auto;">
                        @endif
                    </div>
                </div>
            </div>
            <!-- End Foto -->

            <ul class="tabs-arrow">
                <li class="active"><a class="glyphicons file" href="#tab-0" data-toggle="tab"><i></i> SKPI</a></li>
                <li class=""><a class="glyphicons user" href="#tab-1" data-toggle="tab"><i></i> Biografi</a></li>
                <li class=""><a class="glyphicons certificate" href="#tab-2" data-toggle="tab"><i></i> Akademik</a></li>
                <li class=""><a class="glyphicons tags" href="#tab-3" data-toggle="tab"><i></i> Capaian Pembelajaran</a></li>
                <li class=""><a class="glyphicons paperclip" href="#tab-4" data-toggle="tab"><i></i> Informasi Tambahan</a></li>
            </ul>

            <button type="submit" class="btn btn-primary btn-block btnSave">{{ __('app.save') }} Data</button>
            <button type="button" onClick="back()" class="btn btn-danger btn-block">{{ __('app.back') }}</button>
        </div>
        <!-- End Tabs -->

        <!-- Tab Content -->
        <div class="span9">
            <div class="tab-content">
                <!-- Tab 0: SKPI -->
                <div class="tab-pane active" id="tab-0">
                    <div class="well form-horizontal">
                        <legend>Data Surat Keterangan Pendamping Ijazah</legend>

                        <div class="control-group">
                            <label class="control-label" for="Persyaratan">Persyaratan Penerimaan *</label>
                            <div class="controls">
                                <input type="text" name="Persyaratan" class="span6 enabled" value="{{ $row['Persyaratan'] ?? '' }}" placeholder='Persyaratan'>
                            </div>
                        </div>

                        <div class="control-group">
                            <label class="control-label" for="Bahasa">Bahasa Pengantar Kuliah</label>
                            <div class="controls">
                                <input type="text" name="Bahasa" readonly class="span6 enabled" value="Bahasa Indonesia">
                            </div>
                        </div>

                        <div class="control-group">
                            <label class="control-label" for="PendidikanLanjut">Pendidikan Lanjut *</label>
                            <div class="controls">
                                <select class="span6 enabled" name="PendidikanLanjut">
                                    @foreach(DB::table('jenjang')->whereNotNull('PendidikanLanjut')->get() as $rew)
                                        @php
                                            $selected = '';
                                            if(isset($row['PendidikanLanjut']) && $row['PendidikanLanjut'] == $rew->PendidikanLanjut) {
                                                $selected = 'selected';
                                            }
                                        @endphp
                                        <option value="{{ $rew->PendidikanLanjut }}" {{ $selected }}>
                                            {{ $rew->Nama }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="control-group">
                            <label class="control-label" for="Status">Status Profesi</label>
                            <div class="controls">
                                <select class="span6 enabled" name="Status" id="Status">
                                    <option value="Ya" {{ ($row['StatusProfesi'] ?? '') == 'Ya' ? 'selected' : '' }}>Ada</option>
                                    <option value="Tidak" {{ ($row['StatusProfesi'] ?? '') == 'Tidak' ? 'selected' : '' }}>Tidak Ada</option>
                                </select>
                            </div>
                        </div>

                        <div class="control-group">
                            <label class="control-label" for="Penilaian">Sistem Penilaian *</label>
                            <div class="controls">
                                <textarea id="Penilaian" name="Penilaian" class="span12 enabled">{{ $row['SistemPenilaian'] ?? '' }}</textarea>
                            </div>
                        </div>

                        <div class="control-group">
                            <label class="control-label" for="TanggalKelulusan">Tanggal Kelulusan *</label>
                            <div class="controls">
                                <input type="text" id="TanggalKelulusan" name="TanggalKelulusan" class="span6 enabled" value="{{ $row['TanggalKelulusan'] ?? '' }}" />
                            </div>
                        </div>

                        <div class="control-group">
                            <label class="control-label" for="NoIjazah">No. Ijazah</label>
                            <div class="controls">
                                <input type="text" class="span6 enabled" name="NoIjazah" id="NoIjazah" value="{{ $row['NoIjazah'] ?? '-' }}">
                            </div>
                        </div>

                        <div class="control-group">
                            <label class="control-label" for="LamaStudi">Lama Studi *</label>
                            <div class="controls">
                                <input type="text" readonly class="span6 enabled" name="LamaStudi" id="LamaStudi" value="{{ $row['LamaStudi'] ?? '-' }}">
                            </div>
                        </div>

                        <div class="control-group">
                            <label class="control-label" for="SKS">Satuan Kredit Semester</label>
                            <div class="controls">
                                <input type="text" readonly class="span6 enabled" name="SKS" value="{{ $row['SKS'] ?? '0' }}">
                            </div>
                        </div>

                        <div class="control-group">
                            <label class="control-label" for="IPK">Indeks Prestasi Kumulatif</label>
                            <div class="controls">
                                <input type="text" readonly class="span6 enabled" name="IPK" value="{{ $row['IPK'] ?? '0' }}">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab 1: Biografi -->
                <div class="tab-pane" id="tab-1">
                    <div class="well form-horizontal">
                        <legend>{{ __('app.personal_detail') }}</legend>
                        <div class="control-group">
                            <label class="control-label" for="NPM">{{ __('app.NPM') }} */{{ __('app.Nama') }} *</label>
                            <div class="controls">
                                <input required type="text" id="NPM" name="NPM" class="span5" value="{{ $row['NPM'] ?? '' }}" />
                                &nbsp;&nbsp;&nbsp;
                                <input type="text" required id="Nama" name="Nama" class="span5" value="{{ $row['Nama'] ?? '' }}" />
                            </div>
                        </div>
                        <!-- Add more biografi fields as needed -->
                    </div>
                </div>

                <!-- Tab 2: Akademik -->
                <div class="tab-pane" id="tab-2">
                    <div class="well form-horizontal">
                        <legend>{{ __('app.academic') }}</legend>
                        <!-- Add academic fields as needed -->
                    </div>
                </div>

                <!-- Tab 3: Capaian Pembelajaran -->
                <div class="tab-pane" id="tab-3">
                    <div class="well form-horizontal">
                        <legend>Capaian Pembelajaran</legend>
                        <!-- Add capaian fields as needed -->
                    </div>
                </div>

                <!-- Tab 4: Informasi Tambahan -->
                <div class="tab-pane" id="tab-4">
                    <div class="well form-horizontal">
                        <legend>Informasi Tambahan</legend>
                        <!-- Add informasi fields as needed -->
                    </div>
                </div>
            </div>
        </div>
        <!-- End Tab Content -->
    </div>
</form>
@endsection

@push('scripts')
<script type="text/javascript">
function back() {
    window.location.href = "{{ url('skpi') }}";
}

// Initialize datepicker if needed
$(document).ready(function() {
    // Add any initialization here
});
</script>
@endpush
