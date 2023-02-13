<?php

namespace App\Http\Controllers;

use Redirect;

use App\Models\Location;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:configurations');
    }

    public function index()
    {
        $locations = Location::get();
        return view('locations.index', compact('locations'));
    }

    public function create()
    {
        return view('locations.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'unique:locations',// same location should not allow to assign
        ]); 

        $inputValues = $request->all();
        Location::create($inputValues);
        return redirect()->route('locations.index')
                         ->with('success','Location created successfully.');
        
    }

    public function show(Location $location)
    {
        return view('locations.show', compact('location')); 
    }

    public function edit(Location $location)
    {
        return view('locations.edit', compact('location'));
    }

    public function update(Request $request, Location $location)
    {
        $request->validate([
            'name' => 'unique:locations,name,'.$location->id,
        ]); 
        
        $inputValues = $request->all();
        $location->update($inputValues);
    
        return redirect()->route('locations.index')
                        ->with('success','Location updated successfully');
    }

    public function destroy(Location $location)
    {
        $location->delete();
    
        return redirect()->route('locations.index')
                         ->with('success','Location deleted successfully');
    }
}
