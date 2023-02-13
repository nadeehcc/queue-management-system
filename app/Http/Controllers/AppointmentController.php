<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Customer;
use App\Models\Session;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AppointmentController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:appointment-create', ['only' => ['create','store']]);
        $this->middleware('permission:appointment-list', ['only' => ['index','show','viewTodayAppointment',
                                                                     'viewOldAppointment','viewUpcomingAppointment']]);
        $this->middleware('permission:appointment-payment', ['only' => ['changePayment']]);
    }

    public function index()
    {
        $appointments = Appointment::get();
        return view('appointments.index', compact('appointments'));
    }

    public function viewOldAppointment(Request $request)
    {
        $today = date('Y-m-d');
        $appointments = Appointment::whereDate('scheduledTime', '<', $today)->get();//schedule time has date and time
        return view('appointments.viewOldAppointment', compact('appointments'));
    }

    public function viewTodayAppointment(Request $request)
    {
        $today = date('Y-m-d');
        $appointments = Appointment::whereDate('scheduledTime', '=', $today)->get();
        return view('appointments.viewTodayAppointment', compact('appointments'));
    }

    public function viewUpcomingAppointment(Request $request)
    {
        $today = date('Y-m-d');
        $appointments = Appointment::whereDate('scheduledTime', '>', $today)->get();
        return view('appointments.viewUpComingAppointment', compact('appointments'));
    }

    public function create()
    {
        $today = date('Y-m-d');
        $nowTime = date('H:i');
        $sessions = Session::whereDate('date', '>', $today)->orWhereDate('date', '=', $today)
                                                                  ->where('endTime', '>', $nowTime)->get();
        $customers = Customer::get();
        $tasks = Task::orderBy('name')->get()->pluck('combobox_text', 'id')->all();
        return view('appointments.create', compact('sessions', 'customers', 'tasks'));
    }

    public function store(Request $request)
    {
        $select_or_enter_customer = $request->input('select_or_enter_customer');// which radio button is clicked 

        if($select_or_enter_customer == 'select_customer')
        {
            $customer_id = $request->input('customer_id');
        }
        else
        {
            $phoneNumber = $request->input('phoneNumber');
            $name = $request->input('name');

            $customer = Customer::where('phoneNumber', $phoneNumber)->where('name', $name)->first();

            if($customer == null)
            {
                $customers = Customer::where('phoneNumber', $phoneNumber)->get();

                if(count($customers) >3)
                {
                    $request->validate(
                        ['customer_count' => 'required' ],
                        ['customer_count.required' => 'There are too many customers registered for this phone number.']
                    );
                } 

                $customer = Customer::create(['phoneNumber' => $phoneNumber, 'name' => $name]);
                
            }

            $customer_id = $customer->id;
        }
        $session_id = $request->input('session_id');
   
        $oldAppointment = Appointment::where('session_id', $session_id)->where('customer_id', $customer_id)->first();//get first one

        if($oldAppointment != null){// check customer and session are same
            $request->validate(
                ['no_old_appointment' => 'required' ],
                ['no_old_appointment.required' => 'Customer already has appointment for the given session.']
            );
        } 

        $session = Session::where('id', $session_id)->first();

        $tasks = $request->input('tasks');

        if($this->getTimeForSelectedTasks($tasks) > $session->getAvailableRemainingTimeInMinutes())
        {
            $request->validate(
                ['enough_time' => 'required' ],
                ['enough_time.required' => 'No enough time in session for selected tasks.']
            );
        }

        $data = [
            'customer_id' => $customer_id, 
            'session_id' => $session_id, 
            'status' => 'Scheduled',
            'uuid' => (string)Str::uuid(), 
            'token' => $session->getNextToken(), 
            'scheduledTime' => $session->getNextAvailableTime(),
            'estimatedTime' => $session->getNextAvailableTime(),
            'paid' => $request->input('paid') != null,
        ];
        
        $appointment = Appointment::create($data);
        $appointment->tasks()->attach($tasks);
        if($appointment->amount() == 0){
            $appointment->paid = true;
            $appointment->save();
        }
        return redirect()->route('appointments.show',  $appointment->id);
    }

    private function getTimeForSelectedTasks($tasks) 
    {
        $tasksData = Task::whereIn('id', $tasks)->get();
        $time = 0;
        foreach ($tasksData as $task) 
        {
            $time = $time + $task->durationInMinutes;
        }
        return $time;
    }

    public function show(Appointment $appointment)
    {
        return view('appointments.show', compact('appointment'));
    }

    public function changeStatus(Request $request)
    {
        $appointmentId = $request->input('appointmentId');// appointment id from ui
        $appointment = Appointment::where('id',$appointmentId)->first(); //from database
        $actions = ['Completed' => ['next' => null, 'back' => 'Serving'],
                    'Serving' => ['next' => 'Completed', 'back' => 'Waiting'],
                    'Waiting' => ['next' => 'Serving', 'back' => 'Invited'],
                    'Invited' => ['next' => 'Waiting', 'back' => 'Arrived'],
                    'Arrived' => ['next' => 'Invited', 'back' => 'Scheduled'],
                    'Scheduled' => ['next' => 'Arrived', 'back' => null],
                    'Cancel Requested' => ['next' => 'Canceled', 'back' => 'Scheduled'],
                    'Canceled' => ['next' => null, 'back' => 'Scheduled']];

        $action =  $request->input('action');
        switch ($action) {
            case 'next': // change status to forward 
                if($actions[$appointment->status]['next'] == 'Serving')
                {
                    $oldAppointment = Appointment::where('session_id', $appointment->session->id)->where('status', 'Serving')->first();
                    if($oldAppointment != null)
                    {
                        return redirect()->route('sessions.show', $appointment->session_id)
                                         ->withErrors(['Action'=>'Another appointment is serving.']);
                    }
                }
                $appointment->status = $actions[$appointment->status]['next'];
                break;
                
            case 'back': // change status to backward
                $appointment->status = $actions[$appointment->status]['back'];
                break;

            case 'cancel':
                $appointment->status = 'Canceled';
                break;

            default :
                $appointment->status = 'Canceled';
                break;
        }
        if($appointment->status == 'Arrived'){
            $appointment->arrivedTime = now();
        }
        if($appointment->status == 'Serving'){
            $appointment->servingStartedTime = now();
        }
        if($appointment->status == 'Completed'){
            $appointment->servingCompletedTime = now();
        }
        $appointment->save();
        
        if($appointment->status == 'Completed' or $appointment->status == 'Canceled' or $appointment->status == 'Cancel Requested')
        {
            $this->recalculate( $appointment->session);
        }
        return redirect()->back();
    }

    private function recalculate(Session $session)
    {
        $appointments = Appointment::where('session_id', $session->id)->
                                     whereNotIn('status', ['Serving','Completed', 'Cancel Requested', 'Canceled'])->
                                     orderBy('token')->get();
        
        $startTime = null;
        if($session->started())
        {
            $startTime = date("Y-m-d h:i"); //current time
        }
        else
        {
            $startTime = date("Y-m-d H:i:s", strtotime($session->date.' '.$session->startTime)); //session start time
        }

        foreach($appointments as $appointment)
        {
            $appointment->estimatedTime = $startTime;
            $appointment->save();
            $startTime = $appointment->estimatedCompleteTime(); 
        }
    }

    public function changePayment(Request $request)
    {
        $appointmentId = $request->input('appointmentId');
        $appointment = Appointment::where('id',$appointmentId)->first();
        $appointment->paid = !($appointment->paid);
        $appointment->save();
        return redirect()->route('appointments.show',  $appointment->id);
    }
}
