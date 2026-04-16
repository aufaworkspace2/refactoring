<div class="table-responsive">
    <table class="table table-hover table-bordered tablesorter">
        <thead class="bg-primary text-white">
            <tr>
                <th class="text-center" width="2%">No.</th>
                <th>{{ __('app.ProdiID') }}</th>
                <th>Daftar Semester Paket</th>
            </tr>
        </thead>
        <tbody>
            @php
                $arr = [];
                $cek = DB::table('paket_sks')->get();
                foreach($cek as $r) {
                    $arr[$r->ProdiID] = $r->SemesterPaket;
                }
                $no = $offset ?? 0;
            @endphp
            @foreach($prodi as $row)
                <tr class="paketsks_{{ $no }}">
                    <td class="text-center">{{ ++$no }}.</td>
                    <td>
                        @if($Update == 'YA')
                            <a href="{{ url('paketsks/view/'.$row->ID) }}">
                                {{ get_field($row->JenjangID, 'jenjang') }} {{ $row->Nama }}
                            </a>
                        @else
                            {{ $row->Nama }}
                        @endif
                    </td>
                    <td>
                        @php
                            $array = explode(',', $arr[$row->ID] ?? '');
                        @endphp
                        @if(!empty($arr[$row->ID]))
                            @foreach($array as $rr)
                                @if(!empty($rr))
                                    <label class='badge badge-secondary'>Semester {{ $rr }}</label>
                                @endif
                            @endforeach
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<script>
tablesorter();
</script>

<script src="{{ asset('assets/theme/scripts/responsive-table.js') }}"></script>
