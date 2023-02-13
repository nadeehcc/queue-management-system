<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Branch;
use App\Models\Location;
use App\Models\Queue;
use App\Models\Session;

use Illuminate\Http\Request;

class SessionController extends Controller
{
    function __construct()
    {
         $this->middleware('permission:session-list', ['only' => ['index','show', 'viewOldSession', 
                                                                  'viewTodaySession','viewUpcomingSession']]);
         $this->middleware('permission:session-create', ['only' => ['create','store']]);
         $this->middleware('permission:session-edit', ['only' => ['edit','update']]);
         $this->middleware('permission:session-delete', ['only' => ['destroy']]);
         $this->middleware('permission:recalculate', ['only' => ['recalculate']]);
    }

    public function index()
    {
        $sessions = Session::get();
        return view('sessions.index', compact('sessions'));
    }

    public function viewOldSession(Request $request)
    {
        $today = date('Y-m-d');
        $sessions = Session::whereDate('date', '<', $today)->get();
        return view('sessions.viewOldSession', compact('sessions'));
    }

    public function viewTodaySession(Request $request)
    {
        $today = date('Y-m-d');
        $sessions = Session::whereDate('date', '=', $today)->get();
        return view('sessions.viewTodaySession', compact('sessions'));
    }

    public function viewUpcomingSession(Request $request)
    {
        $today = date('Y-m-d');
        $sessions = Session::whereDate('date', '>', $today)->get();
        return view('sessions.viewUpComingSession', compact('sessions'));
    }

    public function create()
    {
        $queues = Queue::orderBy('name')->pluck('name', 'id')->prepend('None', '');
        $locations = Location::orderBy('name')->pluck('name', 'id')->prepend('None', '');
        return view('sessions.create', compact('queues', 'locations'));
    }

    public function store(Request $request)
    {
        $queue_id = $request->input('queue_id');
        $date = $request->input('date');
        $startTime = $request->input('startTime');
        $endTime = $request->input('endTime');
        $location_id = $request->input('location_id');
   
        $this->validate($request, [
            'date'  => 'date_format:Y-m-d|after:yesterday',
            'startTime' => 'date_format:H:i',
            'endTime' => 'date_format:H:i|after:startTime',
        ]);

        // check doctor has a session for same date and start time of new session between already existing one.
        $oldSession1 = Session::where('queue_id', $queue_id)->where('date', $date)->
                                whereTime('startTime', '<=', $startTime)->whereTime('endTime', '>=', $startTime)->first();
       
        // check doctor has a session for same date and end time of new session between already existing one.
        $oldSession2 = Session::where('queue_id', $queue_id)->where('date', $date)->
                                whereTime('startTime', '<=', $endTime)->whereTime('endTime', '>=', $endTime)->first();
      
        // check doctor has a session for same date and already existing session is between new start and new end.
        $oldSession3 = Session::where('queue_id', $queue_id)->where('date', $date)->
                                whereTime('startTime', '>=', $startTime)->whereTime('endTime', '<=', $endTime)->first();
        
        if($oldSession1 != null or $oldSession2 != null or $oldSession3 != null){
            $request->validate(
                ['no_old_session' => 'required' ],
                ['no_old_session.required' => 'There is a conflicting session for the Doctor.']
            );
        } 
      
        // check location is used by another session for same date and start time of new session between already existing one.
        $oldSession4 = Session::where('location_id', $location_id)->whereDate('date', $date)->
                                whereTime('startTime', '<=', $startTime)->whereTime('endTime', '>=', $startTime)->first();
        
        // check location is used by another session for same date and end time of new session between already existing one.
        $oldSession5 = Session::where('location_id', $location_id)->whereDate('date', $date)->
                               whereTime('startTime', '<=', $endTime)->whereTime('endTime', '>=', $endTime)->first();
       
        // check location is used by another session for same date and already existing session is between new start and new end.
        $oldSession6 = Session::where('location_id', $location_id)->whereDate('date', $date)->
                                whereTime('startTime', '>=', $startTime)->whereTime('endTime', '<=', $endTime)->first();
       
        if($oldSession4 != null or $oldSession5 != null or $oldSession6 != null){
            $request->validate(
                ['no_old_session' => 'required' ],
                ['no_old_session.required' => 'There is a conflicting session for the Location.']
            );
        }
        
        $inputValues = $request->all();
        $session = Session::create($inputValues);
        $today = date('Y-m-d');
        if($date == $today){
            return redirect()->route('sessions.today')
                         ->with('success','Session created successfully.');
        }
        else{
            return redirect()->route('sessions.up.coming')
                         ->with('success','Session created successfully.'); 
        }
    }

