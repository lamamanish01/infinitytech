<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function index() {
        return view('permissions.index');
    }

    public function create() {
        return view('permissions.create');
    }

    public function store(Request $request) {
        //dd($request->all());
        $validator = Validator::make($request->all(), [
            
        ])

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
