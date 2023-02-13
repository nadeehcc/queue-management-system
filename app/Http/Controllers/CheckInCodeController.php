<?php
namespace App\Http\Controllers;

use App\Models\CheckInCode;
use Illuminate\Http\Request;

class CheckInCodeController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:check-in-code');
    }

    public function show()
    {
        $checkInCode = CheckInCode::first();
        return view('check_in_code.show', compact('checkInCode'));
    }

    public function generateNewCode(Request $request)
    {
        $checkInCode = CheckInCode::first();
        $checkInCode->code = rand(10000, 99999);
        $checkInCode->save();
        return redirect()->route('checkInCode.show');
    }
}
