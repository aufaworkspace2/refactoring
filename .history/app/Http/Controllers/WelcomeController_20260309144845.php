<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class WelcomeController extends Controller
{
    public function __construct()
    {
    }

    public function index()
    {
        return view('welcome');
    }

    public function search_alur(Request $request)
    {
        $searchData = $request->get('search');

        $data_alur_sistem = file_get_contents(env('API_URL')."/getDataAlurSistem/?search=".$searchData);

        return response($data_alur_sistem);
    }
}
