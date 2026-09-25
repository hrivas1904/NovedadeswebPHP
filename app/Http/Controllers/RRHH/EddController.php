<?php

namespace App\Http\Controllers\RRHH;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

class EddController extends Controller
{
    public function index(): View
    {
        return view('edd.index');
    }
}
