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
    @endphp
    <tr>
        <td style="border-left:1.2px solid {{ $row['Color'] }};{{ $bordertop }}">
            {{ str_replace("_", " ", $row['Nama']) }}
        </td>
        <td style="border-right:1.2px solid {{ $row['Color'] }};{{ $bordertop }}">
            <input type="hidden" name="opsi[{{ $row['Nama'] }}]" value="{{ $row['Nama'] }}">
            <div class="input-group">
                <input type="number" class="jumlah form-control" value="{{ $row['Jumlah'] ?? '' }}" id="jumlah_{{ $row['Nama'] }}" name="jumlah[{{ $row['Nama'] }}]" required>
                <div class="input-group-append">
                    <span class="input-group-text">Hari</span>
                </div>
            </div>
        </td>
    </tr>
@endforeach
