<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class HomeController extends Controller
{
    public function index()
    {
        return view('home.inicio');
    }

    public function dashboard()
    {
        return view('home.dashboard');
    }

    public function ayuda()
    {
        return view('home.ayuda');
    }
}
