<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $roles = Auth::user()->roles;
        if($roles[0]->name == 'Admin'){
            return redirect()->route('checkInCode.show');
        }
        else if($roles[0]->name == 'Manager'){
            return redirect()->route('report.sessionsTodaySummary');
        }
        else if($roles[0]->name == 'Receptionist'){
            return redirect()->route('appointments.create');
        }
        else if($roles[0]->name == 'Nurse'){
            return redirect()->route('sessions.today');
        }
        return view('home');
    }
}
