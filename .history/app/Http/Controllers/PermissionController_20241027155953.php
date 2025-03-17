<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller implements hasMiddleware
{
    public static function middleware() : array
    {
        return [
            new Middleware('permission:view permissiosn', only: ['index']),
            new Middleware('permission:create permission's, only: ['create']),
            new Middleware('permission:edit permission', only: ['edit']),
            new Middleware('permission:delete permission', only: ['destroy']),
        ];
    }

    public function index()
    {
        $permissions = Permission::orderby('created_at', 'asc')->paginate(10);
        return view('permissions.index', compact('permissions'));
    }

    public function create()
    {
        return view('permissions.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:permissions|min:3'
        ]);

        Permission::create([
            'name' => $request->name
        ]);

        return redirect()->route('permissions.index')->with('success', 'Permission created successfully.');
    }

    public function show()
    {

    }

    public function edit(Permission $permission)
    {
        return view('permissions.edit', compact('permission'));
    }

    public function update(Request $request, Permission $permission)
    {
        $request->validate([
            'name' => 'required|unique:permissions,'.$permission->id.',id'
        ]);

        $permission->name = $request->name;
        $permission->save();

        return redirect()->route('permissions.index')->with('success', 'Permission updated successfully.');
    }

    public function destroy(Permission $permission)
    {
        $permission->delete();
        return redirect()->route('permissions.index')->with('success', 'Permission deleted successfully.');
    }
}
