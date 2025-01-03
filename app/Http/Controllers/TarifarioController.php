<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TarifarioController extends Controller
{
    public function getTarifario ()
    {
        return view('tarifario.index ');
    }
}