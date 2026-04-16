<div class="table-responsive mt-3">
    <table class="table table-bordered table-hover">
        <thead class="bg-primary text-white">
            <tr>
                <th width="5%">
                    <div class="checkbox checkbox-info">
                        <input type="checkbox" name="checkAll" id="checkAll" onClick="checkall(this, document.getElementsByClassName('checkID'));">
                        <label for="checkAll"></label>
                    </div>
                </th>
                <th width="5%">No.</th>
                <th>NPM</th>
                <th>Nama</th>
                <th>Program Studi</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 0; @endphp
            @foreach($get_mhs as $row)
                <tr>
                    <td>
                        <div class="checkbox checkbox-info">
                            <input type="checkbox" name="checkID[]" id="checkID{{ $no }}" class="checkID" value="{{ $row->ID }}">
                            <label for="checkID{{ $no }}"></label>
                        </div>
                    </td>
                    <td>{{ ++$no }}.</td>
                    <td>{{ $row->NPM }}</td>
                    <td>{{ $row->Nama }}</td>
                    <td>{{ get_field($row->ProdiID, 'programstudi') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

<script>
function checkall(chkAll, checkid) {
    if (checkid != null) {
        if (checkid.length == null) {
            checkid.checked = chkAll.checked;
        } else {
            for (i = 0; i < checkid.length; i++) {
                checkid[i].checked = chkAll.checked;
            }
        }
    }
}
</script>
