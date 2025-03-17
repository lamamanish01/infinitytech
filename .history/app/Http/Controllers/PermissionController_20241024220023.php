<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function index() {
        $permissions = Permission::orderby('created_at', 'asc')->paginate(10);
        return view('permissions.index', compact('permissions'));
    }

    public function create() {
        return view('permissions.create');
    }

    public function store(Request $request) {
        $request->validate([
            'name' => 'required|unique:permissions|min:3'
        ]);

        Permission::create([
            'name' => $request->name
        ]);

        return redirect()->route('permissions.index')->with('success', 'Permission created successfully.');
    }

    public function edit(Permission $permission) {
        return view('permissions.edit', compact('permission'));
    }

    public function update(Request $request, Permission $permission) {
        $request->validate([
            'name' => 'required|unique:permissions|min:3'
        ]);

        $permission->name = $request->name;
        $permission->save();

        return redirect()->route('permissions.index')->with('success', 'Permission updated successfully.');
    }

    public function destory(Permission $permission) {
        if ($permission == null) {
            return redirect()->route('permissions.index')->with('error', 'Permission not found.');
        }
        $permission->delete();

        return redirect()->route('permissions.index')->with('error', 'Permission deleted successfully.');
    }
}
