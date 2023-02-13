<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\CheckInCode;
use App\Models\Customer;
use App\Models\Session;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CustomerViewController extends Controller
{
    public function loginPage()
    {
        return view('customer_view.customer_login');
    }

    public function otpRequest(Request $request)
    {
        $phoneNumber = $request->input('phoneNumber');
        $name =  $request->input('name');
        $customer = Customer::where('phoneNumber', $phoneNumber)->where('name', $name)->first();
        if($customer == null)
        {
            $customers = Customer::where('phoneNumber', $phoneNumber)->get();

            if(count($customers) >3)// don't allow to create more than 4 customers 
            {
                $request->validate(
                    ['customer_count' => 'required' ],
                    ['customer_count.required' => 'There are too many customers registered for this phone number.']
                );
            } 

            $customer = Customer::create(['name' => $name, 'phoneNumber' => $phoneNumber]);
        }
        
        $customer->otp = rand(10000, 99999);
        $customer->OtpCreatedTime = now();
        $customer->save();  
        return redirect()->route('customer.loginPage')
                         ->with(['otpSent' => true, 'name' => $name, 'phoneNumber' => $phoneNumber]);
    }

    public function login(Request $request)
    {
        $phoneNumber = $request->input('phoneNumber');
        $name =  $request->input('name');
        $otp = $request->input('otp');
        $customer = Customer::where('phoneNumber', $phoneNumber)->where('name', $name)->first();
        if($customer->otp == $otp)
        {
            $request->session()->put('logInCustomer', $customer->name);
            $request->session()->put('customer_id', $customer->id);
            return redirect()->route('customerView.appointmentCreate'); 
        }
        else
        {
            return redirect()->route('customer.loginPage')->with(['otpSent' => true, 'name' => $name, 'phoneNumber' => $phoneNumber])
                                                          ->withErrors(['otp'=>'OTP mismatch']);
        }
    }

    public function logout(Request $request)
    {
        $request->session()->flush();
        return redirect()->route('customer.loginPage');
    }

    public function appointmentCreate()
    {
        $today = date('Y-m-d');
        $nowTime = date('H:i');
        $sessions = Session::whereDate('date', '>', $today)->orWhereDate('date', '=',$today)
                                                            ->where('endTime', '>', $nowTime)->get();
        $customer_id = session('customer_id');
        $customer = Customer::where('id', $customer_id)->first();
        $tasks = Task::orderBy('name')->get()->pluck('combobox_text', 'id')->all();
        return view('customer_view.customer_appointment_create', compact('sessions', 'customer', 'tasks'));
    }

    public function appointmentStore(Request $request)
    {
        $customer_id = $request->input('customer_id');
        $session_id = $request->input('session_id');
        $tasks = $request->input('tasks');
 
        $oldAppointment = Appointment::where('session_id', $session_id)->where('customer_id', $customer_id)->first();//get first one

        if($oldAppointment != null){// check customer and session are same
            $request->validate(
                ['no_old_appointment' => 'required' ],
                ['no_old_appointment.required' => 'Customer already has appointment for the given session.']
            );
        } 
    
        $session = Session::where('id', $session_id)->first();
  
        if($this->getTimeForSelectedTasks($tasks) > $session->getAvailableRemainingTimeInMinutes())
        {
            $request->validate(
                ['enough_time' => 'required' ],
                ['enough_time.required' => 'No enough time in session for selected tasks.']
            );
        }

        $data = [
            'customer_id' => session('customer_id'), 
            'session_id' => $session->id, 
            'status' => 'Scheduled',
            'uuid' => (string)Str::uuid(), 
            'token' => $session->getNextToken(), 
            'scheduledTime' => $session->getNextAvailableTime(),
            'estimatedTime' => $session->getNextAvailableTime(),
        ];
        $appointment = Appointment::create($data);
        $appointment->tasks()->attach($tasks);
        if($appointment->amount() == 0){
            $appointment->paid = true;
            $appointment->save();
        }
        return redirect()->route('customerView.appointmentShow', $appointment->uuid);   
    }
    
    private function getTimeForSelectedTasks($tasks) 
    {
        $tasksData = Task::whereIn('id', $tasks)->get();
        $time = 0;
        foreach ($tasksData as $task) {
            $time = $time + $task->durationInMinutes;
        }
        return $time;
    }

    public function appointmentShow(String $uuid)
    {
        $appointment = Appointment::where('uuid', $uuid)->first();
        return view('customer_view.customer_appointment_show', compact('appointment'));
    }

    public function appointmentCheckIn(Request $request)
    {
        $appointment_id = $request->input('appointment_id');
        $appointment = Appointment::where('id', $appointment_id)->first();

        $checkInCode = CheckInCode::first();

        if($checkInCode->code == $request->input('check_in_code'))
        {
            $appointment->status = 'Arrived';
            $appointment->save();

            return  redirect()->route('customerView.appointmentShow', $appointment->uuid);
        }
        else
        {
            return redirect()->route('customerView.appointmentShow', $appointment->uuid)
                             ->withErrors(['check_in_code'=>'Wrong check-in code']);
        }
    }

    public function appointmentCancelRequest(Request $request)
    {
        $appointment_id = $request->id; 
        $appointment = Appointment::where('id', $request->id)->first();
        $appointment->status = 'Cancel Requested';
        $appointment->save();
        return redirect()->route('customerView.appointmentList');
    }

    public function appointmentList ()
    {
        $appointments = Appointment::where('customer_id', session('customer_id'))
                                    ->orderBy('estimatedTime', 'DESC')->get();
        return view('customer_view.customer_appointment_list', compact('appointments'));
    }
}