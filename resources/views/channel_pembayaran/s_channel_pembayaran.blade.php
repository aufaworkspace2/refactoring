<p>{!! $total_row !!}</p>
<form id="f_delete_metode_pembayaran" action="{{ url('channel_pembayaran/delete') }}" >
	<div class="table-responsive">
		<table class="table table-bordered mb-0 table-hover tablesorter">
			<thead class="bg-primary text-white">
				<tr>
				  @if($Delete == 'YA')
					<th width="2%">
						<div class="checkbox checkbox-info">
							<input type="checkbox" name="checkAll" id="checkAll" onClick="checkall(this,document.forms.namedItem('f_delete_metode_pembayaran')); show_btnDelete();">
							<label for="checkAll"></label>
						</div>
					</th>
				  @endif
					<th class="text-center" width="2%">No.</th>
					<th width="15%">Icon</th>
					<th width="15%">Nama</th>
					<th width="15%">Metode Bayar</th>
					<th width="15%">Panduan Bayar</th>
					<th width="15%">Komponen Biaya</th>
					<th width="15%">Status</th>
					<th width="10%">Biaya Admin</th>

				</tr>
			</thead>
			<tbody>
            @php $no=$offset; $i=0; @endphp
            @foreach($query as $row)
				@php $row = (object) $row; @endphp
				<tr class="channel_pembayaran_{{$row->ID}}">
				@if($Delete == 'YA')
					<td class="align-middle">
						@if($row->Status != 1)
						<div class="checkbox checkbox-info">
							<input type="checkbox" name="checkID[]" id="checkID{{$i}}" onclick="show_btnDelete()" value="{{$row->ID}}" >
							<label for="checkID{{$i}}"></label>
						</div>
						@php $i++; @endphp
						@else
							-
						@endif
					</td>
				  @endif

					<td class="text-center align-middle">{{++$no}}.</td>
					<td>
					<div class="media thumbnail">
					@if(!empty($row->Icon))
						<img src="{{ asset('metodebayar/channelbayar/' . $row->Icon) }}" alt="{{$row->Nama}}" style="max-width: 100px;" />
					@endif
					</div>
					</td>
					<td class="">
						@if($Update == 'YA')
						<a href="{{ url('channel_pembayaran/view/' . $row->ID) }}" >{{$row->Nama}}</a>
						@else

						{{$row->Nama}}

						@endif
					</td>

					<td>
					{{ DB::table('metode_pembayaran')->where('ID', $row->MetodePembayaranID)->value('Nama') ?? '' }}
					</td>

					<td class="">
						<button type="button" onclick="lihat_detail({{$row->ID}})" class="btn btn-info">Lihat</button>
					</td>
					<td>
					@if($row->JenisBiayaID_list)
						<ol>
						@php
						$jenisbiaya_id_list = explode(",", $row->JenisBiayaID_list);
						foreach($jenisbiaya_id_list as $m){
							$jb = DB::table('jenisbiaya')->where('ID', $m)->first();
							if($jb) {
								echo "<li>" . $jb->Nama . "</li>";
							}
						}
						@endphp
						</ol>
					@else
						Tidak Ada Komponen Biaya
					@endif
					</td>
					<td>
						<script>
						$('#bukatutup{{$no}}').bootstrapSwitch();
						$('input:checkbox[name="bukatutup{{$no}}"]:checked').parents('tr').css("background","#dcfece");
						</script>
						<input id="bukatutup{{$no}}"  name="bukatutup{{$no}}" onchange="bukatutup(this)" value="{{$row->ID}}" type="checkbox" data-on-color="success" data-off-color="danger" data-on-text="Aktif" data-off-text="Tidak" {{($row->Status == 1)? "checked":""}} />
					</td>
					<td class="">
						{{ number_format($row->BiayaAdmin, 0, ',', '.') }}
					</td>


				</tr>
            @endforeach

			</tbody>
		</table>

	</div>
<div class="row">
	<div class="col-md-12">
		{!! $link !!}
	</div>
</div>

@foreach($query as $row)
<div id="lihat_detail{{$row->ID}}" class="modal" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title">Lihat Panduan Bayar</h4>
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
			</div>
			<div class="modal-body">
				@if(isset($list_panduan[$row->ID]))
					<div class="accordion" id="myAccordion{{$row->ID}}">
						@foreach($list_panduan[$row->ID] as $row_panduan)
						<div class="card">
							<div class="card-header bg-primary" id="item{{$row_panduan->ID}}Header">
								<a class="collapsed text-white" data-toggle="collapse" data-target="#expandable{{$row_panduan->ID}}" aria-expanded="false" aria-controls="expandable{{$row_panduan->ID}}">
								{{$row_panduan->NamaPanduan}}
								</a>

							</div>
							<div id="expandable{{$row_panduan->ID}}" class="collapse" aria-labelledby="item1Header" data-parent="#myAccordion{{$row->ID}}">
								<div class="card-body">
								{!! $row_panduan->TextCaraBayar !!}
								</div>
							</div>
						</div>
						@endforeach
					</div>
				@else
					<h4>Belum Diset</h4>
				@endif
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-primary waves-effect waves-light" data-dismiss="modal">{{ __('close') }}</button>
			</div>
		</div>
	</div>
