<div class="form-group">
    <label for="nomor_skpi">Nomor Seri Ijazah</label>
    <input type="hidden" id="mhwsID" value="{{ $mhwsID ?? '' }}">
    <input type="text" class="form-control" id="nomor_skpi" name="nomor_skpi" 
           value="{{ DB::table('number_seri_ijazah')->where('MhswID', $mhwsID ?? '')->value('NoSKPI') ?? '' }}"
           placeholder="Masukkan Nomor Seri Ijazah">
</div>
