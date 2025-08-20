<?php

namespace App\Http\Controllers;

use App\Mail\DeleteUser;
use App\Models\User;
use Inertia\Inertia;
use Illuminate\Support\Str;
use App\Mail\NewUserCreated;
use Illuminate\Http\Request;
use App\Mail\UserUpdatedDetails;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $sortField = $request->input('sort', 'created_at');
        $sortDirection = $request->input('direction', 'desc');
        $allowedSorts = ['id', 'name', 'email', 'created_at'];

        if (!in_array($sortField, $allowedSorts)) {
            $sortField = 'created_at';
        }

        $users = User::query()
            ->when($request->input('search'), function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })
            ->orderBy($sortField, $sortDirection)
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('User', [
            'users' => $users,
            'filters' => $request->only(['search', 'sort', 'direction']),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:' . User::class,
        ]);

        $temporaryPassword = Str::random(20);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($temporaryPassword),
            'email_verified_at' => null,
        ]);

        Mail::to($user)->send(new NewUserCreated($user, $temporaryPassword));

        Log::info("User with id:({$user->id}), name:({$user->name}) created");

        return redirect()->route('user.index')->with('success', 'User created successfully and welcome email sent.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
        ]);

        $user->name = $request->name;
        $user->email = $request->email;

        $user->save();

        Mail::to($user)->send(new UserUpdatedDetails($user));

        Log::info("User with id:({$user->id}), name:({$user->name}) updated");

        return redirect()->route('user.index')->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $user->delete();

        Mail::to($user)->send(new DeleteUser($user));

        Log::info("User with id:({$user->id}), name:({$user->name}) moved to trash");

        return redirect()->route('user.index')->with('success', 'User moved to trash.');
    }
}
