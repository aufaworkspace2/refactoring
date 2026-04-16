<p>{!! $total_row ?? '' !!}</p>
<form id="f_delete_mahasiswa" action="{{ url('skpi/delete') }}" >
    @csrf
    <div class="table-responsive">
        <table class="table table-hover table-bordered tablesorter">
            <thead class="bg-primary text-white">
                <tr>
                    <th class="text-center" width="2%">No.</th>
                    <th class="text-center" width="35%">{{ __('app.Nama') }}</th>
                    <th class="text-center" width="10%">{{ __('app.NPM') }}</th>
                    <th class="text-center" width="5%">{{ __('app.TahunMasuk') }}</th>
                    <th class="text-center" width="12%">{{ __('app.ProdiID') }}</th>
                    <th class="text-center" width="3%">Jenjang</th>
                    <th class="text-center" width="6%">Tanggal Kelulusan</th>
                    <th class="text-center" width="10%">No Ijazah</th>
                    <th class="text-center" width="5%">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @php $no = $offset ?? 0; @endphp
                @foreach($query ?? [] as $row)
                    @php $row = (object) $row; @endphp
                    <tr class="mahasiswa_{{ $row->ID ?? '' }}">
                        <td class="text-center">{{ ++$no }}.</td>
                        <td>
                            <div class="media thumbnail">
                                @if(!empty($row->NPM))
                                    <img src="{{ asset('assets/theme/images/default-photo.png') }}" alt="{{ $row->Nama ?? '' }}" style="width: 50px; height: 50px; object-fit: cover; margin-right: 10px;">
                                @endif
                                <div class="media-body">
                                    @if(($Update ?? '') == 'YA')
                                        <a href="{{ url('skpi/view/'.$row->ID) }}">{{ $row->Nama ?? '' }}</a>
                                    @else
                                        {{ $row->Nama ?? '' }}
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="text-center">{{ $row->NPM ?? '' }}</td>
                        <td class="text-center"><span class="badge badge-secondary">{{ $row->TahunMasuk ?? '' }}</span></td>
                        <td>{{ DB::table('programstudi')->where('ID', $row->ProdiID ?? '')->value('Nama') ?? '' }}</td>
                        <td class="text-center">{{ DB::table('jenjang')->where('ID', DB::table('programstudi')->where('ID', $row->ProdiID ?? '')->value('JenjangID'))->value('Nama') ?? ' - ' }}</td>
                        @php
                            $dataWisuda = DB::table('wisudawan')->where('MhswID', $row->ID ?? '')->first();
                        @endphp
                        <td class="text-center">{{ $dataWisuda && $dataWisuda->TanggalLulus ? \Carbon\Carbon::parse($dataWisuda->TanggalLulus)->format('d M Y') : '-' }}</td>
                        <td class="text-center">{{ ($dataWisuda && $dataWisuda->NoIjazah) ? $dataWisuda->NoIjazah : '-' }}</td>
                        <td class="text-center">
                            <button type="button" class="btn btn-primary" onclick="opnmdl('{{ $row->ID ?? '' }}','pdf')">
                                Cetak
                            </button>
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

<script>
// Initialize tablesorter if function exists
if(typeof tablesorter === 'function') {
    tablesorter();
}

// Show delete button function
window.show_btnDelete = function(){
    i = 0; hasil = false;
    var checkElements = document.getElementsByName('checkID[]');
    while(checkElements.length > i) {
        var checkname = document.getElementById('checkID' + i);
        if(checkname && checkname.checked) {
            hasil = true;
        }
        i++;
    }
    if(hasil == true) {
        if($('#btnDelete').length) {
            $('#btnDelete').removeAttr('disabled');
            $('#btnDelete').removeAttr('href');
            $('#btnDelete').removeAttr('title');
            $('#btnDelete').attr('href', '#hapus');
        }
    } else {
        if($('#btnDelete').length) {
            $('#btnDelete').attr('disabled', 'disabled');
            $('#btnDelete').attr('href', '#');
            $('#btnDelete').attr('title', 'Pilih dahulu data yang akan di hapus');
        }
    }
}
show_btnDelete();

// Checkbox click handler
$("input:checkbox[name='checkID[]']").click(function(){
    if(this.checked == true){
        $(this).parents('tr').addClass('checked_tabel');
    } else {
        $(this).parents('tr').removeClass('checked_tabel');
    }
});

// Delete button handler
$('#btnDelete').click(function(){
    $.ajax({
        url: "{{ url('welcome/test') }}?table=mahasiswa&field=Nama",
        type: "POST",
        data: {
            checkID: $("input:checkbox[name='checkID[]']:checked").map(function(){
                return this.value;
            }).get(),
            _token: "{{ csrf_token() }}"
        },
        success: function(data){
            $('.data_name').html(data);
        }
    });
});
</script>
