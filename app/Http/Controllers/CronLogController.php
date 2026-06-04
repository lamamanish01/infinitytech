<?php

namespace App\Http\Controllers;

use App\Models\CronLog;
use Illuminate\Http\Request;

class CronLogController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    function __construct()
    {
        $this->middleware('permission:view cron logs')->only(['index']);
        $this->middleware('permission:delete cron logs')->only(['destroy', 'clearAll']);
    }

    public function index()
    {
        $cronLogs = CronLog::latest()->paginate(10);
        return view('cron.index', compact('cronLogs'));
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
    public function show(CronLog $cronLog)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CronLog $cronLog)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CronLog $cronLog)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CronLog $cronLog)
    {
        //
    }

    public function clearAll()
    {
        CronLog::truncate();

        return back()->with('success', 'All cron logs deleted successfully');
    }
}
