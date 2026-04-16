<div class="table-responsive">
    <table class="table table-bordered mb-0 table-hover">
        <thead class="bg-primary text-white">
            <tr>
                <td width="50%;">Channel Pembayaran</td>
                <td width="50%;">Status</td>
            </tr>
        </thead>
        <tbody>
            @if(!empty($mhsw->channel_pembayaran_formulir_pmb))
                @php
                $currentChannel = \DB::table('channel_pembayaran')
                    ->where('ID', $mhsw->channel_pembayaran_formulir_pmb)
                    ->first();
                @endphp
                <tr>
                    <td>
                        <strong>Channel Saat Ini:</strong><br>
                        {{ $currentChannel->Nama ?? '-' }}
                    </td>
                    <td>
                        @if($mhsw->statusbayar_pmb == 1)
                            <span class="badge badge-success">Sudah Bayar</span>
                        @else
                            <span class="badge badge-warning">Belum Bayar</span>
                        @endif
                    </td>
                </tr>
            @else
                <tr>
                    <td colspan="2" class="text-center">Belum ada channel pembayaran yang dipilih</td>
                </tr>
            @endif
            
            @if(!empty($channels))
                <tr>
                    <td colspan="2">
                        <hr>
                        <h6>Daftar Channel Pembayaran Tersedia:</h6>
                        <ul>
                            @foreach($channels as $channel)
                                <li>{{ $channel->Nama ?? '' }}</li>
                            @endforeach
                        </ul>
                    </td>
                </tr>
            @endif
        </tbody>
    </table>
</div>

<div class="alert alert-info mt-3">
    <i class="fa fa-info-circle"></i> 
    Untuk mengubah channel pembayaran, silakan hubungi administrator PMB.
</div>
