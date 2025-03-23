<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    function __construct()
    {
         $this->middleware('permission:view roles|create roles|edit roles|delete roles', ['only' => ['index','store']]);
         $this->middleware('permission:create roles', ['only' => ['create','store']]);
         $this->middleware('permission:edit roles', ['only' => ['edit','update']]);
         $this->middleware('permission:delete roles', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $roles = Role::orderBy('name', 'asc')->paginate(10);
        return view('roles.index', compact('roles'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $permissions = Permission::orderby('name', 'asc')->get();
        return view('roles.create', compact('permissions'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([$request->all()]);

        $role = Role::create([
            'name' =>$request->name
        ]);

        if (!empty($request->permission)) {
            foreach ($request->permission as $name) {
                $role->givePermissionTo($name);
            }
        }
        return redirect()->route('roles.index')->with('success', 'Role Created Successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Role $role, Permission $permission)
    {
        $hasPermissions = $role->permissions->pluck('name');
        $permissions = Permission::orderBy('name', 'asc')->get();

        return view('roles.edit', compact('permissions','hasPermissions', 'role'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name' => 'required|unique:roles,name,'.$role->id.',id'
        ]);

        $role->name = $request->name;
        $role->save();

        if (!empty($request->permission)) {
            $role->syncPermissions($request->permission);
        } else {
            $role->syncPermissions([]);
        }

        return redirect()->route('roles.index')->with('success', 'Role updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role)
    {
        if($role == null) {
            return redirect()->route('roles.index')->with('error', 'Role not found.');
        }

        $role->delete();
        return redirect()->route('roles.index')->with('success', 'Role deleted successfully.');
    }
}
