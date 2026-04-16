<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\GenerateTagihanService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class GenerateTagihanController extends Controller
{
    protected $service;

    public function __construct(GenerateTagihanService $service)
    {
        $this->service = $service;

        $this->middleware(function ($request, $next) {
            $lang = Session::get('language');
            if (!$lang && !$request->cookie('language')) {
                $lang = 'indonesia';
                Session::put('language', $lang);
            }

            $locale = ($lang === 'indonesia' || $lang === 'id') ? 'id' : 'en';
            app()->setLocale($locale);

            return $next($request);
        });
    }

    public function index()
    {
        if (function_exists('log_akses')) {
            log_akses('View', 'Melihat Halaman Generate Tagihan');
        }

        return view('generate_tagihan.v');
    }

    public function searchMahasiswa(Request $request)
    {
        $tahun_aktif = DB::table('tahun')->where('ProsesBuka', 1)->first();
        $tahun_yg_dipilih = DB::table('tahun')->where('ID', $request->TahunID)->first();

        $result = $this->service->searchMahasiswa(
            $request->ProgramID,
            $request->ProdiID,
            $request->Angkatan,
            $request->TahunID,
            $request->JenisPendaftaran,
            $request->JalurPendaftaran,
            $request->SemesterMasuk,
            $request->GelombangKe,
            $tahun_yg_dipilih
        );

        $temp = '';
        $temp_tidak_aktif = '';
        $jumlahTidakAktif = 0;

        if (count($result) > 0) {
            foreach ($result as $value) {
                if (isset($value->tidakAktif) && $value->tidakAktif > 0) {
                    $jumlahTidakAktif += 1;
                    $temp_tidak_aktif .= '<tr>';
                    $temp_tidak_aktif .= '<td style="text-align:center">' . $jumlahTidakAktif . '</td>';
                    $temp_tidak_aktif .= '<td style="text-align:center">' . $value->NPM . '</td>';
                    $temp_tidak_aktif .= '<td>' . $value->Nama . '</td>';
                    $temp_tidak_aktif .= '</tr>';
                }
                $temp .= '<option value="' . $value->ID . '">' . $value->NPM . ' | ' . $value->Nama . '</option>';
            }
        } else {
            $temp = '<option value="">Maaf, mahasiswa tidak ditemukan</option>';
        }

        return response()->json([
            'temp' => $temp,
            'jumlah' => count($result),
            'temp_tidak_aktif' => $temp_tidak_aktif,
            'jumlahTidakAktif' => $jumlahTidakAktif
        ]);
    }

    public function changeAngkatan(Request $request)
    {
        $all_tahun = DB::table('mahasiswa')
            ->select('TahunMasuk')
            ->whereNotNull('NPM')
            ->where('TahunMasuk', '!=', '')
            ->distinct()
            ->orderBy('TahunMasuk', 'DESC')
            ->get();

        $option = '';
        foreach ($all_tahun as $tahun) {
            $option .= '<option value="' . $tahun->TahunMasuk . '">' . $tahun->TahunMasuk . '</option>';
        }

        return response($option);
    }

    public function content_biaya(Request $request)
    {
        $data = $this->service->getBiayaContent($request->all());

        return view('generate_tagihan.s_content_biaya', $data);
    }

    public function generate_tagihan(Request $request)
    {
        $post = $request->all();

        if (!isset($post['jumlahdetail'])) {
            $post['jumlahdetail'] = [];
        }

        $detailjumlah = [];
        if (count($post['jumlahdetail']) > 0) {
            foreach ($post['jumlahdetail'] as $jb_pr => $sub) {
                foreach ($sub as $key => $value) {
                    $detailjumlah[$jb_pr][$key] = $value;
                }
            }
        }

        $result = $this->service->generateTagihan($post, $detailjumlah);

        return response()->json($result);
    }

    public function excel()
    {
        $tagihan = DB::table('draft_tagihan_mahasiswa')
            ->join('tmp_generate_tagihan', 'tmp_generate_tagihan.A', '=', 'draft_tagihan_mahasiswa.NoInvoice')
            ->join('jenisbiaya', 'jenisbiaya.ID', '=', 'draft_tagihan_mahasiswa.JenisBiayaID')
            ->select(
                'A', 'B', 'C',
                'jenisbiaya.Nama',
                'draft_tagihan_mahasiswa.Jumlah',
                'draft_tagihan_mahasiswa.Periode',
                'E'
            )
            ->get();

        $tagihan_all = [];
        foreach ($tagihan as $all_tagihan) {
            $tahunID = $all_tagihan->Periode;
            $tagihan_all[$all_tagihan->B][] = $all_tagihan->Jumlah;
        }

        $total_tagihan = [];
        $rowspan = [];
        $data_mahasiswa = null;

        foreach ($tagihan_all as $NPM => $tagihan_list) {
            $data_mahasiswa = DB::table('mahasiswa')->where('NPM', $NPM)->first();
            $rowspan[$NPM] = count($tagihan_list);
            foreach ($tagihan_list as $index => $nominal) {
                $total_tagihan[$NPM] = ($total_tagihan[$NPM] ?? 0) + $nominal;
            }
        }

        $data['query'] = $tagihan->toArray();
        $data['Tahun'] = get_field($tahunID ?? '', 'tahun');
        $data['Program'] = get_field($data_mahasiswa->ProgramID ?? '', 'program');
        $data['Prodi'] = get_field($data_mahasiswa->ProdiID ?? '', 'programstudi');
        $data['Angkatan'] = $data_mahasiswa->TahunMasuk ?? '';
        $data['Semester'] = ($data_mahasiswa->TahunMasuk == '1') ? "Ganjil" : "Genap";
        $data['Jalur'] = DB::table('pmb_edu_jalur_pendaftaran')->where('id', $data_mahasiswa->jalur_pmb ?? '')->value('nama') ?? '';
        $data['total_tagihan'] = $total_tagihan;
        $data['rowspan'] = $rowspan;

        return view('generate_tagihan.ex', $data);
    }
}
