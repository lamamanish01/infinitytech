<?php

namespace App\Http\Controllers;

use App\Models\CronJob;
use Illuminate\Http\Request;

class CronJobController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    function __construct()
    {
        $this->middleware('permission:view cron jobs')->only(['index']);
        $this->middleware('permission:create cron jobs')->only(['store']);
        $this->middleware('permission:edit cron jobs')->only(['toggle', 'updateFrequency']);
        $this->middleware('permission:delete cron jobs')->only(['destroy']);
    }

    public function index()
    {
        $cronJobs = CronJob::all();
        return view('cron.jobs', compact('cronJobs'));
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
        $request->validate([
            'key' => 'required|unique:cron_jobs',
            'name' => 'required',
            'frequency' => 'required',
        ]);

        CronJob::create([
            'key' => $request->key,
            'name' => $request->name,
            'frequency' => $request->frequency,
            'is_active' => 1,
        ]);

        return back()->with('success', 'Cron job created');
    }

    /**
     * Display the specified resource.
     */
    public function show(CronJob $cronJob)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CronJob $cronJob)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CronJob $cronJob)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        CronJob::findOrFail($id)->delete();

        return back()->with('success', 'Cron job deleted');
    }

    public function toggle($id)
    {
        $job = CronJob::findOrFail($id);
        $job->is_active = !$job->is_active;
        $job->save();

        return back();
    }

    public function updateFrequency(Request $request, $id)
    {
        $job = CronJob::findOrFail($id);

        $job->frequency = $request->frequency;
        $job->save();

        return back();
    }
}
