@php
    $color_now = '';
@endphp
@foreach($opsi as $row)
    @php
        if($color_now != $row['Color']) {
            $bordertop = "border-top:1.2px solid " . $row['Color'] . ";";
            $color_now = $row['Color'];
        } else {
            $bordertop = '';
        }

        $edit = 1;
    @endphp
    <tr>
        <td style="border-left:1.2px solid {{ $row['Color'] }};{{ $bordertop }}">
            {{ str_replace("_", " ", $row['Nama']) }}
        </td>
        <td style="border-right:1.2px solid {{ $row['Color'] }};{{ $bordertop }}">
            <input type="hidden" name="opsi[{{ $row['Nama'] }}]" value="{{ $row['Nama'] }}">
            @if($edit == 1)
                <select class="jumlah_semua form-control" id="jumlah_{{ $row['Nama'] }}" name="jumlah[{{ $row['Nama'] }}]" required>
                    <option value="0">{{ __('app.view_all') }}</option>
                    @foreach($komp_opsi[$row['Nama']] as $key => $ko)
                        @php
                            $s = ($key == $row['Jenis']) ? 'selected' : '';
                        @endphp
                        <option value="{{ $key }}" {{ $s }}>
                            {{ ucwords(strtolower(str_replace("_", " ", $ko))) }}
                        </option>
                    @endforeach
                </select>
            @else
                <input type="hidden" class="jumlah_semua form-control" id="jumlah_{{ $row['Nama'] }}" name="jumlah[{{ $row['Nama'] }}]" value="{{ $row['Jenis'] }}">
                @foreach($komp_opsi[$row['Nama']] as $key => $ko)
                    @php
                        $s = ($key == $row['Jenis']) ? 'selected' : '';
                    @endphp
                    @if($s)
                        <strong>{{ ucwords(strtolower(str_replace("_", " ", $ko))) }}</strong>
                    @endif
                @endforeach
            @endif
        </td>
    </tr>
@endforeach

