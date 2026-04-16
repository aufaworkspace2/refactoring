<p>{!! $total_row ?? '' !!}</p>

<div class="table-responsive">
    <table class="table table-hover table-bordered tablesorter">
        <thead class="bg-primary text-white">
            <tr>
                <th class="text-center" width="2%">No.</th>
                <th class="text-center" width="35%">Nama</th>
                <th class="text-center" width="10%">NPM</th>
                <th class="text-center" width="10%">Status Mahasiswa</th>
                <th class="text-center">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @php $no = $offset ?? 0; @endphp
            @foreach($query ?? [] as $row)
                @php $row = (object) $row; @endphp
                <tr class="mahasiswa_{{ $row->ID ?? '' }}">
                    <td class="text-center">{{ ++$no }}.</td>
                    <td>
                        <div class="media">
                            <div class="img">
                                {!! get_photo($row->NPM ?? '', $row->Foto ?? '', $row->Kelamin ?? '', 'mahasiswa') !!}
                            </div>
                            <div class="media-body align-self-center ml-2">
                                {{ $row->Nama ?? '' }}
                            </div>
                        </div>
                    </td>
                    <td class="text-center">{{ $row->NPM ?? '' }}</td>
                    <td class="text-center">{{ get_field($row->StatusMhswID ?? '', 'statusmahasiswa') }}</td>
                    <td width="10%" class="text-center">
                        <div class="btn-group">
                            <button class="btn btn-info waves-effect waves-light dropdown-toggle" data-toggle="dropdown">
                                <i class="fa fa-pencil"></i> Edit Nilai
                                <span class="mdi mdi-chevron-down"></span>
                            </button>
                            <div class="dropdown-menu">
                                <a href="javascript:void(0);" class="dropdown-item" onclick="edit_khs({{ $row->ID ?? '' }})"><i class="fa fa-list"></i> Lihat KHS</a>
                                <a href="javascript:void(0);" class="dropdown-item" onclick="edit_transkrip({{ $row->ID ?? '' }})"><i class="fa fa-list"></i> Lihat Transkrip</a>
                            </div>
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<!-- End Categories Table -->
{!! $link ?? '' !!}

<script>
    function edit_transkrip(ID) {
        window.open("{{ url('transkripmahasiswa/edit_transkrip') }}/" + ID, "_blank");
    }

    function edit_khs(ID) {
        window.open("{{ url('transkripmahasiswa/edit_khs') }}/" + ID, "_blank");
    }
</script>
