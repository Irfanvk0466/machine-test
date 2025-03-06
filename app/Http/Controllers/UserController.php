<?php

namespace App\Http\Controllers;

use App\Http\Requests\ValidateUser;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UserController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index()
    {
        $users = User::all();
        return view('index', compact('users'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        return view('welcome');
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(ValidateUser $request)
    {
        // Generate a unique reference number
        $referenceNumber = strtoupper(Str::random(8));

        // Create the new user
        $user = User::create([
            'name'             => $request->name,
            'email'            => $request->email,
            'reference_number' => $referenceNumber,
            'referred_by'      => $request->referred_by ?? null,
        ]);

        // Handle referral points
        $this->addReferralPoints($user->referred_by);

        return redirect()->route('users.index')->with('success', 'Registration successful! Your reference number is ' . $referenceNumber);
    }

    /**
     * Adds referral points to the referrer if a valid reference number is provided.
     */
    private function addReferralPoints($referredBy)
    {
        if (!empty($referredBy)) {
            $referrer = User::where('reference_number', $referredBy)->first();
            if ($referrer) {
                $referrer->increment('points', 10);
            }
        }
    }

    /**
     * Display the specified user.
     */
    public function show(string $id)
    {
        $user = User::findOrFail($id);
        return view('users.show', compact('user'));
    }

    /**
     * Show the form for editing the user.
     */
    public function edit(string $id)
    {
        $user = User::findOrFail($id);
        return view('users.edit', compact('user'));
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
        ]);

        $user->update($request->only(['name', 'email']));

        return redirect()->route('users.index')->with('success', 'User updated successfully!');
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(string $id)
    {
        User::findOrFail($id)->delete();
        return redirect()->route('users.index')->with('success', 'User deleted successfully!');
    }
}
