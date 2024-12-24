<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PammController extends Controller
{
    public function manager()
    {
        return view("pamm.manager");
    }

    /**
     * Show the form for creating a new resource.
     */
    public function investor()
    {
        return view("pamm.investor");

    }
}
