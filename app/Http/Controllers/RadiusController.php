<?php

namespace App\Http\Controllers;

use App\Models\RadPostAuth;

class RadiusController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:view radpostauth')->only(['index', 'show']);
        $this->middleware('permission:create radpostauth')->only(['create', 'store']);
        $this->middleware('permission:edit radpostauth')->only(['edit', 'update']);
        $this->middleware('permission:delete radpostauth')->only(['destroy']);
    }

    public function index()
    {
        $authLogs = RadPostAuth::orderBy('authdate', 'desc')->paginate(15);
        return view('radius.index', compact('authLogs'));
    }
}
