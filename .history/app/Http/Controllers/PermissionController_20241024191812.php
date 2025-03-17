<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PermissionController extends Controller
{
    public function index() {
        //
    }

    public function create() {
        return view('permissions.create');
    }

    public function store(Request $request) {
        
        $request->validate([
            'name' => 'required'
        ]);
    }

    public function edit() {
        //
    }

    public function update() {
        //
    }

    public function delete() {
        //
    }
}
