<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\DashboardService;

class DashboardController extends Controller
{
    protected $service;

    public function __construct(DashboardService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        // Ambil UserID dari session (Sesuaikan dengan nama session login kamu)
        $userId = session('UserID');
        $modulGrup = session('modulgrup');

        // Panggil data dari Service
        $progressData = $this->service->getDashboardProgressData();
        $menus = $this->service->getMenus($userId);

        // Gabungkan semua data untuk dikirim ke view
        $data = [
            'modulgrup' => $modulGrup,
            'progress_percent' => $progressData['progress_percent'],
            'list_alert_progress' => $progressData['list_alert_progress'],
            'menus' => $menus, // <--- Data Menu kita kirim dari Controller
            'show_setup_crp' => 0 // Asumsi sementara
        ];

        return view('dashboard1', $data);
    }

    public function reset()
    {
        // Kembalikan session modulgrup ke 0 (dashboard utama)
        session(['modulgrup' => 0]);
        return redirect()->route('dashboard');
    }

    public function set_modul($id)
    {
        // Ganti session modulgrup sesuai yang diklik user
        session(['modulgrup' => $id]);
        return redirect()->route('dashboard');
    }
}