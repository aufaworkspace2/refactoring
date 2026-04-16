<div class="row mb-2">
    <div class="col-md-12">
        {!! $total_row !!}
    </div>
</div>
<form id="f_delete_jadwal_usm_pmb" action="{{ url('cetakperprodi_pmb/delete') }}" >
    <div class="table-responsive">
        <table class="table table-bordered mb-0 table-hover tablesorter">
            <thead class="bg-primary text-white">
                <tr>
                    <th class="text-center" width="2%">No.</th>
                    <th width="50%">Programstudi</th>
                    <th width="10%">Jumlah Mahasiswa</th>
                    <th width="10%">Action</th>
                </tr>
            </thead>
            <tbody>
                @php $no=$offset; $i=0; @endphp
                @foreach($datalist as $row)
                    <tr class="jadwal_usm_pmb_{{ $row['id'] ?? '' }}">
                        <td class="text-center">{{ ++$no }}.</td>
                        <td class="align-middle">{{ $row['nama'] ?? '' }}</td>
                        <td class="text-center align-middle">{{ $row['jumlah'] ?? 0 }}</td>
                        <td class="text-center" style="text-align:center">
                            @if($row['jumlah'] > 0)
                                @foreach($row['cetak'] as $cetak)
                                    <a style="cursor: pointer;" onclick="cetak('{{ $row['id'] }}','{{ $gelombang ?? '' }}','{{ $cetak['awal'] }}','{{ $cetak['akhir'] }}')" target="_blank" class="badge badge-success text-white">
                                        {{ $cetak['textawal'] }} - {{ $cetak['textakhir'] }}
                                    </a><br>
                                @endforeach
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</form>

<script>
$.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
tablesorter();
</script>
