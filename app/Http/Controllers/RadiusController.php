<?php

namespace App\Http\Controllers;

use App\Models\RadPostAuth;

class RadiusController extends Controller
{
    public function index()
    {
        $authLogs = RadPostAuth::paginate(15);
        return view('radius.index', compact('authLogs'));
    }
}
