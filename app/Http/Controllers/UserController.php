<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class UserController extends Controller
{
    function index() {
        $users = User::all();
        return Inertia::render('Admin/Users', [
            'users' => $users
        ]);
    }
    public function updateRole(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $user->update($request->validate([
            'role' => ['required', Rule::in(UserRole::values())],
        ]));
        return $user;
    }
}
