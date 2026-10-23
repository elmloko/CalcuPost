<?php

namespace App\Http\Controllers;

class TarifarioController extends Controller
{
    public function getTarifario ()
    {
        return view('tarifario.index');
    }
}
