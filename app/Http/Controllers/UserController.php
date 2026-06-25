<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        $users = User::latest()->paginate(10);

        return view('users.list', compact('users'));   
    }

    public function show(String $id)
    {
        $user = User::findOrFail($id);

        return view('users.show', compact('user'));
    }
}
