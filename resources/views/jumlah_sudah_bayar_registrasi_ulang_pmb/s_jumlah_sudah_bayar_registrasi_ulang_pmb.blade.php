<div class="table-responsive">
	<table class="table table-bordered mb-0 table-hover tablesorter">
		<thead class="bg-primary text-white">
			<tr>
				<th class="text-center" width="2%">No.</th>
				<th width="30%">Program Studi Pilihan 1</th>
				<th>Program</th>
				<th>Jumlah Sudah Bayar Registrasi Ulang</th>
			</tr>
		</thead>
		<tbody>
			@php $arr_prodi_display = []; @endphp
			@foreach($query as $key => $row)
			<tr class="mhsw_{{ $row['ID'] ?? '' }}">
				@if(!in_array($row['prodiID'], $arr_prodi_display))
				<td rowspan="{{ $rowprodi[$row['prodiID']] ?? 1 }}" class="center">{{ $arr_no[$row['prodiID']] ?? '' }}.</td>
				<td rowspan="{{ $rowprodi[$row['prodiID']] ?? 1 }}">{{ $row['prodiNama'] ?? '' }}</td>
				@php $arr_prodi_display[] = $row['prodiID']; @endphp
				@endif
				<td>{{ $row['programNama'] ?? '' }}</td>
				<td>{{ $row['JumlahSudahBayar'] ?? '' }}</td>
			</tr>
			@endforeach
			<tr>
				<th colspan='3' style="text-align:right">Total</th>
				<th>{{ $TotalJumlahSudahBayar }}</th>
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


<script src="{{ asset('assets/theme/scripts/responsive-table.js') }}"></script>
