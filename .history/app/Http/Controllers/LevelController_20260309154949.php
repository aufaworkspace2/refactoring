<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Redirect;

class LevelController extends Controller
{
    public function __construct(Request $request)
    {
        // if (!$request->session()->has('username')) {
        //     return redirect()->route('login');
        // }

        // if (!Cookie::get('language')) {
        //     Session::put('language', 'indonesia');
        // }
    }

    public function index(Request $request, $offset = 0)
    {

        return view('views.level.v_level');
    }

    public function search(Request $request, $offset = 0)
    {
        // ================================================================
        // LOGIC 6: Extract $_POST (CI3 Line 39)
        // ================================================================
        $keyword = $request->input('keyword', '');

        // ================================================================
        // LOGIC 7: Set limit dan count total (CI3 Line 41-42)
        // ================================================================
        $limit = 10;
        $jml = $this->count_all($keyword); // Mirrored dari model method

        // ================================================================
        // LOGIC 8: Get data dengan limit & offset (CI3 Line 44)
        // ================================================================
        $data['offset'] = $offset;
        $data['query'] = $this->get_data($limit, $offset, $keyword); // Mirrored dari model method

        // ================================================================
        // LOGIC 9: Load pagination (CI3 Line 45)
        // ================================================================
        $data['link'] = $this->load_pagination($jml, $limit, $offset, 'search', 'filter');

        // ================================================================
        // LOGIC 10: Total row calculation (CI3 Line 46)
        // ================================================================
        $data['total_row'] = $this->total_row($jml, $limit, $offset);

        // ================================================================
        // LOGIC 11: Load search view (CI3 Line 47)
        // ================================================================
        // $this->load->view('search/s_level',$data);

        return view('search.s_level', $data);
    }

    /**
     * Add - Form tambah data baru
     * CI3 Line 49-53
     */
    public function add(Request $request)
    {
        // ================================================================
        // LOGIC 12: Set save flag untuk add mode (CI3 Line 51)
        // ================================================================
        $data['save'] = 1;

        // ================================================================
        // LOGIC 13: Create breadcrumb (CI3 Line 52)
        // ================================================================
        // echo create_breadcrumb();

        // ================================================================
        // LOGIC 14: Load form view (CI3 Line 53)
        // ================================================================
        // $this->load->view('forms/f_level',$data);

        return view('forms.f_level', $data);
    }

    /**
     * View - Edit form dengan data existing
     * CI3 Line 55-60
     */
    public function view(Request $request, $id)
    {
        // ================================================================
        // LOGIC 15: Get data by ID (CI3 Line 57)
        // ================================================================
        $data['row'] = $this->get_id($id); // Mirrored dari model method

        // ================================================================
        // LOGIC 16: Set save flag untuk edit mode (CI3 Line 58)
        // ================================================================
        $data['save'] = 2;

        // ================================================================
        // LOGIC 17: Create breadcrumb (CI3 Line 59)
        // ================================================================
        // echo create_breadcrumb();

        // ================================================================
        // LOGIC 18: Load form view (CI3 Line 60)
        // ================================================================
        // $this->load->view('forms/f_level',$data);

        return view('forms.f_level', $data);
    }

    /**
     * Save - Simpan data (add atau edit)
     * CI3 Line 62-81
     */
    public function save(Request $request, $save)
    {
        // ================================================================
        // LOGIC 19: Extract $_POST (CI3 Line 64)
        // ================================================================
        $Nama = $request->input('Nama', '');
        $Urut = $request->input('Urut', '');
        $ID = $request->input('ID', '');

        // ================================================================
        // LOGIC 20: Build input array (CI3 Line 66-67)
        // ================================================================
        $input['Nama'] = $Nama;
        $input['Urut'] = $Urut;

        // ================================================================
        // LOGIC 21: Check save mode == 1 (ADD) (CI3 Line 69-77)
        // ================================================================
        if ($save == 1) {
            // ========================================================
            // LOGIC 22: Check if Nama already exists (CI3 Line 71)
            // ========================================================
            $cek = DB::table('level')
                ->where('Nama', $Nama)
                ->first();

            // ========================================================
            // LOGIC 23: IF nama exists → echo gagal (CI3 Line 72-75)
            // ========================================================
            if ($cek && $cek->ID) {
                echo "gagal";
            }
            // ========================================================
            // LOGIC 24: ELSE → tambah data baru (CI3 Line 76-78)
            // ========================================================
            else {
                $this->add_level($input); // Mirrored dari model method
                echo $Nama;
            }
        }

        // ================================================================
        // LOGIC 25: Check save mode == 2 (EDIT) (CI3 Line 80-84)
        // ================================================================
        if ($save == 2) {
            // ========================================================
            // LOGIC 26: Edit data dengan ID (CI3 Line 82)
            // ========================================================
            $this->edit_level($ID, $input); // Mirrored dari model method
            echo $Nama;
        }
    }

