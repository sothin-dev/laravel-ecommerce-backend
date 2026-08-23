<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
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

    public function toggleStatus(Request $request, int $id): RedirectResponse
    {
        $user = User::findOrFail($id);

        // Block deactivating the last active admin-equivalent guard is not needed here,
        // but we never want to fully lock out all access, so just toggle.
        $user->update(['is_active' => ! $user->is_active]);

        $state = $user->is_active ? 'activated' : 'deactivated';

        return redirect()
            ->route('users.index')
            ->with('success', "Customer {$user->name} has been {$state}.");
    }
}
