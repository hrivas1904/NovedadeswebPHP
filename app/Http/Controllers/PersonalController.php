<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PersonalController extends Controller
{
    public function nominaPersonal()
    {
        return view('personal.nominaPersonal');
    }
}
