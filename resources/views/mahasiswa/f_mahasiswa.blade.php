@extends('layouts.template1') 
@section('content')

@php
$judul = (empty($row)) ? __('mahasiswa.title_add') : __('mahasiswa.title_view');
$slog  = (empty($row)) ? __('mahasiswa.slog_add') : __('mahasiswa.slog_view').'<b>'.($row->Nama ?? '').'</b>';
$btn   = (empty($row)) ? __('mahasiswa.add') : __('mahasiswa.edit');
$save  = $save ?? 1;
@endphp
<form id="f_mahasiswa" onsubmit="savedata(this); return false;" action="{{ url('mahasiswa/save/'.$save) }}" enctype="multipart/form-data">
    @csrf
	<input type="hidden" name="ID" id="ID" value="{{ $row->ID ?? '' }}">
	<div class="row">
		<div class="col-md-4">
			<div class="tab-content p-0 border-none">
				<div class="card">
					<div class="card-body">
						<div class="well-img">
							<div class="col-md-12 text-center">
								{!! get_photo($row->NPM ?? '', $row->Foto ?? '', $row->Kelamin ?? '', 'mahasiswa', 'photo_profile') !!}
							</div>
							<input class="mt-3 mb-2" type="file" name="Foto" id="Foto">
							<input type="hidden" name="ID" id="ID1" value="{{ $row->ID ?? '' }}">
							<input type="hidden" name="NamaFoto" id="NamaFoto" value="{{ $row->Foto ?? '' }}">
							<br>
							<strong>*Maksimal Ukuran Upload Foto : <br> 1 MB</strong>
						</div>
					</div>
				</div>
			</div>
			<div class="card">
				<div class="card-body p-2">
					<ul class="nav nav-tabs custom mb-3 d-block">
						<li class="nav-item">
							<a href="#tab-details" data-toggle="tab" aria-expanded="false" class="nav-link active">
								<span class="d-sm-block"><i class="mdi mdi-account mr-1 font-size-16"></i> {{ __('mahasiswa.bio') }}</span>
							</a>
						</li>
						<li class="nav-item">
							<a href="#tab-academic" data-toggle="tab" aria-expanded="false" class="nav-link">
								<span class="d-sm-block"><i class="mdi mdi-certificate mr-1 font-size-16"></i> {{ __('mahasiswa.academic') }}</span>
							</a>
						</li>
						<li class="nav-item">
							<a href="#tab-school" data-toggle="tab" aria-expanded="false" class="nav-link">
								<span class="d-sm-block"><i class="mdi mdi-home mr-1 font-size-16"></i> {{ __('mahasiswa.school') }}</span>
							</a>
						</li>
						<li class="nav-item">
							<a href="#tab-parent" data-toggle="tab" aria-expanded="false" class="nav-link">
								<span class="d-sm-block"><i class="mdi mdi-account-multiple mr-1 font-size-16"></i> {{ __('mahasiswa.parent') }}</span>
							</a>
						</li>
					</ul>
					<button onClick="btnEdit({{ $save }},'1')" type="button" class="btn btn-bordered-success waves-effect  width-md waves-light btn-block btnEdit"><span class="hidden-phone">{{ $btn }} Data</span>
						<icon class="icon-edit icon-white-t"></icon>
					</button>
					<button type="submit" class="btn btn-bordered-primary waves-effect  width-md waves-light btn-block btnSave">{{ __('mahasiswa.save') }} Data <icon class="icon-check icon-white-t"></icon></button>
					<button type="button" onClick="back()" class="btn btn-bordered-danger waves-effect  width-md waves-light btn-block">{{ __('mahasiswa.back') }} </button>

				</div>
			</div>

		</div>
		<div class="col-md-8">
			@if($save == 2)
				<h4 class="mt-0 mb-0">Persentase Kelengkapan Profil</h4>
				<div class="progress progress-lg mb-2" style="cursor:pointer;" data-toggle="modal" data-target="#myModalLengkapProfil" title="Lihat data yang belum lengkap">
					<div class="progress-bar bg-warning progress-bar-striped progress-bar-animated" role="progressbar" aria-valuenow="{{ $PersentaseKelengkapanProfil ?? 0 }}" aria-valuemin="0" aria-valuemax="100" style="width: {{ $PersentaseKelengkapanProfil ?? 0 }}%;">
						{{ $PersentaseKelengkapanProfil ?? 0 }}%
					</div>
				</div>
			@endif
			<div class="tab-content p-0 border-none">
				<div role="tabpanel" class="tab-pane fade show active" id="tab-details">
					<div class="card">
						<h5 class="card-header bg-primary text-white">{{ __('mahasiswa.personal_detail') }}</h5>
						<div class="card-body">
							<div class="form-horizontal">

								<div class="form-group row">
									<label class="col-md-4 col-form-label" for="Nama">{{ __('mahasiswa.NPM') }} */{{ __('mahasiswa.Nama') }} *</label>
									<div class="col-md-4">
										<input data-required="true" data-alert="NIM Harus Diisi" type="text" {{ ($save == 2) ? 'readonly' : '' }} id="NPM" name="NPM" class="form-control" value="{{ $row->NPM ?? '' }}" />
									</div>
									<div class="col-md-4">
										<input type="text" data-required="true" data-alert="Nama Harus Diisi" id="Nama" name="Nama" class="form-control" value="{{ $row->Nama ?? '' }}" onclick="checkForm()" />
									</div>
								</div>

								<div class="form-group row">
									<label class="col-md-4 col-form-label" for="TempatLahir">Tempat */{{ __('mahasiswa.TanggalLahir') }} *</label>
									<div class="col-md-4">
										<input data-required="true" data-alert="Tempat Lahir Harus Diisi" type="text" id="TempatLahir" name="TempatLahir" class="form-control" value="{{ $row->TempatLahir ?? '' }}" />
									</div>
									<div class="col-md-4">
										<input data-required="true" data-alert="Tanggal Lahir Harus Diisi" type="text" id="TanggalLahir" name="TanggalLahir" class="form-control datepicker" value="{{ gantitanggal($row->TanggalLahir ?? '', 'd/m/Y') }}" />
									</div>
								</div>

								<div class="form-group row">
									<label class="col-md-4 col-form-label" for="Kelamin">{{ __('mahasiswa.Kelamin') }} *</label>
									<div class="col-md-4">
										<label>
											<input type="radio" id="KelaminL" name="Kelamin" {{ (($row->Kelamin ?? 'L') == 'L') ? "checked" : "" }} value="L" data-required="true" data-alert="Jenis Kelamin Harus Diisi" />
											<span>{{ __('mahasiswa.male') }}</span>
										</label>
										<label>
											<input type="radio" id="KelaminP" name="Kelamin" {{ (($row->Kelamin ?? '') == 'P') ? "checked" : "" }} value="P" />
											<span>{{ __('mahasiswa.female') }}</span>
										</label>
										<label for="Kelamin" class="error" style="display:none;" id="Kelamin-error"></label>
									</div>
								</div>

								<div class="form-group row">
									<label class="col-md-4 col-form-label" for="AgamaID">{{ __('mahasiswa.AgamaID') }} */{{ __('mahasiswa.StatusSipil') }}</label>
									<div class="col-md-4">
										<select id="AgamaID" name="AgamaID" class="form-control" data-required="true" data-alert="Agama Harus Diisi">
											<option value="">-- {{ __('mahasiswa.select') }} {{ __('mahasiswa.AgamaID') }} --</option>
											@foreach(get_all('agama') as $riw)
                                                <?php $select = (($row->AgamaID ?? '') == $riw->ID) ? "selected" : ""; ?>
												<option value="{{ $riw->ID }}" {{ $select }}>{{ $riw->Nama }}</option>
											@endforeach
										</select>
									</div>
									<div class="col-md-4">
										<select id="StatusSipil" name="StatusSipil" class="form-control">
											<option value="">{{ __('mahasiswa.select') }} {{ __('mahasiswa.StatusSipil') }}</option>
											<option {{ (($row->StatusSipil ?? '') == "S") ? "selected" : "" }} value="S">Sudah Menikah</option>
											<option {{ (($row->StatusSipil ?? '') == "B") ? "selected" : "" }} value="B">Belum Menikah</option>
										</select>
									</div>
								</div>
								<div class="form-group row">
									<label class="col-md-4 col-form-label" for="JenisIdentitas">
										Jenis */ <span id="label_dinamis">No Identitas</span> *
									</label>
									
									<div class="col-md-4">
										<select id="JenisIdentitas" data-required="true" data-alert="Jenis Identitas Harus Diisi" name="JenisIdentitas" class="form-control">
											<option {{ (($row->JenisIdentitas ?? 'KTP') == "KTP") ? "selected" : "" }} value="KTP">KTP / NIK</option>
											<option {{ (($row->JenisIdentitas ?? '') == "PASPOR") ? "selected" : "" }} value="PASPOR">PASSPORT</option>
										</select>
									</div>
									
									<div class="col-md-4">
										<input type="text" data-required="true" data-alert="No Identitas Harus Diisi" id="NoIdentitas" name="NoIdentitas" class="form-control number" value="{{ $row->NoIdentitas ?? '' }}" />
										
										<small id="info_identitas">* Harus 16 Karakter</small>
										<br><small id="err_identitas" class="text-danger" style="display:none;"></small>
									</div>
								</div>
								<div class="form-group row">
									<label class="col-md-4 col-form-label" for="AnakKe">{{ __('mahasiswa.AnakKe') }}/{{ __('mahasiswa.JumlahSaudara') }}</label>
									<div class="col-md-4">
										<input type="text" id="AnakKe" name="AnakKe" class="form-control" value="{{ $row->AnakKe ?? '' }}" />
									</div>
									<div class="col-md-4">
										<input type="text" id="JumlahSaudara" name="JumlahSaudara" class="form-control" value="{{ $row->JumlahSaudara ?? '' }}" />
									</div>
								</div>

								<div class="form-group row">
									<label class="col-md-4 col-form-label" for="Berat">Berat/Tinggi Badan</label>
									<div class="col-md-4">
										<input type="number" id="Berat" name="Berat" class="form-control number" value="{{ $row->Berat ?? '' }}" />
									</div>
									<div class="col-md-4">
										<input type="number" id="TinggiBadan" name="TinggiBadan" class="form-control number" value="{{ $row->TinggiBadan ?? '' }}" />
									</div>
								</div>

								<div class="form-group row">
									<label class="col-md-4 col-form-label" for="Kewarganegaraan">Kewarganegaraan*</label>
									<div class="col-md-8">
										<select id="Kewarganegaraan" name="Kewarganegaraan" class="Kewarganegaraan form-control" data-required="true" data-alert="Kewarganegaraan Harus Diisi">>
										</select>
									</div>
								</div>
								<div class="form-group row">
									<label class="col-md-4 col-form-label">Penerima KPS *</label>
									<div class="col-md-8">
										<select class="form-control input-sm span12" name="PenerimaKPS" data-required="true" data-alert="Penerima KPS Harus Diisi" onchange="change_no_kps()">
											<option value="0" @if(($row->PenerimaKPS ?? '') ==  '0') selected @endif>Tidak</option>
											<option value="1" @if(($row->PenerimaKPS ?? '') ==  '1') selected @endif>Ya</option>
										</select>
									</div>
								</div>
								<div id="no_kps_div" class="form-group row">
									<label class="col-md-4 col-form-label">No KPS</label>
									<div class="col-md-4">
										<input type="text" class="form-control" id="NoKPS" name="NoKPS" value="{{ $row->NoKPS ?? '' }}">
									</div>
								</div>
							</div>
						</div>
					</div>

					<div class="card">
						<h5 class="card-header bg-primary text-white">{{ __('mahasiswa.address_detail') }}</h5>
						<div class="card-body">
							<div class="form-horizontal">
								<div class="form-group row">
									<label class="col-md-4 col-form-label" for="Propinsi">Propinsi *</label>
									<div class="col-md-8">
										<select id="Propinsi" name="Propinsi" class="form-control" onchange="changeKota()" data-required="true" data-alert="Propinsi Harus Diisi">
											<option value=""> -- Pilih Propinsi -- </option>
											@php
											$query_propinsi = DB::table('wilayah')->where('parent_id', 0)->where('group', '01')->select('code', 'name')->get();
											@endphp
											@foreach ($query_propinsi as $raw)
												<option {{ ($raw->code == ($row->PropinsiID ?? '')) ? "selected" : "" }} value="{{ $raw->code }}">{{ $raw->name }}</option>
											@endforeach
										</select>
									</div>
								</div>

								<div class="form-group row">
									<label class="col-md-4 col-form-label" for="Kota">Kabupaten/Kota *</label>
									<div class="col-md-8">
										<select id="Kota" name="Kota" class="form-control" onchange="changeKecamatan()" data-required="true" data-alert="Kabupaten/Kota Harus Diisi">
										</select>
									</div>
								</div>

								<div class="form-group row">
									<label class="col-md-4 col-form-label" for="Kecamatan">Kecamatan *</label>
									<div class="col-md-8">
										<select id="Kecamatan" name="Kecamatan" class="form-control" data-required="true" data-alert="Kecamatan Harus Diisi">
										</select>
									</div>
								</div>

								<div class="form-group row">
									<label class="col-md-4 col-form-label" for="KodePos">Kelurahan */{{ __('mahasiswa.KodePos') }}</label>
									<div class="col-md-4">
										<input type="text" id="Kelurahan" name="Kelurahan" class="form-control" value="{{ $row->Kelurahan ?? '' }}" data-required="true" data-alert="Kelurahan Harus Diisi" />
									</div>
									<div class="col-md-4">
										<input type="text" maxlength="5" id="KodePos" name="KodePos" onkeypress="return event.charCode > 47 && event.charCode < 58;" class="form-control" value="{{ $row->KodePos ?? '' }}" />
									</div>
								</div>

								<div class="form-group row">
									<label class="col-md-4 col-form-label" for="Dusun">Dusun/RT/RW</label>
									<div class="col-md-4">
										<input type="text" id="Dusun" name="Dusun" class="form-control" value="{{ $row->Dusun ?? '' }}" />
									</div>
									<div class="col-md-2">
										<input type="number" id="RT" name="RT" class="form-control number" value="{{ $row->RT ?? '' }}" />
									</div>
									<div class="col-md-2">
										<input type="number" id="RW" name="RW" class="form-control number" value="{{ $row->RW ?? '' }}" />
									</div>
								</div>

								<div class="form-group row">
									<label class="col-md-4 col-form-label" for="Alamat">{{ __('mahasiswa.Alamat') }}</label>
									<div class="col-md-8">
										<textarea maxlength="80" id="Alamat" name="Alamat" class="form-control">{{ $row->Alamat ?? '' }}</textarea>
										<br><small>* Maksimal 80 Karakter</small>
									</div>
								</div>
							</div>
						</div>
					</div>

					<div class="card">
						<h5 class="card-header bg-primary text-white">{{ __('mahasiswa.contact_detail') }}</h5>
						<div class="card-body">
							<div class="form-horizontal">
								<div class="form-group row">
									<label class="col-md-4 col-form-label" for="Telepon">{{ __('mahasiswa.Telepon') }}/{{ __('mahasiswa.HP') }}</label>
									<div class="col-md-4">
										<input type="text" id="Telepon" name="Telepon" onkeypress="return event.charCode > 47 && event.charCode < 58;" class="form-control" value="{{ $row->Telepon ?? '' }}" />
									</div>
									<div class="col-md-4">
										<input minlength="10" type="text" id="HP" name="HP" onkeypress="return event.charCode > 47 && event.charCode < 58;" class="form-control number" value="{{ $row->HP ?? '' }}" />
										<small>* Harus Angka, Minimal 10 Karakter</small>
									</div>
								</div>

								<div class="form-group row">
									<label class="col-md-4 col-form-label" for="Email">{{ __('mahasiswa.Email') }}</label>
									<div class="col-md-8">
										<input type="email" id="Email" name="Email" class="form-control email" value="{{ $row->Email ?? '' }}" />
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div role="tabpanel" class="tab-pane fade" id="tab-academic">
					<div class="card">
						<h5 class="card-header bg-primary text-white">{{ __('mahasiswa.academic_detail') }}</h5>
						<div class="card-body">
							<div class="form-horizontal">
								<div class="form-group row">
									<label class="col-md-4 col-form-label" for="ProgramID">{{ __('mahasiswa.ProgramID') }} */{{ __('mahasiswa.JenjangID') }} *</label>
									<div class="col-md-4">
										<select id="ProgramID" data-required="true" data-alert="Program Harus Diisi" name="ProgramID" class="form-control">
											<option value="" selected="selected">-- {{ __('mahasiswa.select') . ' ' . __('mahasiswa.ProgramID') }} --</option>
											@foreach (get_all('program') as $raw)
												<option {{ ($raw->ID == ($row->ProgramID ?? '')) ? "selected" : "" }} value="{{ $raw->ID }}">{{ $raw->Nama }}</option>
											@endforeach
										</select>
									</div>
									<div class="col-md-4">
										<select data-required="true" data-alert="Jenjang Harus Diisi" id="JenjangID" name="JenjangID" class="form-control" onchange="chjprodi()">
											<option value="" selected="selected">-- {{ __('mahasiswa.select') . ' ' . __('mahasiswa.JenjangID') }} --</option>
											@foreach (get_all('jenjang') as $raw)
												<option {{ ($raw->ID == ($row->JenjangID ?? '')) ? "selected" : "" }} value="{{ $raw->ID }}">{{ $raw->Nama }}</option>
											@endforeach
										</select>
									</div>
								</div>

								<div class="form-group row">
									<label class="col-md-4 col-form-label" for="ProdiID">{{ __('mahasiswa.ProdiID') }} */{{ __('mahasiswa.TanggalMasuk') }} *</label>
									<div class="col-md-4">
										<select id="ProdiID" data-required="true" data-alert="Programstudi Harus Diisi" name="ProdiID" class="form-control" onchange="changekelas();">
											<option value="" selected="selected">-- {{ __('mahasiswa.select') . ' ' . __('mahasiswa.ProdiID') }} --</option>
										</select>
									</div>
									<div class="col-md-4">
										<input type="text" data-required="true" data-alert="Tanggal Masuk Harus Diisi" id="TanggalMasuk" name="TanggalMasuk" class="form-control datepicker" value="{{ gantitanggal($row->TanggalMasuk ?? '', 'd/m/Y') }}" data-toggle="tanggal" />
									</div>
								</div>

								<div class="form-group row">
									@php
                                    $pt_tgl = get_field(1, 'identitas', 'TglBerdiriPT');
									$tahunberdiri = $pt_tgl ? date('Y', strtotime($pt_tgl)) : date('Y');
									$tahunsekarang = date('Y') + 5;
									$loop = $tahunsekarang - $tahunberdiri;
									@endphp
									<label class="col-md-4 col-form-label" for="Angkatan">Angkatan *</label>
									<div class="col-md-4">
										<select id="Angkatan" data-required="true" data-alert="Angkatan/Tahun Masuk Harus Diisi" name="Angkatan" onchange="batasstudi();" class="form-control">
											<option value="" selected="selected">-- {{ __('mahasiswa.select') . ' Tahun' }} --</option>
											@for($i = 0; $i <= $loop; $i++)
												<option {{ (($tahunsekarang - $i) == ($row->TahunMasuk ?? '')) ? "selected" : "" }} value="{{ $tahunsekarang - $i }}">{{ $tahunsekarang - $i }}</option>
											@endfor
										</select>
									</div>
									<div class="col-md-4">
										<select id="SemesterMasuk" data-required="true" data-alert="Semester Masuk (Ganjil/Genap) Harus Diisi" name="SemesterMasuk" onchange="batasstudi();" class="form-control">
											<option {{ (($row->SemesterMasuk ?? '') == 1) ? "selected" : "" }} value="1">Ganjil</option>
											<option {{ (($row->SemesterMasuk ?? '') == 2) ? "selected" : "" }} value="2">Genap</option>
										</select>
									</div>
								</div>
								<div class="form-group row">
									<label class="col-md-4 col-form-label" for="StatusMhswID">{{ __('mahasiswa.StatusMhswID') }} *</label>
									<div class="col-md-8">
										<input type="hidden" id="StatusMhswID" name="StatusMhswID" class="span12" value="{{ $row->StatusMhswID ?? '3' }}" />
										<label class="col-md-8 col-form-label" class="span12">{{ ($row->StatusMhswID ?? '') ? get_field($row->StatusMhswID, 'statusmahasiswa') : "Aktif" }}</label>
									</div>
								</div>

								<div class="form-group row">
									<label class="col-md-4 col-form-label" for="Kurikulum">Kurikulum */{{ __('mahasiswa.BatasStudi') }}</label>
									<div class="col-md-4">
										<select data-required="true" data-alert="Kurikulum Harus Diisi" id="KurikulumID" onchange="batasstudi();" name="KurikulumID" class="form-control">
										</select>
									</div>
									<div class="col-md-4">
										<input type="text" id="BatasStudi" name="BatasStudi" class="form-control" value="{{ $row->BatasStudi ?? '' }}" />
									</div>
								</div>

								<div class="form-group row">
									<label class="col-md-4 col-form-label" for="jalur_pmb">Jalur Pendaftaran *</label>
									<div class="col-md-8">
										<select id="jalur_pmb" name="jalur_pmb" class="form-control" data-required="true" data-alert="Jalur Pendaftaran Harus Diisi">
											<option value=""> -- Pilih --</option>
											@php 
                                            $jalurMasuk = DB::table('pmb_edu_jalur_pendaftaran')->where('aktif', '1')->get();
                                            @endphp
											@foreach ($jalurMasuk as $jm)
												<option value="{{ $jm->id }}" {{ ($jm->id == ($row->jalur_pmb ?? '')) ? 'selected' : '' }}>{{ $jm->nama }}</option>
											@endforeach
										</select>
									</div>
								</div>

								<div class="form-group row">
									<label class="col-md-4 col-form-label" for="BiayaAwalID">Jenis Pembiayaan Awal *</label>
									<div class="col-md-8">
										<select id="BiayaAwalID" name="BiayaAwalID" class="form-control" data-required="true" data-alert="Jenis Pembiayaan Awal Harus Diisi">
											<option value=""> -- Pilih --</option>
											@php $biayaAwal = DB::table('biaya_awal')->get(); @endphp
											@foreach ($biayaAwal as $ba)
												<option value="{{ $ba->ID }}" {{ ($ba->ID == ($row->BiayaAwalID ?? '')) ? 'selected' : '' }}>{{ $ba->Nama }}</option>
											@endforeach
										</select>
									</div>
								</div>

								<div class="form-group row">
									<label class="col-md-4 col-form-label" for="GelombangKe">Gelombang Ke- *</label>
									<div class="col-md-8">
										<select id="GelombangKe" name="GelombangKe" class="form-control" data-required="true" data-alert="Gelombang Ke- Harus Diisi">
											<option value=""> -- Pilih --</option>
											@php $gelombang_ke = DB::table('gelombang_ke')->get(); @endphp
											@foreach ($gelombang_ke as $raw)
												<option value="{{ $raw->GelombangKe }}" {{ ($raw->GelombangKe == ($row->GelombangKe ?? '')) ? 'selected' : '' }}>{{ $raw->Nama }}</option>
											@endforeach
										</select>
									</div>
								</div>

								<div class="form-group row">
									<label class="col-md-4 col-form-label" for="BiayaMasuk">Biaya Masuk *</label>
									<div class="col-md-8">
										<input type="text" class="form-control currency" name="BiayaMasuk" value="{{ $row->BiayaMasuk ?? '' }}" data-required="true" data-alert="Biaya Masuk Harus Diisi" />
									</div>
								</div>

								<div class="form-group row">
									<label class="col-md-4 col-form-label" for="SumberData">Sumber Data</label>
									<div class="col-md-4">
										<p><strong>
												@if($save == 1)
													Input Data Manual di Modul Mahasiswa
												@else
													@if(($row->SumberData ?? '') == 'manual')
														Input Data Manual di Modul Mahasiswa
													@elseif(($row->SumberData ?? '') == 'migrasi')
														Migrasi
													@elseif(($row->SumberData ?? '') == 'pmb')
														PMB
													@elseif(($row->SumberData ?? '') == 'upload_excel')
														Input Melalui Fitur Upload Excel
													@elseif(($row->SumberData ?? '') == 'konversi')
														Input Melalui Fitur Konversi Internal
													@endif
												@endif
											</strong></label>
									</div>
								</div>

								<div class="form-group row">
									<label class="col-md-4 col-form-label" for="StatusPindahan">{{ __('mahasiswa.StatusPindahan') }} *</label>
									<div class="col-md-4">
										@foreach (DB::table('jenis_pendaftaran')->where('Aktif', 'Ya')->get() as $jp)
											<label>
												<input type="radio" id="StatusPindahan" name="StatusPindahan" {{ (($row->StatusPindahan ?? 'B') == $jp->Kode) ? "checked" : "" }} data-required="true" data-alert="Status Mahasiswa Harus Diisi" value="{{ $jp->Kode }}" data-toggle="status" />
												<span>{{ $jp->Nama }}</span>
											</label>
										@endforeach
										<label for="StatusPindahan" class="error" style="display:none;" id="StatusPindahan-error"></label>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="card" id="Pindahan" style="display:{{ (($row->StatusPindahan ?? 'B') != 'B') ? 'block' : 'none' }}">
						<h5 class="card-header bg-primary text-white">Data Pindahan</h5>
						<div class="card-body">
							<div class="form-horizontal">
								<div class="form-group row">
									<label class="col-md-4 col-form-label" for="BatasStudi">{{ __('mahasiswa.AsalPT') }}</label>
									<div class="col-md-8">
										<select id="AsalPT" name="AsalPT" class="form-control" onchange="changeProdiIDPT()">
											<option value="" selected="selected">-- Asal Perguruan Tinggi --</option>
											@if($row->AsalPT ?? '')
                                                @php $AsalPT_row = DB::table('ref_pt')->where('KodePT', $row->AsalPT)->first(); @endphp
												@if ($AsalPT_row)
												    <option selected value="{{ $AsalPT_row->KodePT }}">{{ $AsalPT_row->KodePT }} || {{ $AsalPT_row->NamaPT }} || {{ $AsalPT_row->NamaSingkatPT }}</option>
												@endif
											@endif
										</select>
									</div>
								</div>

								<div class="form-group row">
									<label class="col-md-4 col-form-label" for="ProdiIDPT">{{ __('mahasiswa.ProdiIDPT') }}/{{ __('mahasiswa.AsalJenjangID') }} </label>
									<div class="col-md-4">
										<select id="ProdiIDPT" name="ProdiIDPT" class="form-control">
											<option value="" selected="selected">-- {{ __('mahasiswa.select') . ' ' . __('mahasiswa.ProdiIDPT') }} --</option>
											@if(($row->AsalPT ?? '') && ($row->ProdiIDPT ?? ''))
                                                @php $AsalProdi_row = DB::table('ref_programstudi')->where('KodePT', $row->AsalPT)->where('KodeProdi', $row->ProdiIDPT)->first(); @endphp
												@if ($AsalProdi_row)
												    <option selected value="{{ $AsalProdi_row->KodeProdi }}">{{ $AsalProdi_row->KodeProdi }} || {{ $AsalProdi_row->NamaJenjang }} || {{ $AsalProdi_row->NamaProdi }}</option>
												@endif
											@endif
										</select>
									</div>
									<div class="col-md-4">
										<select id="AsalJenjangID" name="AsalJenjangID" class="form-control">
											<option value="" selected="selected">-- {{ __('mahasiswa.select') . ' ' . __('mahasiswa.AsalJenjangID') }} --</option>
											@foreach (get_all('jenjang') as $raw)
												<option {{ ($raw->ID == ($row->AsalJenjangID ?? '')) ? "selected" : "" }} value="{{ $raw->ID }}">{{ $raw->Nama }}</option>
											@endforeach
										</select>
									</div>
								</div>

								<div class="form-group row">
									<label class="col-md-4 col-form-label" for="AsalNIM">{{ __('mahasiswa.AsalNIM') }}/{{ __('mahasiswa.JlmSKSPT') }}</label>
									<div class="col-md-4">
										<input type="text" id="AsalNIM" name="AsalNIM" class="form-control" value="{{ $row->AsalNIM ?? '' }}" />
									</div>
									<div class="col-md-4">
										<input type="text" id="JlmSKSPT" name="JlmSKSPT" class="form-control" value="{{ $row->JlmSKSPT ?? '' }}" />
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div role="tabpanel" class="tab-pane fade" id="tab-school">
					<div class="card">
						<h5 class="card-header bg-primary text-white">{{ __('mahasiswa.academic_detail') }}</h5>
						<div class="card-body">
							<div class="form-horizontal">
								<div class="form-group row">
									<label class="col-md-4 col-form-label" for="NISN">NISN *</label>
									<div class="col-md-8">
										<input type="text" id="NISN" name="NISN" class="form-control" value="{{ $row->NISN ?? '' }}" data-required="true" data-alert="NISN Harus Diisi" />
									</div>
								</div>

								<div class="form-group row">
									<label class="col-md-4 col-form-label" for="JenisSekolahID">Jenis/Nama Sekolah</label>
									<div class="col-md-4">
										<select id="JenisSekolahID" name="JenisSekolahID" class="form-control">
											<option value="" selected="selected">-- {{ __('mahasiswa.select') . ' ' . __('mahasiswa.JenisSekolahID') }} --</option>
											@foreach (get_all('jenissekolah') as $raw)
												<option {{ ($raw->ID == ($row->JenisSekolahID ?? '')) ? "selected" : "" }} value="{{ $raw->ID }}">{{ $raw->Nama }}</option>
											@endforeach
										</select>
									</div>
									<div class="col-md-4">
										<div id="asalsekolah_div">
											<select id="SekolahID" name="SekolahID" class="form-control">
											</select>
										</div>
										<input type="text" name="sekolah_nama_custom" id="asalsekolah_custom" value="" class="form-control input-sm" style="display: none;" placeholder="Isi Nama Sekolah Disini">
										<input class="form-control" type="hidden" name="cara_input_sekolah" id="cara_input_sekolah" value="1">
										<small style="color: blue;"><a style="color: blue;cursor:pointer" onclick="change_input_sekolah()">Tidak Menemukan Sekolah Anda? Silahkan Klik Disini.</a></small>
									</div>
								</div>

								<div class="form-group row">
									<label class="col-md-4 col-form-label" for="JurusanSekolah">{{ __('mahasiswa.JurusanSekolah') }}/{{ __('mahasiswa.TanggalLulus') }} </label>
									<div class="col-md-4">
										<select id="JurusanSekolahID" name="JurusanSekolahID" class="form-control">
											<option value="" selected="selected">-- {{ __('mahasiswa.select') . ' ' . __('mahasiswa.JurusanSekolah') }} --</option>
											@foreach (get_all('jurusansekolah') as $raw)
												<option {{ ($raw->ID == ($row->JurusanSekolahID ?? '')) ? "selected" : "" }} value="{{ $raw->ID }}">{{ $raw->Nama }}</option>
											@endforeach
										</select>
									</div>
									<div class="col-md-4">
										<input type="text" id="TanggalLulus" name="TanggalLulus" class="form-control datepicker" value="{{ gantitanggal($row->TanggalLulus ?? '', 'd/m/Y') }}" />
									</div>
								</div>

								<div class="form-group row">
									<label class="col-md-4 col-form-label" for="AlamatSekolah">{{ __('mahasiswa.Alamat') }} Sekolah</label>
									<div class="col-md-8">
										<textarea id="AlamatSekolah" name="AlamatSekolah" class="form-control">{{ $row->AlamatSekolah ?? '' }}</textarea>
									</div>
								</div>

								<div class="form-group row">
									<label class="col-md-4 col-form-label" for="KodePos">{{ __('mahasiswa.KodePos') }} Sekolah</label>
									<div class="col-md-8">
										<input type="text" id="KodePosSekolah" name="KodePosSekolah" class="form-control" value="{{ $row->KodePosSekolah ?? '' }}" />
									</div>
								</div>

								<div class="form-group row">
									<label class="col-md-4 col-form-label" for="KecamatanSekolah">Kecamatan / Telp Sekolah</label>
									<div class="col-md-4">
										<select class="wilayah form-control" id="KecamatanSekolah" name="KecamatanSekolah">
										</select>
									</div>
									<div class="col-md-4">
										<input type="text" id="TeleponSekolah" name="TeleponSekolah" class="form-control" value="{{ $row->TeleponSekolah ?? '' }}" />
									</div>
								</div>

								<div class="form-group row">
									<label class="col-md-4 col-form-label" for="NoIjazah">{{ __('mahasiswa.NoIjazah') }}/{{ __('mahasiswa.Nilaiunas') }} </label>
									<div class="col-md-4">
										<input type="text" id="NoIjazah" name="NoIjazah" class="form-control" value="{{ $row->NoIjazah ?? '' }}" />
									</div>
									<div class="col-md-4">
										<input type="text" id="Nilaiunas" name="Nilaiunas" class="form-control" value="{{ $row->Nilaiunas ?? '' }}" />
									</div>
								</div>

								<div class="form-group row">
									<label class="col-md-4 col-form-label" for="FileIjazah">File Ijazah</label>
									<div class="col-md-8">
										<input class="form-control" name="FileIjazah" id="FileIjazah" type="file">
										<div class="span6">
											<p>File sebelumnya : </p>
											@if(($row->FileIjazah ?? '') == '')
												(Tidak Ada)
											@else
												<a target="blank" href="{{ env('CLIENT_HOST') . '/mahasiswa/' . ($row->NPM ?? '') . '/document/FileIjazah/' . $row->FileIjazah }}">{{ $row->FileIjazah }}</a>
											@endif
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div role="tabpanel" class="tab-pane fade" id="tab-parent">
					<div class="card">
						<h5 class="card-header bg-primary text-white">Penanggung Jawab</h5>
						<div class="card-body">
							<div class="form-horizontal">
								<div class="form-group row">
									<label class="col-md-4 col-form-label" for="PenanggungJawab">Penanggung Jawab </label>
									<div class="col-md-8">
										<select id="PJID" name="PJID" class="form-control" onchange="penanggungjawab()">
											@php
											$penanggunga = (($sql_ayah->PenanggungJawab ?? 0) == 1) ? "selected" : "";
											$penanggungi = (($sql_ibu->PenanggungJawab ?? 0) == 1) ? "selected" : "";
											$penanggungw = (($sql_wali->PenanggungJawab ?? 0) == 1) ? "selected" : "";
											@endphp
											<option value="Ayah" {{ $penanggunga }}>Ayah</option>
											<option value="Ibu" {{ $penanggungi }}>Ibu</option>
											<option value="Wali" {{ $penanggungw }}>Wali</option>
										</select>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="card">
						<h5 class="card-header bg-primary text-white">Ayah</h5>
						<div class="card-body">
							<div class="form-horizontal">
								<input type="hidden" id="IDAyah" name="IDAyah" class="span12" value="{{ $sql_ayah->ID ?? '' }}" />

								<div class="form-group row">
									<label class="col-md-4 col-form-label" for="NamaAyah">Nama *</label>
									<div class="col-md-8">
										<input type="text" id="NamaAyah" data-required="true" data-alert="Nama Ayah Harus Diisi" name="NamaAyah" class="form-control" value="{{ $sql_ayah->Nama ?? '' }}" />
									</div>
								</div>

								<div class="form-group row">
									<label class="col-md-4 col-form-label" for="StatusAyah">Wafat</label>
									<div class="col-md-4">
										<input {{ (($sql_ayah->Status ?? '') == 'Tiada') ? 'checked' : '' }} name="StatusAyah" id="StatusAyah" type="checkbox" value="Tiada"> Ya
									</div>
								</div>

								<div class="form-group row">
									<label class="col-md-4 col-form-label" for="TanggalLahir">Tanggal Lahir</label>
									<div class="col-md-8">
										<input type="text" id="TanggalLahirAyah" name="TanggalLahirAyah" class="form-control datepicker" value="{{ gantitanggal($sql_ayah->TanggalLahir ?? '', 'd/m/Y') }}" />
									</div>
								</div>

								<div class="form-group row">
									<label class="col-md-4 col-form-label" for="JenisSekolahIDAyah">Pendidikan/Pekerjaan</label>
									<div class="col-md-4">
										<select id="JenisSekolahIDAyah" name="JenisSekolahIDAyah" class="form-control">
											<option value="" selected="selected">-- {{ __('mahasiswa.select') . ' Pendidikan' }} --</option>
											@foreach (get_all('jenissekolah') as $raw)
												<option {{ ($raw->ID == ($sql_ayah->JenisSekolahID ?? '')) ? "selected" : "" }} value="{{ $raw->ID }}">{{ $raw->Nama }}</option>
											@endforeach
										</select>
									</div>
									<div class="col-md-4">
										<select id="PekerjaanAyah" name="PekerjaanAyah" class="form-control">
											<option value="" selected="selected">-- {{ __('mahasiswa.select') . ' Pekerjaan' }} --</option>
											@foreach (get_all('pekerjaan') as $raw)
												<option {{ ($raw->ID == ($sql_ayah->PekerjaanID ?? '')) ? "selected" : "" }} value="{{ $raw->ID }}">{{ $raw->Nama }}</option>
											@endforeach
										</select>
									</div>
								</div>

								<div class="form-group row">
									<label class="col-md-4 col-form-label" for="PenghasilanAyah">Penghasilan</label>
									<div class="col-md-8">
										<select id="PenghasilanAyah" name="PenghasilanAyah" class="form-control">
											<option value="" selected="selected">-- {{ __('mahasiswa.select') . ' Penghasilan' }} --</option>
											@foreach (get_all('penghasilan') as $raw)
												<option {{ ($raw->ID == ($sql_ayah->PenghasilanID ?? '')) ? "selected" : "" }} value="{{ $raw->ID }}">{{ $raw->Nama }}</option>
											@endforeach
										</select>
									</div>
								</div>

								<div class="form-group row">
									<label class="col-md-4 col-form-label" for="AgamaAyah">Agama</label>
									<div class="col-md-8">
										<select id="AgamaAyah" name="AgamaAyah" class="form-control">
											<option value="" selected="selected">-- {{ __('mahasiswa.select') }} --</option>
											@foreach (get_all('agama') as $raw)
												<option {{ ($raw->ID == ($sql_ayah->AgamaID ?? '')) ? "selected" : "" }} value="{{ $raw->ID }}">{{ $raw->Nama }}</option>
											@endforeach
										</select>
									</div>
								</div>

								<div class="form-group row">
									<label class="col-md-4 col-form-label" for="KecamatanAyah">Kecamatan</label>
									<div class="col-md-8">
										<select class="wilayah form-control" id="KecamatanAyah" name="KecamatanAyah">
										</select>
									</div>
								</div>

								<div class="form-group row">
									<label class="col-md-4 col-form-label" for="KelurahanAyah">Kelurahan </label>
									<div class="col-md-8">
										<input type="text" id="KelurahanAyah" name="KelurahanAyah" class="form-control" value="{{ $sql_ayah->Kelurahan ?? '' }}" />
									</div>
								</div>

								<div class="form-group row">
									<label class="col-md-4 col-form-label" for="KodePosAyah">Kode Pos </label>
									<div class="col-md-8">
										<input type="text" id="KodePosAyah" name="KodePosAyah" class="form-control" value="{{ $sql_ayah->KodePos ?? '' }}" />
									</div>
								</div>

								<div class="form-group row">
									<label class="col-md-4 col-form-label" for="AlamatAyah">Alamat *</label>
									<div class="col-md-8">
										<textarea id="AlamatAyah" name="AlamatAyah" class="form-control" data-required="true" data-alert="Alamat Ayah Harus Diisi">{{ $sql_ayah->Alamat ?? '' }}</textarea>
									</div>
								</div>

								<div class="form-group row">
									<label class="col-md-4 col-form-label" for="TeleponAyah">Telepon */Email </label>
									<div class="col-md-4">
										<input type="text" id="TeleponAyah" name="TeleponAyah" data-required="true" data-alert="Telepon Ayah Harus Diisi" class="form-control" onkeypress="return event.charCode > 47 && event.charCode < 58;" value="{{ $sql_ayah->Telepon ?? '' }}" />
									</div>
									<div class="col-md-4">
										<input type="email" id="EmailAyah" name="EmailAyah" class="form-control email" value="{{ $sql_ayah->Email ?? '' }}" />
									</div>
								</div>

								<div class="form-group row">
									<label class="col-md-4 col-form-label" for="Instansi">Nama Instansi / HP *</label>
									<div class="col-md-4">
										<input type="text" id="NamaInstansiAyah" name="NamaInstansiAyah" class="form-control" value="{{ $sql_ayah->NamaInstansi ?? '' }}" />
									</div>
									<div class="col-md-4">
										<input type="text" id="HPAyah" name="HPAyah" class="form-control" data-required="true" data-alert="No HP Ayah Harus Diisi" onkeypress="return event.charCode > 47 && event.charCode < 58;" value="{{ $sql_ayah->HP ?? '' }}" />
									</div>
								</div>
								<div class="form-group row">
									<label class="col-md-4 col-form-label" for="Instantsi">Alamat Instansi </label>
									<div class="col-md-8">
										<textarea id="AlamatInstansiAyah" name="AlamatInstansiAyah" class="form-control">{{ $sql_ayah->AlamatInstansi ?? '' }}</textarea>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="card">
						<h5 class="card-header bg-primary text-white">Ibu</h5>
						<div class="card-body">
							<div class="form-horizontal">
								<input type="hidden" id="IDIbu" name="IDIbu" class="span12" value="{{ $sql_ibu->ID ?? '' }}" />

								<div class="form-group row">
									<label class="col-md-4 col-form-label" for="NamaIbu">Nama *</label>
									<div class="col-md-8">
										<input type="text" id="NamaIbu" data-required="true" data-alert="Nama Ibu Harus Diisi" name="NamaIbu" class="form-control" value="{{ $sql_ibu->Nama ?? '' }}" />
									</div>
								</div>

								<div class="form-group row">
									<label class="col-md-4 col-form-label" for="StatusIbu">Wafat</label>
									<div class="col-md-8">
										<input {{ (($sql_ibu->Status ?? '') == 'Tiada') ? 'checked' : '' }} name="StatusIbu" id="StatusIbu" type="checkbox" value="Tiada"> Ya
									</div>
								</div>

								<div class="form-group row">
									<label class="col-md-4 col-form-label" for="TanggalLahir">Tanggal Lahir</label>
									<div class="col-md-8">
										<input type="text" id="TanggalLahirIbu" name="TanggalLahirIbu" class="form-control datepicker" value="{{ gantitanggal($sql_ibu->TanggalLahir ?? '', 'd/m/Y') }}" />
									</div>
								</div>

								<div class="form-group row">
									<label class="col-md-4 col-form-label" for="JenisSekolahIDIbu">Pendidikan/Pekerjaan</label>
									<div class="col-md-4">
										<select id="JenisSekolahIDIbu" name="JenisSekolahIDIbu" class="form-control">
											<option value="" selected="selected">-- {{ __('mahasiswa.select') . ' Pendidikan' }} --</option>
											@foreach (get_all('jenissekolah') as $raw)
												<option {{ ($raw->ID == ($sql_ibu->JenisSekolahID ?? '')) ? "selected" : "" }} value="{{ $raw->ID }}">{{ $raw->Nama }}</option>
											@endforeach
										</select>
									</div>
									<div class="col-md-4">
										<select id="PekerjaanIbu" name="PekerjaanIbu" class="form-control">
											<option value="" selected="selected">-- {{ __('mahasiswa.select') . ' Pekerjaan' }} --</option>
											@foreach (get_all('pekerjaan') as $raw)
												<option {{ ($raw->ID == ($sql_ibu->PekerjaanID ?? '')) ? "selected" : "" }} value="{{ $raw->ID }}">{{ $raw->Nama }}</option>
											@endforeach
										</select>
									</div>
								</div>

								<div class="form-group row">
									<label class="col-md-4 col-form-label" for="PenghasilanIbu">Penghasilan</label>
									<div class="col-md-8">
										<select id="PenghasilanIbu" name="PenghasilanIbu" class="form-control">
											<option value="" selected="selected">-- {{ __('mahasiswa.select') . ' Penghasilan' }} --</option>
											@foreach (get_all('penghasilan') as $raw)
												<option {{ ($raw->ID == ($sql_ibu->PenghasilanID ?? '')) ? "selected" : "" }} value="{{ $raw->ID }}">{{ $raw->Nama }}</option>
											@endforeach
										</select>
									</div>
								</div>

								<div class="form-group row">
									<label class="col-md-4 col-form-label" for="AgamaIbu">Agama</label>
									<div class="col-md-8">
										<select id="AgamaIbu" name="AgamaIbu" class="form-control">
											<option value="" selected="selected">-- {{ __('mahasiswa.select') }} --</option>
											@foreach (get_all('agama') as $raw)
												<option {{ ($raw->ID == ($sql_ibu->AgamaID ?? '')) ? "selected" : "" }} value="{{ $raw->ID }}">{{ $raw->Nama }}</option>
											@endforeach
										</select>
									</div>
								</div>

								<div class="form-group row">
									<label class="col-md-4 col-form-label" for="KecamatanIbu">Kecamatan</label>
									<div class="col-md-8">
										<select class="wilayah form-control" id="KecamatanIbu" name="KecamatanIbu">
										</select>
									</div>
								</div>

								<div class="form-group row">
									<label class="col-md-4 col-form-label" for="KelurahanIbu">Kelurahan </label>
									<div class="col-md-8">
										<input type="text" id="KelurahanIbu" name="KelurahanIbu" class="form-control" value="{{ $sql_ibu->Kelurahan ?? '' }}" />
									</div>
								</div>

								<div class="form-group row">
									<label class="col-md-4 col-form-label" for="KodePosIbu">Kode Pos </label>
									<div class="col-md-8">
										<input type="text" id="KodePosIbu" name="KodePosIbu" class="form-control" value="{{ $sql_ibu->KodePos ?? '' }}" />
									</div>
								</div>

								<div class="form-group row">
									<label class="col-md-4 col-form-label" for="AlamatIbu">Alamat * </label>
									<div class="col-md-8">
										<textarea id="AlamatIbu" name="AlamatIbu" class="form-control" data-required="true" data-alert="Alamat Ibu Harus Diisi">{{ $sql_ibu->Alamat ?? '' }}</textarea>
									</div>
								</div>

								<div class="form-group row">
									<label class="col-md-4 col-form-label" for="TeleponIbu">Telepon * /Email </label>
									<div class="col-md-4">
										<input type="text" id="TeleponIbu" name="TeleponIbu" class="form-control" data-required="true" data-alert="Telepon Ibu Harus Diisi" onkeypress="return event.charCode > 47 && event.charCode < 58;" value="{{ $sql_ibu->Telepon ?? '' }}" />
									</div>
									<div class="col-md-4">
										<input type="email" id="EmailIbu" name="EmailIbu" class="form-control email" value="{{ $sql_ibu->Email ?? '' }}" />
									</div>
								</div>

								<div class="form-group row">
									<label class="col-md-4 col-form-label" for="Instansi">Nama Instansi / HP * </label>
									<div class="col-md-4">
										<input type="text" id="NamaInstansiIbu" name="NamaInstansiIbu" class="form-control" value="{{ $sql_ibu->NamaInstansi ?? '' }}" />
									</div>
									<div class="col-md-4">
										<input type="text" id="HPIbu" name="HPIbu" class="form-control" data-required="true" data-alert="HP Ibu Harus Diisi" onkeypress="return event.charCode > 47 && event.charCode < 58;" value="{{ $sql_ibu->HP ?? '' }}" />
									</div>
								</div>
								<div class="form-group row">
									<label class="col-md-4 col-form-label" for="Instantsi">Alamat Instansi </label>
									<div class="col-md-8">
										<textarea id="AlamatInstansiIbu" name="AlamatInstansiIbu" class="form-control">{{ $sql_ibu->AlamatInstansi ?? '' }}</textarea>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="card" id="TabWali" style="display:{{ (($sql_wali->PenanggungJawab ?? 0) == 1) ? 'block' : 'none' }}">
						<h5 class="card-header bg-primary text-white">Wali</h5>
						<div class="card-body">
							<div class="form-horizontal">
								<input type="hidden" id="IDWali" name="IDWali" class="span12" value="{{ $sql_wali->ID ?? '' }}" />

								<div class="form-group row">
									<label class="col-md-4 col-form-label" for="NamaWali">Nama *</label>
									<div class="col-md-8">
										<input type="text" id="NamaWali" name="NamaWali" class="form-control" value="{{ $sql_wali->Nama ?? '' }}" />
									</div>
								</div>

								<div class="form-group row">
									<label class="col-md-4 col-form-label" for="StatusWali">Wafat</label>
									<div class="col-md-4">
										<input {{ (($sql_wali->Status ?? '') == 'Tiada') ? 'checked' : '' }} name="StatusWali" id="StatusWali" type="checkbox" value="Tiada"> Ya
									</div>
								</div>

								<div class="form-group row">
									<label class="col-md-4 col-form-label" for="TanggalLahir">Tanggal Lahir</label>
									<div class="col-md-8">
										<input type="text" id="TanggalLahirWali" name="TanggalLahirWali" class="form-control datepicker" value="{{ gantitanggal($sql_wali->TanggalLahir ?? '', 'd/m/Y') }}" />
									</div>
								</div>

								<div class="form-group row">
									<label class="col-md-4 col-form-label" for="JenisSekolahIDWali">Pendidikan/Pekerjaan</label>
									<div class="col-md-4">
										<select id="JenisSekolahIDWali" name="JenisSekolahIDWali" class="form-control">
											<option value="" selected="selected">-- {{ __('mahasiswa.select') . ' Pendidikan' }} --</option>
											@foreach (get_all('jenissekolah') as $raw)
												<option {{ ($raw->ID == ($sql_wali->JenisSekolahID ?? '')) ? "selected" : "" }} value="{{ $raw->ID }}">{{ $raw->Nama }}</option>
											@endforeach
										</select>
									</div>
									<div class="col-md-4">
										<select id="PekerjaanWali" name="PekerjaanWali" class="form-control">
											<option value="" selected="selected">-- {{ __('mahasiswa.select') . ' Pekerjaan' }} --</option>
											@foreach (get_all('pekerjaan') as $raw)
												<option {{ ($raw->ID == ($sql_wali->PekerjaanID ?? '')) ? "selected" : "" }} value="{{ $raw->ID }}">{{ $raw->Nama }}</option>
											@endforeach
										</select>
									</div>
								</div>

								<div class="form-group row">
									<label class="col-md-4 col-form-label" for="PenghasilanWali">Penghasilan</label>
									<div class="col-md-8">
										<select id="PenghasilanWali" name="PenghasilanWali" class="form-control">
											<option value="" selected="selected">-- {{ __('mahasiswa.select') . ' Penghasilan' }} --</option>
											@foreach (get_all('penghasilan') as $raw)
												<option {{ ($raw->ID == ($sql_wali->PenghasilanID ?? '')) ? "selected" : "" }} value="{{ $raw->ID }}">{{ $raw->Nama }}</option>
											@endforeach
										</select>
									</div>
								</div>

								<div class="form-group row">
									<label class="col-md-4 col-form-label" for="AgamaWali">Agama</label>
									<div class="col-md-8">
										<select id="AgamaWali" name="AgamaWali" class="form-control">
											<option value="" selected="selected">-- {{ __('mahasiswa.select') }} --</option>
											@foreach (get_all('agama') as $raw)
												<option {{ ($raw->ID == ($sql_wali->AgamaID ?? '')) ? "selected" : "" }} value="{{ $raw->ID }}">{{ $raw->Nama }}</option>
											@endforeach
										</select>
									</div>
								</div>

								<div class="form-group row">
									<label class="col-md-4 col-form-label" for="KecamatanWali">Kecamatan</label>
									<div class="col-md-8">
										<select class="wilayah form-control" id="KecamatanWali" name="KecamatanWali">
										</select>
									</div>
								</div>

								<div class="form-group row">
									<label class="col-md-4 col-form-label" for="KelurahanWali">Kelurahan </label>
									<div class="col-md-8">
										<input type="text" id="KelurahanWali" name="KelurahanWali" class="form-control" value="{{ $sql_wali->Kelurahan ?? '' }}" />
									</div>
								</div>

								<div class="form-group row">
									<label class="col-md-4 col-form-label" for="KodePosWali">Kode Pos </label>
									<div class="col-md-8">
										<input type="text" id="KodePosWali" name="KodePosWali" class="form-control" value="{{ $sql_wali->KodePos ?? '' }}" />
									</div>
								</div>

								<div class="form-group row">
									<label class="col-md-4 col-form-label" for="AlamatWali">Alamat</label>
									<div class="col-md-8">
										<textarea id="AlamatWali" name="AlamatWali" class="form-control">{{ $sql_wali->Alamat ?? '' }}</textarea>
									</div>
								</div>

								<div class="form-group row">
									<label class="col-md-4 col-form-label" for="TeleponWali">Telepon/Email </label>
									<div class="col-md-4">
										<input type="text" id="TeleponWali" name="TeleponWali" class="form-control" onkeypress="return event.charCode > 47 && event.charCode < 58;" value="{{ $sql_wali->Telepon ?? '' }}" />
									</div>
									<div class="col-md-4">
										<input type="email" id="EmailWali" name="EmailWali" class="form-control email" value="{{ $sql_wali->Email ?? '' }}" />
									</div>
								</div>

								<div class="form-group row">
									<label class="col-md-4 col-form-label" for="Instansi">Nama Instansi / HP</label>
									<div class="col-md-4">
										<input type="text" id="NamaInstansiWali" name="NamaInstansiWali" class="form-control" value="{{ $sql_wali->NamaInstansi ?? '' }}" />
									</div>
									<div class="col-md-4">
										<input type="text" id="HPWali" name="HPWali" class="form-control" onkeypress="return event.charCode > 47 && event.charCode < 58;" value="{{ $sql_wali->HP ?? '' }}" />
									</div>
								</div>
								<div class="form-group row">
									<label class="col-md-4 col-form-label" for="Instantsi">Alamat Instansi </label>
									<div class="col-md-8">
										<textarea id="AlamatInstansiWali" name="AlamatInstansiWali" class="form-control">{{ $sql_wali->AlamatInstansi ?? '' }}</textarea>
									</div>
								</div>
							</div>
						</div>
					</div>
			</div>
		</div>
	</div>
