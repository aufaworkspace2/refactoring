<?php

namespace App\Http\Controllers;

use App\Services\PerkembanganAkademikService;
use App\Services\MahasiswaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Barryvdh\DomPDF\Facade\Pdf;

class PerkembanganAkademikController extends Controller
{
    protected $service;
    protected $mahasiswaService;

    public function __construct(PerkembanganAkademikService $service, MahasiswaService $mahasiswaService)
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

    public function index()
    {
        return view('perkembanganakademik.v_perkembanganakademik');
    }

    public function loadinfo($id)
    {
        $data['ID'] = $id;
        $data['dosen'] = \App\Models\Dosen::all(); 
        return view('perkembanganakademik.v_loadinfo', $data);
    }

    public function search(Request $request, $offset = 0)
    {
        $limit = 10;
        $params = $request->all();
        
        $jml = $this->mahasiswaService->count_all(
            $params['ProgramID'] ?? '',
            $params['ProdiID'] ?? '',
            $params['KelasID'] ?? '',
            $params['StatusMhswID'] ?? '',
            $params['TahunMasuk'] ?? '',
            $params['JenjangID'] ?? '',
            $params['keyword'] ?? '',
            '', '',
            $params['SemesterMasuk'] ?? ''
        );

        $query = $this->mahasiswaService->get_data(
            $limit, $offset,
            $params['ProgramID'] ?? '',
            $params['ProdiID'] ?? '',
            $params['KelasID'] ?? '',
            $params['StatusMhswID'] ?? '',
            $params['TahunMasuk'] ?? '',
            $params['JenjangID'] ?? '',
            $params['keyword'] ?? '',
            '', '', '',
            $params['SemesterMasuk'] ?? ''
        );

        $data = [
            'query' => $query,
            'offset' => $offset,
            'total_row' => total_row($jml, $limit, $offset),
            'link' => load_pagination($jml, $limit, $offset, 'searchtranskrip', 'filter')
        ];

        return view('perkembanganakademik.s_perkembanganakademik', $data);
    }

    public function cetak(Request $request, $mhswId, $jenis = "ASLI", $bahasa = 1)
    {
        $transcriptData = $this->service->getTranscriptData($mhswId);
        
        $data = [
            'MhswID' => $mhswId,
            'jenis' => $jenis,
            'all' => $transcriptData->mahasiswa,
            'TahunAktif' => $transcriptData->tahunAktif,
            'Judul' => $transcriptData->tugasAkhir->Judul ?? '',
            'Nomor' => $request->query('nomor'),
            'TanggalLulus' => $request->query('tgl'),
            'tgl_cetak' => $request->query('tgl2'),
            'Dekan' => $request->query('Dekan'),
            'NIDN' => $request->query('NIDN'),
            'KA' => $transcriptData->ka->NamaGelar ?? '',
            'NIPKA' => $transcriptData->ka->NIP ?? '',
            'WKA' => $transcriptData->wka->NamaGelar ?? '',
            'NIPWKA' => $transcriptData->wka->NIP ?? ''
        ];

        if ($jenis == "ASLI") {
            $view = ($bahasa == 1) ? 'perkembanganakademik.p_cetaktranskrip' : 'perkembanganakademik.p_cetaktranskrip_inggris';
        } else {
            $view = 'perkembanganakademik.p_cetaktranskrip_sementara';
        }

        $pdf = Pdf::loadView($view, $data)->setPaper('legal', 'portrait');
        return $pdf->stream("Transkrip_".$transcriptData->mahasiswa->NPM.".pdf");
    }

    public function pdf_kelas(Request $request)
    {
        $data = $this->service->getClassDevelopmentData($request->all());
        $pdf = Pdf::loadView('perkembanganakademik.p_perkembangan_kelas', $data);
        return $pdf->stream("Perkembangan_Kelas.pdf");
    }
}
