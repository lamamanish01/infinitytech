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

    public function store(Request $request) {
        //dd($request->all());
        $request->validate([
            'name' => 'required|unique:permissions|min:3'
        ]);

        Permission::create([
            'name' => $request->name
        ]);

        return redirect()->route('permissions.create')->with('success', 'Permission created successfully.');
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
