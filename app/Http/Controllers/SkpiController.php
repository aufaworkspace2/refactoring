<?php

namespace App\Http\Controllers;

use App\Services\SkpiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class SkpiController extends Controller
{
    protected $service;
    public $Create;
    public $Update;
    public $Delete;

    public function __construct(SkpiService $service)
    {
        $this->service = $service;

        $this->middleware(function ($request, $next) {
            if (!Session::get('username')) {
                return redirect('/');
            }

            $lang = Session::get('language');
            if (!$lang && !$request->cookie('language')) {
                $lang = 'indonesia';
                Session::put('language', $lang);
            }

            $locale = ($lang === 'indonesia' || $lang === 'id') ? 'id' : 'en';
            app()->setLocale($locale);

            $levelUser = Session::get('LevelUser');
            $this->Create = cek_level($levelUser, 'c_skpi', 'Create');
            $this->Update = cek_level($levelUser, 'c_skpi', 'Update');
            $this->Delete = cek_level($levelUser, 'c_skpi', 'Delete');

            return $next($request);
        });
    }

    /**
     * Display main SKPI Identitas view
     */
    public function index(Request $request, $offset = 0)
    {
        $data['Create'] = $this->Create;
        $data['Update'] = $this->Update;
        $data['Delete'] = $this->Delete;
        $data['Jenis'] = 'Transaksi';

        if (function_exists('log_akses')) {
            log_akses('View', 'Melihat Daftar Data Mahasiswa');
        }

        return view('skpi.v_identitas', $data);
    }

    /**
     * Search SKPI data with filters
     */
    public function search(Request $request, $offset = 0)
    {
        $filters = [
            'ProgramID' => $request->input('ProgramID', ''),
            'ProdiID' => $request->input('ProdiID', ''),
            'StatusMhswID' => $request->input('StatusMhswID', ''),
            'TahunMasuk' => $request->input('TahunMasuk', ''),
            'KelasID' => $request->input('KelasID', ''),
            'keyword' => $request->input('keyword', '')
        ];

        $limit = 10;
        $jml = $this->service->count_all($filters);
        $data['offset'] = $offset;

        $data['query'] = $this->service->get_data($limit, $offset, $filters);
        $data['link'] = load_pagination($jml, $limit, $offset, 'search', 'filter');
        $data['total_row'] = total_row($jml, $limit, $offset);

        $data['Create'] = $this->Create;
        $data['Update'] = $this->Update;
        $data['Delete'] = $this->Delete;

        return view('skpi.s_identitas', $data);
    }

    /**
     * Display add form
     */
    public function add()
    {
        $data['save'] = 1;
        $data['btn'] = 'Tambah';

        return view('skpi.f_identitas', $data);
    }

    /**
     * Display view/edit form
     */
    public function view($id)
    {
        $data['row'] = $this->service->get_id($id);
        $data['MhswID'] = $id;
        $data['ID'] = $id;
        $data['save'] = 2;
        $data['btn'] = 'Ubah';

        return view('skpi.f_daftar_identitas', $data);
    }

    /**
     * Save SKPI data
     */
    public function save(Request $request, $save)
    {
        $ID = $request->input('MhswID');
        $input['MhswID'] = $ID;
        $input['IjinProdi'] = $request->input('IjinProdi');
        $input['Persyaratan'] = $request->input('Persyaratan');
        $input['Bahasa'] = $request->input('Bahasa');
        $input['PendidikanLanjut'] = $request->input('PendidikanLanjut');
        $input['StatusProfesi'] = $request->input('Status');
        $input['SistemPenilaian'] = $request->input('Penilaian');
        $input['TanggalKelulusan'] = tgl($request->input('TanggalKelulusan'), '05');
        $input['NoIjazah'] = $request->input('NoIjazah');
        $input['Gelar'] = $request->input('Gelar');
        $input['LamaStudi'] = $request->input('LamaStudi');
        $input['IPK'] = $request->input('IPK');
        $input['SKS'] = $request->input('SKS');

        $cek = $this->service->checkSkpiExists($ID);

        if ($save == 1 && !$cek) {
            $npm = DB::table('mahasiswa')->where('ID', $ID)->value('NPM');
            if (function_exists('logs')) {
                logs("Menambah data {$npm} pada tabel skpi");
            }

            $input['createAt'] = date('Y-m-d H:i:s');
            $this->service->add($input);

            // Save capaian if exists
            $capaian = $request->input('capaian', []);
            if (!empty($capaian)) {
                foreach ($capaian as $idCapaian => $inen) {
                    foreach ($inen as $bahasa => $value) {
                        $insert[$bahasa] = $value;
                    }
                    DB::table('t_pencapaian')
                        ->updateOrInsert(
                            ['MhswID' => $ID, 'CapaiID' => $idCapaian],
                            $insert
                        );
                }
            }

            // Save informasi if exists
            $informasi = $request->input('informasi', []);
            if (!empty($informasi)) {
                foreach ($informasi as $idInformasi => $inen) {
                    foreach ($inen as $bahasa => $value) {
                        $insert2['MhswID'] = $ID;
                        $insert2['InformasiID'] = $idInformasi;
                        $insert2[$bahasa] = $value;
                    }
                    DB::table('t_informasi_baru')
                        ->updateOrInsert(
                            ['MhswID' => $ID, 'InformasiID' => $idInformasi],
                            $insert2
                        );
                }
            }

            return response()->json(['status' => 'success', 'id' => DB::getPdo()->lastInsertId()]);

        } elseif ($save == 2 && $cek) {
            $npm = DB::table('mahasiswa')->where('ID', $ID)->value('NPM');
            if (function_exists('logs')) {
                logs("Mengubah data {$npm} pada tabel skpi");
            }

            $this->service->edit(['MhswID' => $ID], $input);

            // Update capaian
            $capaian = $request->input('capaian', []);
            if (!empty($capaian)) {
                foreach ($capaian as $idCapaian => $inen) {
                    foreach ($inen as $bahasa => $value) {
                        $insert[$bahasa] = $value;
                    }
                    if (!empty($insert[$bahasa])) {
                        DB::table('t_pencapaian')
                            ->updateOrInsert(
                                ['MhswID' => $ID, 'CapaiID' => $idCapaian],
                                $insert
                            );
                    } else {
                        DB::table('t_pencapaian')
                            ->where(['MhswID' => $ID, 'CapaiID' => $idCapaian])
                            ->update([
                                'IsiIndonesia' => DB::raw('NULL'),
                                'IsiInggris' => DB::raw('NULL')
                            ]);
                    }
                }
            }

            // Update informasi
            $informasi = $request->input('informasi', []);
            if (!empty($informasi)) {
                foreach ($informasi as $idInformasi => $inen) {
                    foreach ($inen as $bahasa => $value) {
                        $insert2[$bahasa] = str_replace(' ', '', $value);
                        $insert2['MhswID'] = $ID;
                        $insert2['InformasiID'] = $idInformasi;
                    }

                    $existing = DB::table('t_informasi_baru')
                        ->where(['MhswID' => $ID, 'InformasiID' => $idInformasi])
                        ->first();

                    if ($existing) {
                        if (empty($insert2[$bahasa])) {
                            DB::table('t_informasi_baru')
                                ->where(['MhswID' => $ID, 'InformasiID' => $idInformasi])
                                ->update([
                                    'IsiIndonesia' => DB::raw('NULL'),
                                    'IsiInggris' => DB::raw('NULL')
                                ]);
                        } else {
                            DB::table('t_informasi_baru')
                                ->where(['MhswID' => $ID, 'InformasiID' => $idInformasi])
                                ->update($insert2);
                        }
                    } else {
                        if (!empty($insert2[$bahasa])) {
                            DB::table('t_informasi_baru')->insert($insert2);
                        }
                    }
                }
            }

            return response()->json(['status' => 'success']);
        }

        return response()->json(['status' => 'error']);
    }

    /**
     * Delete SKPI records
     */
    public function delete(Request $request)
    {
        $checkid = $request->input('checkID', []);

        foreach ($checkid as $id) {
            $row = DB::table('skpi')->where('ID', $id)->first();
            $npm = DB::table('mahasiswa')->where('ID', $row->MhswID ?? $id)->value('NPM');

            if (function_exists('log_akses')) {
                log_akses('Hapus', "Menghapus Data Mahasiswa Dengan NIM {$npm}");
            }

            $this->service->delete($id, 'skpi');
            $this->service->deleteCapaiInfo($id);
        }

        $scripts = '';
        foreach ($checkid as $id) {
            $scripts .= "$('.mahasiswa_{$id}').remove();filter(null, 1);";
        }

        return response($scripts)->header('Content-Type', 'text/html');
    }

    /**
     * Load form identitas for specific mahasiswa
     */
    public function load_form_identitas(Request $request)
    {
        $MhswID = $request->input('MhswID');
        $ProdiID = $request->input('ProdiID');

        $data = $this->service->get_id($MhswID);
        $data['MhswID'] = $MhswID;
        $data['save'] = $data ? 2 : 1;
        $data['btn'] = $data ? 'Ubah' : 'Tambah';

        return view('skpi.f_daftar_identitas', $data);
    }

    /**
     * Get mahasiswa by ProdiID for dropdown
     */
    public function changemahasiswaprodi(Request $request)
    {
        $ProdiID = $request->input('ProdiID');
        $mahasiswa = $this->service->get_mahasiswa_by_prodi($ProdiID);

        $options = '<option value="">-- Pilih Mahasiswa --</option>';
        foreach ($mahasiswa as $m) {
            $options .= "<option value='{$m->ID}'>{$m->NPM} || {$m->Nama}</option>";
        }

        return response($options)->header('Content-Type', 'text/html');
    }

    /**
     * Print SKPI to PDF
     */
    public function pdf(Request $request)
    {
        $ID = $request->input('ID');

        // Get data for PDF
        $mahasiswa = DB::table('mahasiswa')->where('ID', $ID)->first();
        $prodi = DB::table('programstudi')->where('ID', $mahasiswa->ProdiID)->first();
        $jenjang = DB::table('jenjang')->where('ID', $prodi->JenjangID)->first();

        $data['mahasiswa'] = $mahasiswa;
        $data['prodi'] = $prodi;
        $data['jenjang'] = $jenjang;
        $data['row'] = DB::table('skpi')->where('MhswID', $ID)->first();
        $data['wisudawan'] = DB::table('wisudawan')->where('MhswID', $ID)->first();
        $data['data_kelulusan'] = DB::table('keteranganstatusmahasiswa')
            ->where('MhswID', $ID)
            ->where('StatusMahasiswaID', '1')
            ->orderBy('ID', 'DESC')
            ->first();
        
        // Get data_skpi if table exists
        try {
            $data['data_skpi'] = DB::table('data_skpi')->where('ProdiID', $mahasiswa->ProdiID)->first();
        } catch (\Exception $e) {
            // Table doesn't exist, set to null
            $data['data_skpi'] = null;
        }

        // Get Kategori Pencapaian
        $kategoriPencapaian = DB::table('tbl_kategori_pencapaian')
            ->orderBy('Urut', 'ASC')
            ->get();
        $listKPID = $kategoriPencapaian->pluck('ID')->toArray();

        // Get Pencapaian
        if (!empty($listKPID)) {
            $pencapaian = DB::table('m_pencapaian')
                ->select('m_pencapaian.*', 't_pencapaian.MhswID', 't_pencapaian.CapaiID',
                    't_pencapaian.IsiIndonesia', 't_pencapaian.IsiInggris')
                ->leftJoin('t_pencapaian', function($join) use ($ID) {
                    $join->on('m_pencapaian.ID', '=', 't_pencapaian.CapaiID')
                        ->where('t_pencapaian.MhswID', '=', $ID);
                })
                ->leftJoin('tbl_kategori_pencapaian', 'tbl_kategori_pencapaian.ID', '=', 'm_pencapaian.KategoriPencapaianID')
                ->whereIn('m_pencapaian.KategoriPencapaianID', $listKPID)
                ->whereRaw("FIND_IN_SET(?, m_pencapaian.ProdiID)", [$prodi->ID])
                ->orderBy('tbl_kategori_pencapaian.Urut', 'ASC')
                ->orderByRaw('CAST(SUBSTRING_INDEX(m_pencapaian.Kode,".",1) AS UNSIGNED)')
                ->orderByRaw('CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(m_pencapaian.Kode,".",2),".",-1) AS UNSIGNED)')
                ->get();

            $arrPencapaian = [];
            foreach ($pencapaian as $p) {
                $arrPencapaian[$p->KategoriPencapaianID][] = $p;
            }
            $data['M_Pencapaian'] = $arrPencapaian;
        }

        // Get Informasi
        $informasi = DB::table('m_informasi')
            ->select('m_informasi.*', DB::raw('GROUP_CONCAT(t_informasi_baru.ID) as GID'))
            ->leftJoin('t_informasi_baru', function($join) use ($ID) {
                $join->on('m_informasi.ID', '=', 't_informasi_baru.InformasiID')
                    ->where('t_informasi_baru.MhswID', '=', $ID)
                    ->where('t_informasi_baru.approve', '1');
            })
            ->whereRaw("FIND_IN_SET(?, m_informasi.ProdiID)", [$prodi->ID])
            ->groupBy('m_informasi.ID')
            ->get();

        $data['M_Informasi'] = $informasi;

        // Get Ka Prodi
        $kaProdi = DB::table('dosen')->where('ID', $prodi->KaProdiID)->first();
        if ($kaProdi) {
            $tKetuaProd = $kaProdi->Title ? $kaProdi->Title . '. ' : '';
            $gKetuaProd = $kaProdi->Gelar ? ', ' . $kaProdi->Gelar : '';
            $data['NamaKetuaProdi'] = $tKetuaProd . $kaProdi->Nama . $gKetuaProd;
            $data['NIP'] = $kaProdi->NIP;
            $data['NUPTKKAProd'] = $kaProdi->NUPTK;
        } else {
            $data['NamaKetuaProdi'] = '';
            $data['NIP'] = '';
            $data['NUPTKKaProd'] = '';
        }

        // Get Catatan Resmi
        $catatanResmi = DB::table('catatan_resmi_skpi')->get();
        foreach ($catatanResmi as $cr) {
            $data['CatatanResmi']['Indonesia'][] = $cr->Nama;
            $data['CatatanResmi']['Inggris'][] = $cr->NamaInggris;
        }

        // Get Ketua
        $ketua = DB::table('karyawan')->where('Jabatan1', '1')->first();
        if ($ketua) {
            $tKetua = $ketua->Title ? $ketua->Title . '. ' : '';
            $gKetua = $ketua->Gelar ? ', ' . $ketua->Gelar : '';
            $data['NamaKetua'] = $tKetua . $ketua->Nama . $gKetua;
            $data['NIPKetua'] = $ketua->NIP;
        } else {
            $data['NamaKetua'] = '';
            $data['NIPKetua'] = '';
        }

        // Get identitas (for backward compatibility)
        try {
            $data['identitas'] = DB::table('identitas')->first();
        } catch (\Exception $e) {
            $data['identitas'] = null;
        }

        // Alias for dataWisuda (for backward compatibility with view)
        $data['dataWisuda'] = $data['wisudawan'];

        $pdf = \PDF::loadView('skpi.p_skpi', $data);
        $pdf->setPaper('A4', 'P');
        return $pdf->stream('SKPI_' . $mahasiswa->NPM . '.pdf');
    }

    /**
     * Load info modal for PDF printing
     */
    public function loadinfo($id)
    {
        $data['mhwsID'] = $id;
        return view('skpi.loadinfo', $data);
    }
}
