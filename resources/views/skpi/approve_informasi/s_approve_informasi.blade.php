<p>{!! $total_row ?? '' !!}</p>
<form id="f_delete_mahasiswa" action="#" >
    <div class="table-responsive">
        <table class="table table-hover table-bordered tablesorter">
            <thead class="bg-primary text-white">
                <tr>
                    <th class="text-center" width="2%">No.</th>
                    <th class="text-center" width="35%">Nama</th>
                    <th class="text-center" width="10%">NPM</th>
                    <th class="text-center" width="5%">Tahun Masuk</th>
                    <th class="text-center" width="12%">Program Studi</th>
                    <th class="text-center" width="3%">Jenjang</th>
                    <th class="text-center" width="6%">Tanggal Kelulusan</th>
                    <th class="text-center" width="10%">No Ijazah</th>
                    <th class="text-center" width="5%">Aksi</th>
                </tr>
            </thead>
            <tbody>
            @php $no=$offset ?? 0; @endphp
            @foreach(($query ?? []) as $row)
                @php
                    $dataWisuda = DB::table('wisudawan')->where('MhswID', $row['ID'])->first();
                @endphp
                <tr class="mahasiswa_{{ $row['ID'] ?? '' }}">
                    <td class="text-center">{{ ++$no }}.</td>
                    <td>
                        <div class="media thumbnail">
                            <div class="media-body">
                                @if($Update == 'YA')
                                    <a href="#">{{ $row['Nama'] ?? '' }}</a>
                                @else
                                    {{ $row['Nama'] ?? '' }}
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="text-center">{{ $row['NPM'] ?? '' }}</td>
                    <td class="text-center"><span class="label">{{ $row['TahunMasuk'] ?? '' }}</span></td>
                    <td>{{ $row['ProdiID'] ?? '' }}</td>
                    <td class="text-center">{{ $row['JenjangID'] ? 'Sarjana' : '-' }}</td>
                    <td class="text-center">{{ $dataWisuda ? \Carbon\Carbon::parse($dataWisuda->TanggalLulus)->format('d-m-Y') : '-' }}</td>
                    <td class="text-center">{{ $dataWisuda && $dataWisuda->NoIjazah ? $dataWisuda->NoIjazah : '-' }}</td>
                    <td class="text-center">
                        <a href="javascript:void(0)" class="btn btn-success btn-phone-block" onclick="lihat_informasi('{{ $row['ID'] ?? '' }}')">
                            Lihat Informasi Tambahan
                        </a>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>

    <div class="row">
        <div class="col-md-12">
            {!! $link ?? '' !!}
        </div>
    </div>
</form>

<!-- Modal Lihat Informasi -->
<div id="modal_informasi" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modal_informasi" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="modal_informasi_label">Informasi Tambahan</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body" id="modal_informasi_body">
                <!-- Content will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger waves-effect waves-light" data-dismiss="modal">{{ __('app.close') }}</button>
            </div>
        </div>
    </div>
</div>

<script>
tablesorter();

function lihat_informasi(mhswID) {
    $.ajax({
        url: "{{ url('skpi/approveInformasi/lihatInformasi') }}",
        type: "POST",
        data: {
            mhswID: mhswID,
            _token: "{{ csrf_token() }}"
        },
        success: function(data) {
            $('#modal_informasi_body').html(data);
            $('#modal_informasi').modal('show');
        }
    });
}
</script>

<script src="{{ asset('assets/theme/scripts/responsive-table.js') }}"></script>
