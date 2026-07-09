<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $activitys = ActivityLog::latest()->paginate(15);
        $unreadCount = ActivityLog::where('is_read', false)->count();

        return view('activities.index', [
            'activitys' => $activitys,
            'unreadCount' => $unreadCount
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(ActivityLog $activityLog)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ActivityLog $activityLog)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ActivityLog $activityLog)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ActivityLog $activityLog)
    {
        //
    }

    public function read($id)
    {
        $activity = ActivityLog::findOrFail($id);

        $activity->update([
            'is_read' => true
        ]);

        if ($activity->url) {
            return redirect()->to(trim($activity->url));
        }

        return back();
    }

    public function markAllRead()
    {
        ActivityLog::where('is_read', false)->update([
            'is_read' => true
        ]);

        return back();
    }
}
