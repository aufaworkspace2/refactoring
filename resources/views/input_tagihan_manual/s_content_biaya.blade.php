@foreach($biaya as $row)
    <tr>
        <td>
            {{ $row['Nama'] }}

            @php
                $i = 0;
                $count = count($get_detail[$row['ID']] ?? []);
            @endphp

            @foreach ($get_detail[$row['ID']] ?? [] as $ro)
                @if($i == 2)
                    <div id="detailjenis_{{ $row['ID'] }}" class="toggle_jenis" style="display: none;">
                @endif

                <br/><b>{{ $ro->Nama }}</b><br/>
                <input type="text" class="form-control currency" name="jumlahdetail[{{ $row['ID'] }}][{{ $ro->ID }}]"
                       id="isib_{{ $row['ID'] }}" class="isib_{{ $row['ID'] }} currency"
                       onkeyup="total(this.value, {{ $row['ID'] }}, {{ $ro->ID }})" placeholder="Biaya"
                       value="{{ $tmp_biaya_det[$row['ID']][$ro->ID] ?? '' }}">
                <input type="hidden" name="biayadetail[{{ $row['ID'] }}][]" value="{{ $ro->ID }}">
                <input type="hidden" name="biaya[{{ $ro->ID }}]" value="{{ $ro->ID }}">

                @php $i++; @endphp

                @if($i == $count && $count > 2)
                    </div>
                    <a style="text-decoration: underline;" id="toggle_{{ $row['ID'] }}" onclick="toggle_detail({{ $row['ID'] }})">Tampilkan lebih banyak</a>
                @endif
            @endforeach
        </td>
        <td>
            <input type="hidden" name="biaya[{{ $row['ID'] }}]" value="{{ $row['ID'] }}">
            <input type="text" class="currency jumlah_semua form-control" onkeyup="total()"
                   id="jumlah_{{ $row['ID'] }}" name="jumlah[{{ $row['ID'] }}]"
                   {{ $count > 0 ? 'readonly' : '' }} value="{{ $row['JumlahTagihan'] }}">
        </td>
    </tr>
@endforeach

@if(count($biaya) == 0)
    <tr>
        <td class="text-center" colspan="2">
            Biaya Belum di Set Sesuai Filter. Untuk Setting Klik
            <a target="_blank" href="{{ url('biaya/?ProgramID=' . $ProgramID . '&ProdiID=' . $ProdiID . '&TahunMasuk=' . $TahunMasuk . '&JenisPendaftaran=' . $JenisPendaftaran . '&JalurPendaftaran=' . $JalurPendaftaran) }}">Disini</a>
        </td>
    </tr>
@endif

<script>
$('.currency').mask('#.##0', {reverse: true});

function total(val, JenisBiayaID, Jenisbiayadetail) {
    if (JenisBiayaID) {
        var sum = 0;
        $(".isib_" + JenisBiayaID).each(function() {
            var value = $(this).cleanVal();
            if (!isNaN(value) && value.length != 0) {
                sum += parseFloat(value);
            }
        });
        $("#jumlah_" + JenisBiayaID).val(sum);
    }

    var value_total = 0;
    $(".jumlah_semua").each(function() {
        var total_value = $(this).cleanVal();
        if (!isNaN(total_value) && total_value.length != 0) {
            value_total += parseInt(total_value);
        }
    });

    $("#total_tagihan").val(value_total);
    $("#total_tagihan").trigger('input');
}

total();

var biaya = @json($biaya);

$(document).ready(function() {
    if (biaya != null && Object.keys(biaya).length > 0) {
        $('#buttonGenerate2').prop('disabled', false);
    } else {
        $('#buttonGenerate2').prop('disabled', true);
    }
});

function toggle_detail(id) {
    $('#detailjenis_' + id).toggle();
    $('#toggle_' + id).text(function(i, text) {
        return text === "Tampilkan lebih banyak" ? "Tampilkan lebih sedikit" : "Tampilkan lebih banyak";
    });
}
</script>
