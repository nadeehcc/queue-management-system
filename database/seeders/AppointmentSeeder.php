<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

use App\Models\Appointment;
use App\Models\Session;
use App\Models\Task;

class AppointmentSeeder extends Seeder
{
    public function run()
    {
        $this->createAppointments(1,  1, ['Completed', 'Completed','Completed', 'Completed', 'Completed', 'Canceled', 'Completed', 'Completed']);
        $this->createAppointments(2, 10, ['Completed', 'Serving','Waiting', 'Invited', 'Arrived', 'Scheduled', 'Cancel Requested', 'Canceled']);
        $this->createAppointments(3, 20, ['Scheduled', 'Scheduled','Scheduled', 'Scheduled', 'Scheduled', 'Scheduled', 'Scheduled', 'Scheduled']);
        $this->createAppointments(4, 30, ['Completed', 'Completed','Completed', 'Completed', 'Completed', 'Canceled', 'Completed', 'Completed']);
        $this->createAppointments(5, 40, ['Scheduled', 'Scheduled','Scheduled', 'Scheduled', 'Scheduled', 'Scheduled', 'Scheduled', 'Scheduled']);
        $this->createAppointments(6, 50, ['Completed', 'Completed','Completed', 'Completed', 'Completed', 'Canceled', 'Completed', 'Completed']);
        $this->createAppointments(7, 60, ['Completed', 'Serving','Waiting', 'Invited', 'Arrived', 'Scheduled', 'Cancel Requested', 'Canceled']);
        $this->createAppointments(8, 70, ['Scheduled', 'Scheduled','Scheduled', 'Scheduled', 'Scheduled', 'Scheduled', 'Scheduled', 'Scheduled']);
    }

    private function createAppointments(int $sessionId,  $idOfTheFistCustomerInTheSession, $statuses)
    {
        $session = Session::where('id', $sessionId)->first();
        $tasks = Task::get()->all();
        $customerId = $idOfTheFistCustomerInTheSession;
        foreach($statuses as $status)
        {
            $task = $tasks[array_rand($tasks)];
            $appointmentTestData = $this->generateAppointmentData($session, $task, $status, $customerId++);
            $appointment = Appointment::create($appointmentTestData);
            $appointment->tasks()->attach($task);
        }
    }

    private function generateAppointmentData(Session $session, Task $task, String $status, int $customer_id){
        $scheduledTime = $session->getNextAvailableTime();
        $estimatedTime = date("Y-m-d h:i", strtotime($scheduledTime."+1 minutes"));
        $arrivedTime = null;
        if( $status == 'Completed' || $status == 'Serving' || $status == 'Waiting' || $status == 'Invited' || $status == 'Arrived'){
            $arrivedTime = date("Y-m-d h:i", strtotime($scheduledTime."-17 minutes"));
        }
        $servingStartedTime = null;
        if( $status == 'Completed' || 'Serving'){
            $servingStartedTime = date("Y-m-d h:i", strtotime($scheduledTime."+1 minutes"));
        }

        $servingCompletedTime = null;
        if( $status == 'Completed'){
            $servingCompletedTime = date("Y-m-d h:i", strtotime($servingStartedTime."+".$task->durationInMinutes." minutes"));
        }
        return [
            'customer_id' => $customer_id, 
            'session_id' => $session->id, 
            'status' => $status, 
            'uuid' => Str::uuid(), 
            'token' => $session->getNextToken(), 
            'scheduledTime' => $scheduledTime, 
            'estimatedTime' =>  $estimatedTime,
            'arrivedTime' => $arrivedTime, 
            'servingStartedTime' =>  $servingStartedTime,  
            'servingCompletedTime' => $servingCompletedTime,
            'paid' => true
        ];
    }
}