</form>

<!-- MODAL LENGKAP PROFIL -->
<div id="myModalLengkapProfil" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLengkapProfil" aria-hidden="true">
	<div class="modal-dialog modal-dialog-scrollable">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title" id="myModalLengkapProfil">Daftar isian yang belum lengkap</h4>
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
			</div>
			<div class="modal-body">
				<div class="row" id="listBelumLengkap">
					<ol>
						@foreach($TidakLengkap as $TL)
							<li>{{ $TL }}</li>
						@endforeach
					</ol>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-danger waves-effect waves-light" data-dismiss="modal">Tutup</button>
			</div>
		</div>
	</div>
</div>

@endsection

@push('scripts')
@php
if (($sql_ayah->KecamatanID ?? '')) $kec_ayah = get_wilayah($sql_ayah->KecamatanID);
if (($sql_ibu->KecamatanID ?? '')) $kec_ibu = get_wilayah($sql_ibu->KecamatanID);
if (($sql_wali->KecamatanID ?? '')) $kec_wali = get_wilayah($sql_wali->KecamatanID);
if (($row->KecamatanSekolah ?? '')) $kec_sekolah = get_wilayah($row->KecamatanSekolah);
if (($row->SekolahID ?? '')) {
	$sekolahdata_arr = get_sekolah('', $row->SekolahID);
    $sekolahdata = !empty($sekolahdata_arr) ? $sekolahdata_arr[0] : null;
    if ($sekolahdata) {
        $sekolahdata->nama = str_replace("'", "\'", $sekolahdata->nama);
    }
}
if (($row->Kewarganegaraan ?? '')) {
    $negara_arr = get_negara('', $row->Kewarganegaraan);
    $negara = !empty($negara_arr) ? $negara_arr[0] : null;
}
@endphp
<script type="text/javascript">
	$('.form-horizontal').on('click', function() {
		$('.btnEdit').addClass("bounce");
		if ($('.btnEdit').css("display") != "none") {
			$('html, body').animate({
				scrollTop: $(".btnEdit").offset().top - 300
			}, 10);
		}
	});

	$('.btnEdit').on('click', function() {
		$(this).removeClass("bounce");
	});

	function batasstudi() {
		$.ajax({
			url: "{{ url('c_kurikulum/batasstudi') }}",
			type: "POST",
			data: {
                _token: "{{ csrf_token() }}",
				Angkatan: $('#Angkatan').val(),
				SemesterMasuk: $('#SemesterMasuk').val(),
				KurikulumID: $('#KurikulumID').val()
			},
			success: function(data) {
				$('#BatasStudi').val(data);
			}
		});
	}

	if ({{ $save ?? 0 }} == 2) {
		@isset($kec_sekolah)
		$('#KecamatanSekolah').select2({
			data: [{
				id: '{{ ($kec_sekolah->Negara ?? "") . "_" . ($kec_sekolah->Kode_Propinsi ?? "") . "_" . ($kec_sekolah->Kode_Kota ?? "") . "_" . ($kec_sekolah->Kode_Kecamatan ?? "") }}',
				text: '{{ $kec_sekolah->Kecamatan ?? "" }}'
			}, ]
		});
		@endisset

		@isset($kec_ayah)
		$('#KecamatanAyah').select2({
			data: [{
				id: '{{ ($kec_ayah->Negara ?? "") . "_" . ($kec_ayah->Kode_Propinsi ?? "") . "_" . ($kec_ayah->Kode_Kota ?? "") . "_" . ($kec_ayah->Kode_Kecamatan ?? "") }}',
				text: '{{ $kec_ayah->Kecamatan ?? "" }}'
			}, ]
		});
		@endisset

		@isset($kec_ibu)
		$('#KecamatanIbu').select2({
			data: [{
				id: '{{ ($kec_ibu->Negara ?? "") . "_" . ($kec_ibu->Kode_Propinsi ?? "") . "_" . ($kec_ibu->Kode_Kota ?? "") . "_" . ($kec_ibu->Kode_Kecamatan ?? "") }}',
				text: '{{ $kec_ibu->Kecamatan ?? "" }}'
			}, ]
		});
		@endisset

		@isset($kec_wali)
		$('#KecamatanWali').select2({
			data: [{
				id: '{{ ($kec_wali->Negara ?? "") . "_" . ($kec_wali->Kode_Propinsi ?? "") . "_" . ($kec_wali->Kode_Kota ?? "") . "_" . ($kec_wali->Kode_Kecamatan ?? "") }}',
				text: '{{ $kec_wali->Kecamatan ?? "" }}'
			}, ]
		});
		@endisset

		@isset($sekolahdata)
		$('#SekolahID').select2({
			data: [{
				id: '{{ $sekolahdata->id ?? "" }}',
				text: '{{ $sekolahdata->nama ?? "" }}'
			}, ]
		});
		@endisset
	}

	$('.wilayah').select2({
		ajax: {
			url: '{{ url('c_propinsi/json_wilayah') }}',
			dataType: 'json',
			delay: 250,
			data: function(params) {
				return {
					q: params.term, // search term
					page: params.page,
					group: '03',
					kode: '0',
				};
			},
			processResults: function(data, params) {
				params.page = params.page || 1;
				return {
					results: data.items
				};
			},
			cache: true
		}
	});

	$('#SekolahID').select2({
		ajax: {
			url: '{{ url('mahasiswa/jsonSekolah') }}',
			dataType: 'json',
			delay: 250,
			data: function(params) {
				return {
					keyword: params.term, // search term
					page: params.page,
					ID: '',
				};
			},
			processResults: function(data, params) {
				params.page = params.page || 1;
				return {
					results: data.items
				};
			},
			cache: true
		}
	});

	@isset($negara)
	$('#Kewarganegaraan').select2({
		data: [{
			id: '{{ $negara->Kode ?? "" }}',
			text: '{{ $negara->Nama ?? "" }}'
		}, ]
	});
	@endisset


	function chjprodi() {
		$.ajax({
			url: "{{ url('c_programstudi/change_by_jenjang') }}",
			type: "POST",
			data: {
                _token: "{{ csrf_token() }}",
				JenjangID: $('#JenjangID').val(),
				ProdiID: '{{ $row->ProdiID ?? "" }}',
			},
			success: function(data) {
				$('#ProdiID').html(data);
				changekelas();
				changekurikulum();
			}
		});
	}

	function changekelas() {
		$.ajax({
			url: "{{ url('c_kelas/changekelas') }}",
			type: "POST",
			data: {
                _token: "{{ csrf_token() }}",
				ProdiID: $('#ProdiID').val(),
				KelasID: '{{ $row->KelasID ?? "" }}'
			},
			success: function(data) {
				$('#KelasID').html(data);
				changekurikulum();
			}
		});
	}

	function changekurikulum() {
		$.ajax({
			url: "{{ url('c_kurikulum/onchange') }}",
			type: "POST",
			data: {
                _token: "{{ csrf_token() }}",
				ProgramID: $('#ProgramID').val(),
				ProdiID: $('#ProdiID').val(),
				KurikulumID: '{{ $row->KurikulumID ?? "" }}'
			},
			success: function(data) {
				var data2 = "<option value=''>-- Pilih Kurikulum --</option>" + data;
				$('#KurikulumID').html(data2);
				batasstudi();
			}
		});
	}

	$('#AsalPT').select2({
		ajax: {
			url: '{{ url('mahasiswa/jsonPT') }}',
			dataType: 'json',
			delay: 250,
			data: function(params) {
				return {
					keyword: params.term, // search term
					page: params.page,
					ID: '',
				};
			},
			processResults: function(data, params) {
				params.page = params.page || 1;
				return {
					results: data.items
				};
			},
			cache: true
		}
	});

	function changeProdiIDPT() {
		var IDProdiPT = $('#ProdiIDPT').val();
		if (IDProdiPT == null || IDProdiPT == '') {
			IDProdiPT = '{{ $row->ProdiIDPT ?? "" }}';
		}

		$.ajax({
			url: "{{ url('mahasiswa/changeProdiPT') }}",
			type: "GET",
			data: {
				IDPT: $('#AsalPT').val(),
				ID: IDProdiPT,
			},
			success: function(data) {
				$('#ProdiIDPT').html(data);
				// if (typeof autocomplete === "function") autocomplete('ProdiIDPT'); 
			}
		});
	}
	changeProdiIDPT();

	function savedata(formz) {
		var alert_n = "";
		$("[data-required='true']").each(function() {
			var notif = $(this).data('alert');
			if ($(this).val() == '') {
				alert_n += '<li style="text-align:left;">' + notif + '</li>';
			}
		});
		if (alert_n != "") {
			swal('Peringatan !', 'Masih ada data wajib yang belum diisi (Bertanda *).<br> Silahkan cek kembali form isian data !<br> <br><span style="display:block;text-align:left;font-weight:bold;">Data Yang Belum Diisi</span> <ul>' + alert_n + '</ul>', 'warning');
			return;
		}


		var formData = new FormData(formz);
		$.ajax({
			type: 'POST',
			url: $(formz).attr('action'),
			data: formData,
			dataType: "JSON",
			cache: false,
			contentType: false,
			processData: false,
			beforeSend: function(r) {
				silahkantunggu();
			},
			success: function(data) {
				if (data.status) {
					if ({{ $save ?? 0 }} == '1') {
						window.location = "{{ url(request()->segment(1)) }}";
					}

					if ({{ $save ?? 0 }} == '2') {
						load_content('{{ request()->segment(1) }}/view/{{ $row->ID ?? "" }}');
					}
					alertsuccess();
					berhasil();
				} else {
					alertfail(data.message);
					berhasil();
				}
			},
			error: function(data) {
				$(".btnSave").html('{{ __("save") }} Data <icon class="icon-check icon-white-t"></icon>');
				$(".btnSave").removeAttr("disabled");
                if (typeof alertfail === "function") alertfail("Terjadi kesalahan sistem");
			}
		});
	}


	function btnEdit(type, checkid) {
		$(".number").attr('disabled', true);
		$(".email").attr('disabled', true);
		$("input:text").attr('disabled', true);
		$("input:file").attr('disabled', true);
		$("input:radio").attr('disabled', true);
		$("button:submit").attr('disabled', true);
		$("select").attr('disabled', true);
		$("textarea").attr('disabled', true);
		$(".btnSave").css('display', 'none');

		if (checkid == 1) {
			$(".number").removeAttr('disabled');
			$(".email").removeAttr('disabled');
			$("input:text").removeAttr('disabled');
			$("#BatasStudi").attr('readonly', true);
			$("input:file").removeAttr('disabled');
			$("input:radio").removeAttr('disabled');
			$("select").removeAttr('disabled');
			$("textarea").removeAttr('disabled');
			$("button:submit").removeAttr('disabled');
			$(".btnEdit").fadeOut(0);
			$(".btnSave").fadeIn(0);
		}

	}
	btnEdit({{ $save ?? 0 }});
	chjprodi();
	changekelas();


	$(".datepicker").datepicker({
		dateFormat: 'dd/mm/yy',
		yearRange: '1900:{{ date("Y") + 5 }}',
		changeMonth: true,
		changeYear: true
	});
	$(".datepicker").css("margin-right", "5px");
	$(".datepicker").attr('readOnly', 'true');
	$('[data-toggle="status"]').click(function() {
		var status = $(this).attr('value');
		if (status == 'B') {
			$("#Pindahan").hide();
		} else {
			$("#Pindahan").show();
		}
	});

	function penanggungjawab() {
		var penanggung = $('#PJID').val();
		if (penanggung == 'Wali') {
			$("#TabWali").show();
		} else {
			$("#TabWali").hide();
		}
	}

	function changeKota() {
		$.ajax({
			url: "{{ url('mahasiswa/changeWilayah') }}",
			type: "POST",
			data: {
                _token: "{{ csrf_token() }}",
				group: '02',
				parent: $('#Propinsi').val(),
				mhswID: $('#ID').val()
			},
			success: function(data) {
				$('#Kota').html(data);
				// if (typeof autocomplete === "function") autocomplete('Kota');
				changeKecamatan();
			}
		});
	}
	changeKota();


	function changeKecamatan() {
		$.ajax({
			url: "{{ url('mahasiswa/changeWilayah') }}",
			type: "POST",
			data: {
                _token: "{{ csrf_token() }}",
				group: '03',
				parent: $('#Kota').val(),
				mhswID: $('#ID').val()
			},
			success: function(data) {
				$('#Kecamatan').html(data);
				// if (typeof autocomplete === "function") autocomplete('Kecamatan');
			}
		});
	}

	// if (typeof autocomplete === "function") {
    //     autocomplete('Propinsi');
    //     autocomplete('JurusanSekolahID');
    // }

	$('#Kewarganegaraan').select2({
		ajax: {
			url: '{{ url('mahasiswa/jsonNegara') }}',
			dataType: 'json',
			delay: 250,
			data: function(params) {
				return {
					keyword: params.term, // search term
					page: params.page,
					ID: '',
				};
			},
			processResults: function(data, params) {
				params.page = params.page || 1;
				return {
					results: data.items
				};
			},
			cache: true
		}
	});

	function change_no_kps() {
		var a = $('[name=PenerimaKPS]').val();
		if (a == 1) {
			$('#no_kps_div').show();
		} else {
			$('#no_kps_div').hide();
		}
	}
	change_no_kps();

	function change_input_sekolah() {
		var cara_input_sekolah = $('#cara_input_sekolah').val();
		if (cara_input_sekolah == 1) {
			$('#cara_input_sekolah').val('2');
			$('#asalsekolah_div').hide();
			$('#asalsekolah_custom').show();
		} else {
			$('#cara_input_sekolah').val('1');
			$('#asalsekolah_div').show();
			$('#asalsekolah_custom').hide();
		}
	}

	$(document).ready(function() {
        
        function cekIdentitas() {
            var jenis = $('#JenisIdentitas').val();
            var input = $('#NoIdentitas');
            var info  = $('#info_identitas');
            var label = $('#label_dinamis'); 
            var err   = $('#err_identitas');

            err.hide();

            if (jenis == 'KTP') {
                label.text('No KTP');          
                info.text('* Harus 16 Digit Angka'); 
                
                input.attr('maxlength', '16');
                input.attr('placeholder', '16 Digit Angka');
            } 
            else if (jenis == 'PASPOR') {
                label.text('No Paspor');       
                info.text('* Minimal 7 Karakter (Huruf & Angka)'); 
                
                input.attr('maxlength', '20');
                input.attr('placeholder', 'Contoh: A1234567');
            }
        }

        cekIdentitas();
        $('#JenisIdentitas').on('change', function() {
            $('#NoIdentitas').val(''); 
            cekIdentitas();
        });


        $('#NoIdentitas').on('input keyup paste', function() {
            var jenis = $('#JenisIdentitas').val();
            var val   = $(this).val();
            var cleanVal = val;
            var msg = "";

            if (jenis == 'KTP') {
                cleanVal = val.replace(/[^0-9]/g, '');

                if (cleanVal.length !== 16 && cleanVal.length > 0) {
                    msg = "KTP harus 16 digit.";
                }
            } 
            else if (jenis == 'PASPOR') {
                cleanVal = val.replace(/[^a-zA-Z0-9]/g, '');

                if (cleanVal.length < 7 && cleanVal.length > 0) {
                    msg = "Paspor minimal 7 karakter.";
                }
            }

            if (val !== cleanVal) {
                $(this).val(cleanVal);
            }

            if (msg !== "") {
                $('#err_identitas').text(msg).show();
            } else {
                $('#err_identitas').hide();
            }
        });

    });
</script>
@endpush