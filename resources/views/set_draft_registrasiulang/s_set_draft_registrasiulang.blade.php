<form id="f_save_set_draft_registrasiulang" method="POST" action="{{ url('set_draft_registrasiulang/save') }}" >
	@csrf

<div class="form-row">
	<div class="form-group col-md-4">
		<select name="action_do" id="action_do" class="form-control" onchange="show_btnSubmit()"  >
			<option value="">Pilih Aksi</option>
			<option value="registrasi">Set Tagihan</option>
			<option value="tidakregistrasi">Set Tidak Dapat tagihan</option>
			<option value="batalregistrasi">Batal Set Tagihan </option>
		</select>
	</div>
	<div class="col-md-8">
		<button disabled id="btnSubmit" name="act" type="submit" class="btn btn-bordered-success waves-effect waves-light small"> Submit </button>
	</div>
</div>
<p>{!! $total_row !!}</p>
<div class="table-responsive">
    <table class="table table-bordered mb-0 table-hover tablesorter">
		<thead class="bg-primary text-white">
			<tr>
			@if($Update == 'YA')
				<th width="2%">
					<div class="checkbox checkbox-info">
						<input type="checkbox" name="checkAll" id="checkAll" onClick="checkall(this,document.forms.namedItem('f_save_set_draft_registrasiulang')); show_btnSubmit();">
						<label for="checkAll"></label>
					</div>
				</th>
			@endif
			  	<th class="text-center" width="1%">No.</th>
				<th width="15%">No Ujian</th>
				<th width="30%">Nama</th>
				<th width="14%">Pilihan</th>
				<th width="10%">Program</th>
				<th class="text-center">Lulus</th>
				<th class="text-center">Draft Tagihan</th>
				<th class="text-center">Detail Draft Tagihan</th>
			</tr>
		</thead>
		<tbody>
		@php $no=$offset; $i=0; @endphp
		@foreach($query as $row)
			@php $row = (array) $row; @endphp
			<tr class="set_draft_registrasiulang_{{ $row['ID'] }}">
			@if($Update == 'YA')
				<td class="align-middle">
					@if($row['statusregistrasi_pmb'] != 1)
						<div class="checkbox checkbox-info">
							<input type="checkbox" name="checkID[]" id="checkID{{ $i }}" onclick="show_btnSubmit()" value="{{ $row['ID'] }}" >
							<label for="checkID{{ $i }}"></label>
						</div>
					@php $i++; @endphp
					@else
						-
					@endif
				</td>
			@endif
				<td class="text-center">{{ ++$no }}.</td>
				<td class="text-center">{{ $row['noujian_pmb'] ?? '' }}</td>
				<td>
					{{ $row['Nama'] ?? '' }}
				</td>
				<td>
					@php
					$pilihanprodilulus = $all_prodi[$row['prodilulus_pmb'] ?? ''] ?? null;
					$pilihan1 = $all_prodi[$row['pilihan1'] ?? ''] ?? null;
					$pilihan2 = $all_prodi[$row['pilihan2'] ?? ''] ?? null;
					$pilihan3 = $all_prodi[$row['pilihan3'] ?? ''] ?? null;
					@endphp
					@if($row['prodilulus_pmb'] && $pilihanprodilulus)
					Lulus : {{ $pilihanprodilulus['NamaJenjang'] ?? '' }} {{ $pilihanprodilulus['Nama'] ?? '' }}<br>
					@endif
					1. {{ $pilihan1['NamaJenjang'] ?? '' }} {{ $pilihan1['Nama'] ?? '' }}
					@if($row['pilihan2'] && $pilihan2)
					<br>2. {{ $pilihan2['NamaJenjang'] ?? '' }} {{ $pilihan2['Nama'] ?? '' }}
					@endif
					@if($row['pilihan3'] && $pilihan3)
					<br>3. {{ $pilihan3['NamaJenjang'] ?? '' }} {{ $pilihan3['Nama'] ?? '' }}
					@endif
				</td>

				<td>
					{{ $row['programNama'] ?? '-' }}
					<input type="hidden" name="idpend[]" value="{{ $row['ID'] }}" />
				</td>

			<td>
			{!! $row['statuslulus_str'] ?? '' !!} <br>
			</td>

			<td>
			{!! $row['statusdraftregistrasi_str'] ?? '' !!} <br>
			</td>
			<td>
				@if($row['statusdraftregistrasi_pmb'] == 1)
				<a class="btn btn-info waves-effect waves-light" href="javascript:void(0);" onclick="load_modalLarge('Detail Draft Tagihan {{ $row['noujian_pmb'] ?? '' }}','{{ url('set_draft_registrasiulang/detail_draft/' . $row['ID']) }}')">
				Detail
				</a>
				@else
					-
				@endif
			</td>

			</tr>
		@endforeach

		</tbody>
	</table>
    <div id="hapus" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="hapus" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title" id="hapus">{{ __('app.confirm_header') }}</h4>
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				</div>
				<div class="modal-body">
					<p>{{ __('app.confirm_message') }}</p>
					<p class="data_name"></p>
				</div>
				<div class="modal-footer">
					<button type="submit" class="btn btn-danger waves-effect" >{{ __('app.delete') }}</button>
					<button type="button" class="btn btn-primary waves-effect waves-light" data-dismiss="modal">{{ __('app.close') }}</button>
				</div>
			</div>
		</div>
	</div>

	</div>
	<div class="row">
		<div class="col-md-12">
			{!! $link !!}
		</div>
	</div>
</form>
