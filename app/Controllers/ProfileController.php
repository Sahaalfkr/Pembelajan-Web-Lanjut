<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class ProfileController extends BaseController
{
    /**
     * Menampilkan halaman profil pengguna yang login
     */
    public function index()
    {
        // Data dari session
        $data = [
            'username' => session()->get('username'),
            'email' => session()->get('email'),
            'role' => session()->get('role'),
            'login_time' => session()->get('login_time'),
            'is_logged_in' => session()->get('isLoggedIn')
        ];

        return view('v_profil', $data);
    }
}
