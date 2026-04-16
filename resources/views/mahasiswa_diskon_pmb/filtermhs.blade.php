<div class="card mb-3">
    <div class="card-header">
        <h5 class="mb-0">Filter Mahasiswa</h5>
    </div>
    <div class="card-body">
        <form id="f_filter" onsubmit="loadMahasiswa(); return false;">
            <div class="form-row">
                <div class="form-group col-md-3">
                    <label class="col-form-label">Tahun Masuk</label>
                    <select name="TahunMasuk[]" class="form-control" multiple>
                        <!-- Options will be loaded -->
                    </select>
                </div>
                
                <div class="form-group col-md-3">
                    <label class="col-form-label">Gelombang</label>
                    <select name="gelombang" class="form-control" onchange="loadGelombangDetail()">
                        <option value="">-- Pilih --</option>
                    </select>
                </div>
                
                <div class="form-group col-md-3">
                    <label class="col-form-label">Gelombang Detail</label>
                    <select name="gelombang_detail" class="form-control">
                        <option value="">-- Pilih --</option>
                    </select>
                </div>
                
                <div class="form-group col-md-3">
                    <label class="col-form-label">Keyword</label>
                    <input type="text" name="keyword" class="form-control" placeholder="No. Ujian / Nama">
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary">
                <i class="fa fa-search"></i> Filter
            </button>
        </form>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header">
        <h5 class="mb-0">Daftar Mahasiswa</h5>
    </div>
    <div class="card-body">
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th width="2%">
                        <input type="checkbox" id="checkAll" onclick="toggleCheckAll()">
                    </th>
                    <th>No. Ujian</th>
                    <th>Nama</th>
                    <th>Program</th>
                    <th>Prodi</th>
                    <th class="text-right">Total Tagihan</th>
                    <th class="text-right">Total Diskon</th>
                </tr>
            </thead>
            <tbody>
                @foreach($get_mhs ?? [] as $mhs)
                    @php $mhs = (object) $mhs; @endphp
                    <tr>
                        <td>
                            <input type="checkbox" name="checkID[]" value="{{ $mhs->ID }}" class="mahasiswa-check">
                        </td>
                        <td>{{ $mhs->noujian_pmb ?? '' }}</td>
                        <td>{{ $mhs->Nama ?? '' }}</td>
                        <td>{{ $mhs->programNama ?? '-' }}</td>
                        <td>{{ $mhs->prodiNama ?? '-' }}</td>
                        <td class="text-right">{{ number_format($mhs->JumlahTagihan ?? 0, 0, ',', '.') }}</td>
                        <td class="text-right">{{ number_format($mhs->JumlahDiskon ?? 0, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Jenis Biaya & Diskon</h5>
    </div>
    <div class="card-body">
        <table class="table table-bordered" id="diskon-table">
            <thead>
                <tr>
                    <th>Jenis Biaya</th>
                    <th>Master Diskon</th>
                    <th>Nominal (Rp)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($query_jenisbiaya ?? [] as $jb)
                    @php $jb = (object) $jb; @endphp
                    <tr>
                        <td>
                            {{ $jb->Nama }}
                            <input type="hidden" name="JenisBiayaID[]" value="{{ $jb->ID }}">
                        </td>
                        <td>
                            <select name="MasterDiskonID[{{ $jb->ID }}]" class="form-control" onchange="changenominal(this.value, this)">
                                <option value="">-- Pilih --</option>
                                @foreach($diskon ?? [] as $md)
                                    @php $md = (object) $md; @endphp
                                    <option value="{{ $md->ID }}">
                                        {{ $md->Nama }}
                                    </option>
                                @endforeach
                            </select>
                            <input type="hidden" name="DiscountID[{{ $jb->ID }}]" class="DiscountID">
                        </td>
                        <td>
                            <input type="number" name="Nominal[{{ $jb->ID }}]" class="form-control nominal-input" value="0">
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@push('scripts')
<script>
function toggleCheckAll() {
    var checked = $('#checkAll').is(':checked');
    $('.mahasiswa-check').prop('checked', checked);
}

function loadGelombangDetail() {
    // Load gelombang detail based on selected gelombang
}
</script>
@endpush
