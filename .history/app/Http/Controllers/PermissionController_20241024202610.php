<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function index() {
        //
    }

    public function create() {
        return view('permissions.create');
    }

    public function store(Request $request, Permission $permission) {
        //dd($request->all());
        $request->validate([
            'name' => 'required'
        ]);

        $permission->create([
            'name' => 'required'
        ]);

        return redirect()->route('permissions.create')
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
