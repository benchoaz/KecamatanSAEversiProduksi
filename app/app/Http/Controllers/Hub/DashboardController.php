<?php

namespace App\Http\Controllers\Hub;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Tampilkan halaman login khusus Hub Kabupaten.
     */
    public function loginForm()
    {
        return view('hub.auth.login');
    }

    /**
     * Tampilkan Dashboard utama Hub.
     */
    public function index()
    {
        return view('hub.dashboard'); // Nanti kita buat file-nya
    }
}