</div>
@endforeach

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
</form>

<!-------------------------------------------------------------------- Javascript Area -------------------------------------------------------------------->

<script>
tablesorter();

function lihat_detail(id){
	$('#lihat_detail'+id).modal('show');
}

// Fungsi global untuk show_btnDelete agar bisa dipanggil dari inline onclick
window.show_btnDelete = function(){
	i=0; hasil = false;
	while(document.getElementsByName('checkID[]').length > i) {
		var el = document.getElementById('checkID'+i);
		if(el && el.checked == true) {
			hasil = true;
		}
		i++;
	}
	if(hasil == true) {
		$('#btnDelete').removeAttr('disabled');
		$('#btnDelete').removeAttr('href');
		$('#btnDelete').removeAttr('title');
		$('#btnDelete').attr('href', '#hapus');
	} else {
		$('#btnDelete').attr('disabled','disabled');
		$('#btnDelete').attr('href','#');
		$('#btnDelete').attr('title', '{{ __('app.Pilih dahulu data yang akan di hapus') }}');
	}
}
show_btnDelete();

// Global function for checkall
window.checkall = function(chkAll,checkid) {
	if (checkid != null) {
		if (checkid.length == null) checkid.checked = chkAll.checked;
		else for (i=0;i<checkid.length;i++) checkid[i].checked = chkAll.checked;
		$("input:checkbox[name='checkID[]']").parents('tr').removeClass('table-danger');
		$("input:checkbox[name='checkID[]']:checked").parents('tr').addClass('table-danger');
	}
}

$("#f_delete_metode_pembayaran").submit(function(){
	$.ajax({
		type: "POST",
		url: $("#f_delete_metode_pembayaran").attr('action'),
		data: $("#f_delete_metode_pembayaran").serialize(),
		dataType: 'json',
		success:function(response){
			// Remove rows based on response
			if(response.status === 'success' && response.removed_ids) {
				response.removed_ids.forEach(function(id) {
					var className = '.' + response.class_prefix + id;
					$(className).remove();
				});
			}

			$("#hapus").modal("hide");
			setTimeout(function() {
				$("body").removeClass("modal-open");
				$(".modal-backdrop").remove();
			}, 300);

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

			// Refresh filter to update pagination
			filter("{{ url('channel_pembayaran/search') }}");
		},
		error: function(data){
			// Hide modal and cleanup on error too
			$("#hapus").modal("hide");
			setTimeout(function() {
				$("body").removeClass("modal-open");
				$(".modal-backdrop").remove();
			}, 300);

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

$("input:checkbox[name='checkID[]']").click(function(){
	if(this.checked == true){
		$(this).parents('tr').addClass('table-danger');
	}
	else
	{
		$(this).parents('tr').removeClass('table-danger');
	}
});
$('#btnDelete').click(function(){
	$.ajax({
		url : "{{ url('welcome/test') }}/?table=channel_pembayaran&field=Nama",
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

function bukatutup(chek){
	var aha = $(chek).bootstrapSwitch('state');
	var val = $(chek).val();

	if(aha == true){
	var buka = 1;
	var tutup = 0;
	}

	if(aha == false){
	var tutup = 1;
	var buka = 0;
	}

	toastr.options = {
	  "closeButton": false,
	  "debug": false,
	  "newestOnTop": false,
	  "progressBar": false,
	  "positionClass": "toast-top-left",
	  "preventDuplicates": false,
	  "onclick": null,
	  "showDuration": "300",
	  "hideDuration": "1000",
	  "timeOut": "5000",
	  "extendedTimeOut": "1000",
	  "showEasing": "swing",
	  "hideEasing": "linear",
	  "showMethod": "fadeIn",
	  "hideMethod": "fadeOut"
	}

	$.ajax({
		url:"{{ url('channel_pembayaran/set_aktif') }}",
		data:{ buka : buka, tutup:tutup, val : val},
		type:"POST",
		beforeSend: function() {
			$('.loading').fadeIn('fast');
		},
		success: function(data) {
			$('.loading').fadeOut('fast');
			toastr["success"]("", "Update Data Berhasil");
			filter("{{ url('channel_pembayaran/search/' . $offset) }}");
		},
		error: function(){
			$('.loading').fadeOut('fast');
			toastr["danger"]("", "Update Data Gagal");
		}
	});
}
</script>

<script src="{{ asset('assets/theme/scripts/responsive-table.js') }}"></script>
<!------------------------------------------------------------------ End Javascript Area ------------------------------------------------------------------>
