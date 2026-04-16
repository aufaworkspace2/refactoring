<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ResetUsmPmbService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class ResetUsmPmbController extends Controller
{
    protected $service;
    public $Create;
    public $Update;
    public $Delete;

    public function __construct(ResetUsmPmbService $service)
    {
        $this->service = $service;

        $this->middleware(function ($request, $next) {
            if (!Session::get('username')) { return redirect('/'); }
            $lang = Session::get('language');
            if (!$lang && !request()->cookie('language')) { $lang = 'indonesia'; Session::put('language', $lang); }
            $locale = ($lang === 'indonesia' || $lang === 'id') ? 'id' : 'en';
            app()->setLocale($locale);
            $levelUser = Session::get('LevelUser');
            $this->Create = cek_level($levelUser, 'c_reset_usm_pmb', 'Create');
            $this->Update = cek_level($levelUser, 'c_reset_usm_pmb', 'Update');
            $this->Delete = cek_level($levelUser, 'c_reset_usm_pmb', 'Delete');
            return $next($request);
        });
    }

    public function index(Request $request, $offset = 0, $bayar = 1)
    {
        $data['Create'] = $this->Create;
        $data['bayar'] = $bayar;

        if (function_exists('log_akses')) { log_akses('View', 'Melihat Menu Reset Nilai USM'); }

        return view('reset_usm_pmb.v_reset_usm_pmb', $data);
    }

    public function search(Request $request, $offset = 0)
    {
        $post = $request->all();
        $whr = '';
        $limit = 10;
        $bayar = 1;
        $linkurlpage = "?1";

        // Filter Gelombang
        if (!empty($post['gelombang'])) {
            $whr .= " and pmb_tbl_gelombang.ID='" . $post['gelombang'] . "' ";
            $linkurlpage .= "&gelombang=" . $post['gelombang'];
        }

        // Filter Gelombang Detail
        if (!empty($post['gelombang_detail'])) {
            $whr .= " and mahasiswa.gelombang_detail_pmb='" . $post['gelombang_detail'] . "'";
            $linkurlpage .= "&gelombang_detail=" . $post['gelombang_detail'];
        }

        // Filter Jalur Pendaftaran
        if (!empty($post['jalur_pendaftaran'])) {
            $whr .= " and mahasiswa.jalur_pmb='" . $post['jalur_pendaftaran'] . "'";
            $linkurlpage .= "&jalur_pendaftaran=" . $post['jalur_pendaftaran'];
        }

        // Filter Program
        if (!empty($post['program'])) {
            $whr .= " and mahasiswa.ProgramID='" . $post['program'] . "'";
            $linkurlpage .= "&program=" . $post['program'];
        }

        // Filter Pilihan 1
        if (!empty($post['pilihan1'])) {
            $whr .= " and mahasiswa.pilihan1='" . $post['pilihan1'] . "'";
            $linkurlpage .= "&pilihan1=" . $post['pilihan1'];
        }

        // Filter Pilihan 2
        if (!empty($post['pilihan2'])) {
            $whr .= " and mahasiswa.pilihan2='" . $post['pilihan2'] . "'";
            $linkurlpage .= "&pilihan2=" . $post['pilihan2'];
        }

        // Filter Status Test
        $having = '';
        if (!empty($post['statustest'])) {
            if ($post['statustest'] == 'selesai') {
                $having = "HAVING (jumlahSelesai >= jumlahUjian AND jumlahUjian != 0)";
            } else {
                $having = "HAVING (jumlahSelesai < jumlahUjian OR jumlahUjian = 0)";
            }
            $linkurlpage .= "&statustest=" . $post['statustest'];
        }

        // Filter Status Lulus PMB
        if (!empty($post['statuslulus_pmb'])) {
            if ($post['statuslulus_pmb'] == '3') {
                $whr .= "and (mahasiswa.statuslulus_pmb IS NULL or mahasiswa.statuslulus_pmb = '0' or mahasiswa.statuslulus_pmb = '')";
            } else {
                if ($post['statuslulus_pmb'] == '1') {
                    $whr .= "and mahasiswa.statuslulus_pmb = '1'";
                } elseif ($post['statuslulus_pmb'] == '2') {
                    $whr .= "and mahasiswa.statuslulus_pmb = '2'";
                }
            }
            $linkurlpage .= "&statuslulus_pmb=" . $post['statuslulus_pmb'];
        }

        // Filter Keyword
        if (!empty($post['keyword'])) {
            $whr .= " and (mahasiswa.noujian_pmb like '%" . $post['keyword'] . "%' or mahasiswa.Nama like '%" . $post['keyword'] . "%')";
            $linkurlpage .= "&keyword=" . $post['keyword'];
        }

        // Order By
        $ord_tbh = !empty($post['orderby']) ? $post['orderby'] : 'mahasiswa.Nama';
        $ord_asc = !empty($post['descasc']) ? $post['descasc'] : 'asc';
        $linkurlpage .= "&orderby=$ord_tbh&descasc=$ord_asc";

        $orderby_calon = $having . " order by $ord_tbh $ord_asc, mahasiswa.ID desc limit $offset, $limit";
        $orderby_count_calon = $having . " order by $ord_tbh $ord_asc, mahasiswa.ID desc";

        $jml = $this->service->countVerifikasiPMB($whr, $bayar, $orderby_count_calon);
        $data['offset'] = $offset;
        $data['linkurlpage'] = $linkurlpage;

        $query = $this->service->getMahasiswaPMB($whr, $bayar, $orderby_calon);

        $tempQuery = [];
        foreach ($query as $row) {
            $row = (array) $row;
            $id = $row['ID'];

            if ($row['statuslulus_pmb'] == "1") {
                $row['statuslulus_str'] = "<label class='badge badge-success'>Lulus</label>";
                $row['textubah_statuslulus'] = "<label class='badge badge-secondary'>Batalkan Lulus</label>";
            } else if ($row['statuslulus_pmb'] == "2") {
                $row['statuslulus_str'] = "<label class='badge badge-danger'>Tidak Lulus</label>";
                $row['textubah_statuslulus'] = "<label class='badge badge-secondary'>Batalkan Tidak Lulus</label>";
            } else {
                $row['statuslulus_str'] = "<label class='badge badge-secondary'>Belum Lulus</label>";
                $row['textubah_statuslulus'] = '';
            }

            $tempQuery[] = (object) $row;
        }

        $data['jmlVerif'] = $this->service->countVerifikasiPMB('', '0', '');
        $data['query'] = $tempQuery;
        $data['link'] = load_pagination($jml, $limit, $offset, 'search', 'filter');
        $data['total_row'] = total_row($jml, $limit, $offset);
        $data['Update'] = $this->Update;
        $data['Delete'] = $this->Delete;
        $data['bayar'] = $bayar;

        return view('reset_usm_pmb.s_reset_usm_pmb', $data);
    }

    public function save(Request $request)
    {
        try {
            $request->validate([
                'checkID' => 'required|array|min:1',
                'checkID.*' => 'required|integer|exists:mahasiswa,ID'
            ], [
                'checkID.required' => 'Pilih minimal 1 data untuk direset',
                'checkID.array' => 'Data tidak valid',
                'checkID.*.exists' => 'Data tidak ditemukan'
            ]);

            $ids = $request->input('checkID', []);
            
            if (empty($ids)) {
                return response()->json([
                    'status' => 0,
                    'message' => 'Pilih minimal 1 data untuk direset'
                ], 400);
            }

            $result = $this->service->resetMultipleHasilTest($ids);
            
            if ($result['success'] > 0) {
                return response()->json([
                    'status' => 1,
                    'message' => "{$result['success']} data berhasil direset. {$result['failed']} data gagal.",
                    'data' => $result
                ]);
            } else {
                return response()->json([
                    'status' => 0,
                    'message' => 'Gagal mereset data'
                ], 500);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 0,
                'message' => $e->errors(),
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('ResetUsmPmbController::save - Error: ' . $e->getMessage());
            return response()->json([
                'status' => 0,
                'message' => 'Terjadi kesalahan saat mereset data'
            ], 500);
        }
    }
}
