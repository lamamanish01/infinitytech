<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Branch;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;


class UserController extends Controller
{
    function __construct()
    {
         $this->middleware('permission:view users|create users|edit users|delete users', ['only' => ['index','store']]);
         $this->middleware('permission:create users', ['only' => ['create','store']]);
         $this->middleware('permission:edit users', ['only' => ['edit','update']]);
         $this->middleware('permission:delete users', ['only' => ['destroy']]);
    }

    public function index()
    {
        $users = User::orderBy('name', 'asc')->paginate(5);
        return view('users.index', compact('users'));
    }

    public function create()
    {
        $branches = Branch::all();
        return view('users.create', compact('branches'));
    }

    public function store(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|unique:users,email,'.$user->id.',id',
            'branch_id' => 'required',
            'password' => 'required|confirmed|min:8',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'branch_id' => $request->branch_id,
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    public function edit(User $user)
    {
        $roles = Role::orderBy('name', 'asc')->get();
        $hasRoles = $user->roles->pluck('name');
        return view('users.edit', compact('user', 'roles', 'hasRoles'));
    }

    public function update(Request $request, User $user)
    {
        //dd($request->all());
        $request->validate([
            'name' => 'required',
            'email' => 'required|unique:users,email,'.$user->id.',id',
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->branch_id = $user->branch_id;
        $user->password = Hash::make($request->password);
        $user->save();

        $user->syncRoles($request->role);

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }
}
