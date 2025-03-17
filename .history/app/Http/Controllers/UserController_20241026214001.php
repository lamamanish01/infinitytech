<?php

namespace App\Http\Controllers;

use App\Models\User;

class UserController extends Controller
{
    public function index()
    {
        $users = User::orderBy('name', 'asc')->paginate(5);
        return view('users.index', compact('users'));
    }

    public function create()
    {
        return view('users.create');
    }

    public function store(Request $request, User $user)
    {
        $request->validate([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
            'confirm_password' => $request->confirm_password
        ])
    }
}
