<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $menus = Menu::where('parent_id', '=', null)->orderBy('order', 'asc')->paginate(10);
        return view('menus.index', compact('menus'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $parent_id = request()->parent_id;
        $parents = Menu::pluck('title', 'id');
        return view('menus.create', compact('parents', 'parent_id'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        dd($request->all());
        $request->validate([
            'title' => 'required',
            'url' => 'required',
            'role' => 'required'
        ]);

        Menu::create([
            'title' => $request->title,
            'url' => $request->url,
            'icon' => $request->icon,
            'parent_id' => $request->parent_id,
            'order' => $request->order,
            'role' => $request->role
        ]);

        return redirect()->route('menus.index')->with('success', 'Menu created successfully.');
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
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
