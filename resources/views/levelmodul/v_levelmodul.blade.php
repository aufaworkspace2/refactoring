@extends('layouts.template1') 
@section('content') 
<div class="card">
	<div class="card-body">
		<div id="konten">
		</div>
	</div>
</div>


<!-------------------------------------------------------------------- Javascript Area -------------------------------------------------------------------->
@push('scripts')
<script type="text/javascript">	
function filter(url) {
	if(url == null)
	url = "{{ url('levelmodul/search') }}";
	
	$.ajax({
		type: "POST",
		url: url,
		data: {
			_token: "{{ csrf_token() }}",
			keyword : $(".keyword").val(),
			level : "{{ request('level') }}"
		},
		beforeSend: function(data) {
			$("#konten").html("<center><h3><i class='fa fa-spin fa-spinner'></i> Memuat Data...</h3></center>");
		},
		success: function(data) {
			$("#konten").html(data);
		}
	});
	return false;
}

function pdf(){
		window.open("{{ url('levelmodul/pdf') }}?level={{ request('level') }}&keyword="+$(".keyword").val(),"_Blank");
}
	
function excel(){
		window.open("{{ url('levelmodul/excel') }}?level={{ request('level') }}&keyword="+$(".keyword").val(),"_Blank");
}


function checkall(chkAll,checkid) {
	if (checkid != null) 
	{
		if (checkid.length == null) checkid.checked = chkAll.checked;
        else for (i=0;i<checkid.length;i++) checkid[i].checked = chkAll.checked;
		
		$("input:checkbox[name='checkID[]']").parents('tr').removeClass('checked_tabel');
		$("input:checkbox[name='checkID[]']:checked").parents('tr').addClass('checked_tabel');
    }
}
filter();


</script>
@endpush
<!------------------------------------------------------------------ End Javascript Area ------------------------------------------------------------------>
@endsection