    public function show(Session $session)
    {                              
        $appointments = Appointment::where('session_id', $session->id)->orderBy('token')->get();// get all sessions according to the order by token
        $order = array('Serving', 'Waiting', 'Invited', 'Arrived', 'Scheduled', 'Cancel Requested','Completed', 'Canceled');//according to this order
        $appointments = $appointments->sort(function ($a, $b) use ($order) {//sorting according to the above order
            $pos_a = array_search($a->status, $order);
            $pos_b = array_search($b->status, $order);
            return $pos_a - $pos_b;
        });
        return view('sessions.show', compact('session', 'appointments'));
    }

    public function edit(Session $session)
    {
        $locations = Location::orderBy('name')->pluck('name', 'id')->prepend('None', 0);
        return view('sessions.edit', compact('session', 'locations'));
    }

    public function update(Request $request, Session $session)
    { 
        $date = $request->input('date');
        $startTime = $request->input('startTime');
        $endTime = $request->input('endTime');
        $queue_id = $request->input('queue_id');
        $location_id = $request->input('location_id');

        $this->validate($request, [
            'startTime' => 'date_format:H:i',
            'endTime' => 'date_format:H:i|after:startTime',
        ]);

        // check doctor has a session for same date and start time of new session between already existing one.
        $oldSession1 = Session::where('id', '!=', $session->id)->where('queue_id', $queue_id)->where('date', $date)->
                                whereTime('startTime', '<=', $startTime)->whereTime('endTime', '>=', $startTime)->first();
       
        // check doctor has a session for same date and end time of new session between already existing one.
        $oldSession2 = Session::where('id', '!=', $session->id)->where('queue_id', $queue_id)->where('date', $date)->
                                whereTime('startTime', '<=', $endTime)->whereTime('endTime', '>=', $endTime)->first();
      
        // check doctor has a session for same date and already existing session is between new start and new end.
        $oldSession3 = Session::where('id', '!=', $session->id)->where('queue_id', $queue_id)->where('date', $date)->
                                whereTime('startTime', '>=', $startTime)->whereTime('endTime', '<=', $endTime)->first();
        
        if($oldSession1 != null or $oldSession2 != null or $oldSession3 != null){
            $request->validate(
                ['no_old_session' => 'required' ],
                ['no_old_session.required' => 'There is a conflicting session for the Doctor.']
            );
        } 
      
        // check location is used by another session for same date and start time of new session between already existing one.
        $oldSession4 = Session::where('id', '!=', $session->id)->where('location_id', $location_id)->whereDate('date', $date)->
                                whereTime('startTime', '<=', $startTime)->whereTime('endTime', '>=', $startTime)->first();
        
        // check location is used by another session for same date and end time of new session between already existing one.
        $oldSession5 = Session::where('id', '!=', $session->id)->where('location_id', $location_id)->whereDate('date', $date)->
                               whereTime('startTime', '<=', $endTime)->whereTime('endTime', '>=', $endTime)->first();
       
        // check location is used by another session for same date and already existing session is between new start and new end.
        $oldSession6 = Session::where('id', '!=', $session->id)->where('location_id', $location_id)->whereDate('date', $date)->
                                whereTime('startTime', '>=', $startTime)->whereTime('endTime', '<=', $endTime)->first();
       
        if($oldSession4 != null or $oldSession5 != null or $oldSession6 != null){
            $request->validate(
                ['no_old_session' => 'required' ],
                ['no_old_session.required' => 'There is a conflicting session for the Location']
            );
        }

        $inputValues = $request->all();

        $session->update($inputValues);

        $today = date('Y-m-d');

        if($date ==  $today){
            return redirect()->route('sessions.today')
                         ->with('success','Session updated successfully.');
        }
        else{
            return redirect()->route('sessions.up.coming')
                         ->with('success','Session updated successfully.'); 
        }
    }

    public function destroy(Session $session)
    {
        $date = $session->date;

        $session->delete();

        $today = date('Y-m-d');
    
        if($date == $today){
            return redirect()->route('sessions.today')
                         ->with('success','Session deleted successfully.');
        }
        else{
            return redirect()->route('sessions.up.coming')
                         ->with('success','Session deleted successfully.'); 
        };
    }

    public function recalculate(Request $request)
    {
        $delayInMinutes = $request->input('delayInMinutes');
        if(!$delayInMinutes)
        {
            $delayInMinutes = 0;
        }
        
        $session_id = $request->input('session_id');
        $session = Session::where('id', $session_id)->first();
        
        $startTime = null;
        if($session->started()){
            $startTime = date("Y-m-d h:i", strtotime("+".$delayInMinutes." minutes"));
        }
        else{
            $startTime = date("Y-m-d h:i", strtotime($session->date.' '.$session->startTime ."+".$delayInMinutes ." minutes"));
        }
        
        $appointments = Appointment::where('session_id', $session->id)->
                                     whereNotIn('status', ['Serving','Completed', 'Cancel Requested', 'Canceled'])->
                                     orderBy('token')->get();

        foreach($appointments as $appointment)
        {
            $appointment->estimatedTime = $startTime;
            $appointment->save();
            $startTime = $appointment->estimatedCompleteTime(); 
        }
        return redirect()->route('sessions.show', $session_id);
    }
}