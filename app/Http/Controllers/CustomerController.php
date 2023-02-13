<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:customer');
    }

    public function index()
    {
        $customers = Customer::get();
        return view('customers.index', compact('customers'));
    }

    public function create()
    {
        return view('customers.create');
    }

    public function store(Request $request)
    {  
        $name = $request->input('name');
        $phoneNumber = $request->input('phoneNumber');
   
        $oldCustomer = Customer::where('name', $name)->where('phoneNumber', $phoneNumber)->first();//get one
        
        if($oldCustomer != null)
        {
            $request->validate(
                ['no_old_customer' => 'required' ],
                ['no_old_customer.required' => 'Customer is already exist.']
            );
        } 

        $customers = Customer::where('phoneNumber', $phoneNumber)->get();

        if(count($customers) >3)
        {
            $request->validate(
                ['customer_count' => 'required' ],
                ['customer_count.required' => 'There are too many customers registered for this phone number.']
            );
        } 

        $inputValues = $request->all();
        Customer::create($inputValues);
        
        return redirect()->route('customers.index')
                        ->with('success','Customer created successfully.');
                
    }

    public function show(Customer $customer)
    {
        return view('customers.show', compact('customer'));
        
    }

    public function edit(Customer $customer)
    {
        return view('customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {  
        $name = $request->input('name');
        $phoneNumber = $request->input('phoneNumber');
   
        $oldCustomer = Customer::where('id', '!=', $customer->id)->where('name', $name)->where('phoneNumber', $phoneNumber)->first();//get one
        
        if($oldCustomer != null)
        {
            $request->validate(
                ['no_old_customer' => 'required' ],
                ['no_old_customer.required' => 'Customer is already exist.']
            );
        } 

        $customers = Customer::where('phoneNumber', $phoneNumber)->get();

        if(count($customers) >3)
        {
            $request->validate(
                ['customer_count' => 'required' ],
                ['customer_count.required' => 'There are too many customers registered for this phone number.']
            );
        } 

        $inputValues = $request->all();
        $customer->update($inputValues);
    
        return redirect()->route('customers.index')
                         ->with('success','Customer updated successfully.');
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();
    
        return redirect()->route('customers.index')
                        ->with('success','Customer deleted successfully.');
    }
}
