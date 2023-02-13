<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use Spatie\Permission\Models\Role;
use DB;
use Hash;
use Illuminate\Support\Arr;

class UserController extends Controller
{
    function __construct()
    {
        $this->middleware('permission:configurations');
    }

    public function index(Request $request)
    {
        $users = User::get();
        return view('users.index', compact('users'));
    }
    
    public function create()
    {
        $roles = Role::pluck('name', 'name')->all();
        return view('users.create', compact('roles'));
    }
    
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|same:confirm-password',
            'roles' => 'required'
        ]);
    
        $inputValues = $request->all();
        $inputValues['password'] = Hash::make($inputValues['password']);
    
        $user = User::create($inputValues);
        $selectedRoles = $request->input('roles');
        $user->assignRole($selectedRoles);
    
        return redirect()->route('users.index')
                         ->with('success','User created successfully.');
    }
    
    public function show($id)
    {
        $user = User::find($id);
        return view('users.show',compact('user'));
    }
    
    public function edit($id)
    {
        $user = User::find($id);
        $roles = Role::pluck('name','name')->all();
        $userRole = $user->roles->pluck('name','name')->all();
    
        return view('users.edit',compact('user','roles','userRole'));
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required',
            'email' => 'required|email|unique:users,email,'.$id,
            'password' => 'same:confirm-password',
            'roles' => 'required'
        ]);
    
        $inputValues = $request->all();
        if(!empty($inputValues['password'])){ 
            $inputValues['password'] = Hash::make($inputValues['password']);
        }else{
            $inputValues = Arr::except($inputValues,array('password'));    
        }
    
        $user = User::find($id);
        $user->update($inputValues);
        DB::table('model_has_roles')->where('model_id',$id)->delete();
    
        $selectedRoles = $request->input('roles');
        $user->assignRole($request->input($selectedRoles));
    
        return redirect()->route('users.index')
                        ->with('success','User updated successfully');
    }
    
    public function destroy($id)
    {
        User::find($id)->delete();
        return redirect()->route('users.index')
                         ->with('success','User deleted successfully');
    }
}