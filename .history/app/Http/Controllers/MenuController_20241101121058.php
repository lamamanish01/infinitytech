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
        $menus = Menu::orderBy('order', 'asc')->paginate(10);
        return view('menus.index', compact('menus'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $parents = Menu::where('parent_id', null)->with('children')->get();
        return view('menus.create', compact('parents'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
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
    public function edit(Menu $menu)
    {
        // $parents = Menu::get();
        $parents = Menu::where('parent_id', null)->with('children')->get();
        return view('menus.edit', compact('menu', 'parents'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Menu $menu)
    {
        $request->validate([
            'title' => 'required',
        ]);

        $menu->title = $request->title;
        $menu->url = $request->url;
        $menu->icon = $request->icon;
        $menu->parent_id = $request->parent_id;
        $menu->order = $request->order;
        $menu->role = $request->role;
        $menu->save();

        return redirect()->route('menus.index')->with('success', 'Menu update successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Menu $menu)
    {
        $menu->delete();
        return redirect()->route('menus.index')->with('success', 'Menu delete successfully.');
    }
}
