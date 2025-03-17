<?php

namespace App\Http\Controllers;

use App\Models\User;

class UserController extends Controller
{
    public function index()
    {
        $users = User::orderBy('name', ''paginate();

        return view('users.index', compact('users'));
    }

    public function create()
    {
        return view('users.create');
    }
}
