<?php

namespace App\Http\Controllers;

use App\Services\RencanaStudiService;
use App\Services\MahasiswaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

class RencanaStudiController extends Controller
{
    protected $service;
    protected $mahasiswaService;

    public function __construct(RencanaStudiService $service, MahasiswaService $mahasiswaService)
    {
        $this->service = $service;
        $this->mahasiswaService = $mahasiswaService;

        $this->middleware(function ($request, $next) {
            if (!Session::get('username')) {
                return redirect('/');
            }
            return $next($request);
        });
    }

    /**
     * Display transcript index page
     */
    public function loadtranskrip()
    {
        return view('rencanastudi.v_cetaktranskrip');
    }

    /**
     * AJAX search for transcript
     */
    public function searchtranskrip(Request $request, $offset = 0)
    {
        $limit = 10;
        $params = $request->all();
        
        $data = $this->service->searchTranscript($params, $limit, $offset);
        
        return view('rencanastudi.s_cetaktranskrip', [
            'query' => $data['query'],
            'offset' => $offset,
            'total_row' => total_row($data['total'], $limit, $offset),
            'link' => load_pagination($data['total'], $limit, $offset, 'searchtranskrip', 'filter')
        ]);
    }

    /**
     * Load modal info
     */
    public function loadinfo($id)
    {
        return view('rencanastudi.v_loadinfo', ['ID' => $id]);
    }

    /**
     * Save transcript number/info
     */
    public function no_transkrip(Request $request)
    {
        $id = $request->input('ID');
        $data = [
            'NoTranskrip' => $request->input('nomor'),
            'NoSK' => $request->input('nomor_sk'),
            'TanggalLulus' => $request->input('tgl_lulus'),
        ];

        DB::table('mahasiswa')->where('ID', $id)->update($data);
        
        return response()->json(['status' => 'success']);
    }

    /**
     * Print transcript (PDF)
     */
    public function cetak(Request $request, $mhswId, $jenis = "ASLI", $bahasa = 1)
    {
        $isAsli = ($jenis == "ASLI");
        $transcriptData = $this->service->getTranscriptData($mhswId, $isAsli);

        if (!$transcriptData) return abort(404);

        $data = $transcriptData;
        $data['Nomor'] = $request->query('nomor');
        $data['TanggalLulus'] = tgl($request->query('tgl'), '02');
        $data['tgl_cetak'] = $request->query('tgl2') ?? date('Y-m-d');
        $data['Dekan'] = $request->query('Dekan');
        $data['NIDN'] = $request->query('NIDN');
        $data['jenis'] = $jenis;

        $view = $isAsli ? 'rencanastudi.p_cetaktranskrip_edit' : 'rencanastudi.p_cetaktranskrip_sementara';
        
        $pdf = Pdf::loadView($view, $data)->setPaper('legal', 'portrait');
        return $pdf->stream("Transkrip_".$data['mhs']->NPM.".pdf");
    }

    /**
     * Perkembangan Akademik (PDF)
     */
    public function perkembangan($mhswId)
    {
        $data = $this->service->getPerkembanganData($mhswId);
        
        if (!$data) return abort(404);

        $setup = get_setup_app("setup_cetak_perkembangan_akademik");
        $custom = json_decode($setup->metadata ?? '{}', true);

        $view = 'rencanastudi.p_cetakperkembanganakademik';
        $ukuran = $custom['size'] ?? 'A4';
        $orientation = $custom['orientation'] ?? 'P';

        $pdf = Pdf::loadView($view, $data)->setPaper($ukuran, $orientation == 'P' ? 'portrait' : 'landscape');
        return $pdf->stream("Perkembangan_".$data['NPM'].".pdf");
    }
}
