<div class="table-responsive">
	<table class="table table-bordered mb-0 table-hover tablesorter">
		<thead class="bg-primary text-white">
			<tr>
				<th rowspan="2" class="text-center align-middle" width="2%">No.</th>
				<th rowspan="2" class="align-middle" width="60%">Program Studi</th>
				<th rowspan="2" class="align-middle" width="60%">Program</th>
				<th rowspan="2" class="align-middle" width="50%">Jalur Pendaftaran</th>
				<th colspan='1'>Jumlah Peserta</th>
			</tr>
			<tr>
				<td>Pilihan 1</td>
			</tr>
		</thead>
		<tbody>
		<?php $no = $offset ?? 0; $i = 0; $total = 0; ?>
		@php $arr_prodi = []; $arrProdi = []; @endphp
		@foreach($query as $row)
			<?php $total += $row['jumlah'] ?? 0 ?>
			<tr class="agama_{{ $row['ID'] ?? '' }}">
			@if(!in_array($row['pilihan1'], $arr_prodi))
				<td rowspan="{{ $rowprodi[$row['pilihan1']] ?? 1 }}" class="text-center">{{ ++$no }}.</td>
				<td rowspan="{{ $rowprodi[$row['pilihan1']] ?? 1 }}">
				@if($row['pilihan1'] ?? '')
					@foreach(explode(",", $row['pilihan1']) as $key => $value)
						@if(!isset($arrProdi[$value]))
							<?php
								$getprodi = get_id($value,'programstudi');
								$arrProdi[$value] = get_field($getprodi->JenjangID ?? '',"jenjang")." ".($getprodi->Nama ?? '');
							?>
						@endif
						{{ $arrProdi[$value] }}<br>
					@endforeach
				@endif
				</td>
				<?php $arr_prodi[] = $row['pilihan1']; ?>
			@endif
				<td>{{ get_field($row['ProgramID'] ?? '', 'program') }}</td>
				<td>{{ get_field($row['jalur_pmb'] ?? '','pmb_edu_jalur_pendaftaran','nama') }}</td>
				<td>{{ $row['jumlah'] ?? '' }}</td>
			</tr>
			@endforeach
			<tr>
				<th colspan='4' style="text-align:right">Total</th>
				<th>{{ $total }}</th>
			</tr>
		</tbody>
	</table>
</div>

<script>
tablesorter();
$("form").submit(function(){
		$.ajax({
		type: "POST",
		url: $("form").attr('action'),
		data: $("form").serialize(),
		success:function(data){
			$("#hapus").modal("hide");
			setTimeout(function(){
				$("body").removeClass("modal-open");
				$(".modal-backdrop").remove();
			}, 300);
			filter();

            $( ".alert-success" ).animate({
					backgroundColor: "#dff0d8"
			}, 1000 );
			$( ".alert-success" ).animate({
					backgroundColor: "#b6ef9e"
			}, 1000 );
			$( ".alert-success" ).animate({
					backgroundColor: "#dff0d8"
			}, 1000 );
			$( ".alert-success" ).animate({
					backgroundColor: "#b6ef9e"
			}, 1000 );

			$(".alert-success").show();
			$(".alert-success-content").html("{{ __('app.alert-success-delete') }}");
			window.setTimeout(function() { $(".alert-success").slideUp(); }, 10000);
		},
		error: function(data){
			$( ".alert-error" ).animate({
					backgroundColor: "#ec9b9b"
			}, 1000 );
			$( ".alert-error" ).animate({
					backgroundColor: "#df3d3d"
			}, 1000 );
			$( ".alert-error" ).animate({
					backgroundColor: "#ec9b9b"
			}, 1000 );
			$( ".alert-error" ).animate({
					backgroundColor: "#df3d3d"
			}, 1000 );

			$(".alert-error").show();
			$(".alert-error-content").html("{{ __('app.alert-error-delete') }}");
			window.setTimeout(function() { $(".alert-error").slideUp( "slow" ); }, 6000);
		}
		});
		return false;
	});

window.show_btnDelete = function(){
	i=0; hasil = false;
	while(document.getElementsByName('checkID[]').length > i)
	{
		var el = document.getElementById('checkID'+i);
		if(el && el.checked)
		{
			hasil = true;
		}
		i++;
	}
	if(hasil == true) {
		$('#btnDelete').removeAttr('disabled');
		$('#btnDelete').removeAttr('href');
		$('#btnDelete').removeAttr('title');
		$('#btnDelete').attr('href', '#hapus');
	}
	else
	{
		$('#btnDelete').attr('disabled','disabled');
		$('#btnDelete').attr('href','#');
		$('#btnDelete').attr('title', '{{ __('app.Pilih dahulu data yang akan di hapus') }}');
	}
}
show_btnDelete();

$("input:checkbox[name='checkID[]']").click(function(){
	if(this.checked == true){
		$(this).parents('tr').addClass('checked_tabel');
	}
	else
	{
		$(this).parents('tr').removeClass('checked_tabel');
	}
});
$('#btnDelete').click(function(){
	$.ajax({
		url : "{{ url('welcome/test') }}/?table={{ request()->segment(1) }}&field=Nama",
		type: "POST",
		data: $("input:checkbox[name='checkID[]']:checked").serialize(),
		success: function(data){
			$('.data_name').html(data);
		}
	});
});
</script>

<script src="{{ asset('assets/theme/scripts/responsive-table.js') }}"></script>
