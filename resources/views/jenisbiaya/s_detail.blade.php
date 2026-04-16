<table width="100%" class="table table-hover table-bordered table-responsive block tablesorter">
    <thead>
        <tr>
            <th class="center" width="2%">No.</th>
            <th style="text-align:center">{{ __('app.Nama') }}</th>
        </tr>
    </thead>
    <tbody>
        @php $no = 0; @endphp
        @foreach($query as $row)
            <tr class="jenisbiaya_{{ $row->ID }}">
                <td class="center">{{ ++$no }}.</td>
                <td>{{ $row->Nama }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
