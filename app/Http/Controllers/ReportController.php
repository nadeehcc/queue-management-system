<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Queue;
use App\Models\Session;
use App\Models\Location;
use App\Models\Appointment;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:reports');
    }

    public function locationUsage()
    {
        $sessions = Session::where('date', '>=', date('now'))->orderBy('location_id')->get();
        return view('reports.location_usage', compact('sessions'));
    }

    public function sessionsDaySummarySearch()
    {
        return view('reports.sessions_day_summary_search');
    }

    public function sessionsDaySummary(Request $request)
    {
        $date = $request->input('date');
        $sessions = Session::where('date', $date)->get();
        return view('reports.sessions_day_summary', compact('sessions', 'date'));
    }

    public function sessionsTodaySummary()
    {
        $date = date('Y-m-d');
        $sessions = Session::where('date', $date)->get();
        return view('reports.sessions_today_summary', compact('sessions', 'date'));
    }

    public function queueMonthSummerySearch()
    {
        $queues = Queue::orderBy('name')->pluck('name', 'id');
        return view('reports.queue_month_summery_search', compact('queues'));
    }

    public function queueMonthSummery(Request $request)
    {
        $monthAndYear = $request->input('monthAndYear');
        $queue_id = $request->input('queue_id');
        
        $queue = Queue::where('id', $queue_id)->first();

        $sessions = Session::where('queue_id', $queue_id)->
                             whereMonth('date', date("m", strtotime($monthAndYear)))->
                             whereYear('date', date("Y", strtotime($monthAndYear)))->get();
        return view('reports.queue_month_summery', compact('sessions', 'monthAndYear', 'queue'));    
    }

    public function session(String $id)
    {
        $session = Session::where('id', $id)->first();
        $appointments = Appointment::where('session_id', $session->id)->orderBy('token')->get();
        return view('reports.session', compact('session', 'appointments'));
    }
}