    /**
     * Delete - Hapus data (multiple)
     * CI3 Line 86-107
     */
    public function delete(Request $request)
    {
        // ================================================================
        // LOGIC 27: Get checkID from POST (CI3 Line 88)
        // ================================================================
        $checkid = $request->input('checkID', []);

        // ================================================================
        // LOGIC 28: Loop semua checkID (CI3 Line 89-107)
        // ================================================================
        for ($x = 0; $x <= count($checkid) - 1; $x++) {
            // ========================================================
            // LOGIC 29: Hapus data per ID (CI3 Line 104)
            // ========================================================
            $this->delete_level($checkid[$x]); // Mirrored dari model method

            // ========================================================
            // LOGIC 30: Echo script untuk remove row (CI3 Line 106-109)
            // ========================================================
            echo '
            <script>
                $(".level_' . $checkid[$x] . '").remove();
            </script>
            ';
        }
    }

    /**
     * PDF - Export ke PDF
     * CI3 Line 111-116
     */
    public function pdf(Request $request)
    {
        // ================================================================
        // LOGIC 31: Get keyword dari GET (CI3 Line 113)
        // ================================================================
        $keyword = $request->input('keyword', '');

        // ================================================================
        // LOGIC 32: Load chtml2pdf library (CI3 Line 114)
        // ================================================================
        // $this->load->library("chtml2pdf");

        // ================================================================
        // LOGIC 33: Get data dengan keyword (CI3 Line 115)
        // ================================================================
        $data['query'] = $this->get_data('', '', $keyword);

        // ================================================================
        // LOGIC 34: Load PDF view (CI3 Line 116)
        // ================================================================
        // $content = $this->load->view('pdf/p_level',$data,true);
        // $this->chtml2pdf->cetak("P","A4",$content,"level");

        // CATATAN: PDF library tidak dimigrasi
        // ALASAN: chtml2pdf adalah library CI3 yang perlu diganti dengan Laravel package (seperti DomPDF atau TCPDF)
        // ACTION: Gunakan mPDF atau DomPDF untuk Laravel

        return view('pdf.p_level', $data);
    }

    /**
     * Excel - Export ke Excel
     * CI3 Line 118-131
     */
    public function excel(Request $request)
    {
        // ================================================================
        // LOGIC 35: Get keyword dari GET (CI3 Line 120)
        // ================================================================
        $keyword = $request->input('keyword', '');

        // ================================================================
        // LOGIC 36: Set header Excel (CI3 Line 122-127)
        // ================================================================
        header("Pragma: public");
        header("Expires: 0");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header("Content-Disposition: attachment; filename=data_level_" . date('d-m-Y') . ".xls");
        header("Content-Transfer-Encoding: binary ");

        // ================================================================
        // LOGIC 37: Get data dengan keyword (CI3 Line 131)
        // ================================================================
        $data['query'] = $this->get_data('', '', $keyword);

        // ================================================================
        // LOGIC 38: Load Excel view (CI3 Line 132)
        // ================================================================
        // $this->load->view('excel/ex_level',$data);

        return view('excel.ex_level', $data);
    }

    // ================================================================
    // HELPER METHODS - Mirrored dari Model (m_level)
    // ================================================================

    /**
     * get_data - Ambil data dengan filter
     * Mirrored dari m_level model
     */
    private function get_data($limit = '', $offset = '', $keyword = '')
    {
        $query = DB::table('level');

        if (!empty($keyword)) {
            $query->where('Nama', 'LIKE', '%' . $keyword . '%');
        }

        if (!empty($limit) && !empty($offset)) {
            $query->limit($limit)->offset($offset);
        } elseif (!empty($limit)) {
            $query->limit($limit);
        }

        return $query->get();
    }

    /**
     * count_all - Count total data dengan filter
     * Mirrored dari m_level model
     */
    private function count_all($keyword = '')
    {
        $query = DB::table('level');

        if (!empty($keyword)) {
            $query->where('Nama', 'LIKE', '%' . $keyword . '%');
        }

        return $query->count();
    }

    /**
     * get_id - Ambil satu data by ID
     * Mirrored dari m_level model
     */
    private function get_id($id)
    {
        return DB::table('level')->where('ID', $id)->first();
    }

    /**
     * add_level - Tambah data baru
     * Mirrored dari m_level model
     */
    private function add_level($input)
    {
        DB::table('level')->insert($input);
    }

    /**
     * edit_level - Update data
     * Mirrored dari m_level model
     */
    private function edit_level($id, $input)
    {
        DB::table('level')->where('ID', $id)->update($input);
    }

    /**
     * delete_level - Hapus data by ID
     * Mirrored dari m_level model
     */
    private function delete_level($id)
    {
        DB::table('level')->where('ID', $id)->delete();
    }

    /**
     * load_pagination - Mirrored pagination helper
     */
    private function load_pagination($total, $limit, $offset, $type, $filter)
    {
        // TODO: Implement pagination
        // Bisa pakai Laravel pagination atau load dari helper
        return '';
    }

    /**
     * total_row - Hitung total row untuk display
     */
    private function total_row($total, $limit, $offset)
    {
        $start = $offset + 1;
        $end = min($offset + $limit, $total);
        return "Menampilkan $start sampai $end dari $total data";
    }
}
?>
