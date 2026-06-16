<?php

namespace App\Http\Controllers;

use App\Models\RankRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function edit()
    {
        $user = auth()->user();

        // Get distinct quotas and categories from database dynamically to show as select options
        $quotas = RankRecord::whereNotNull('quota')
            ->where('quota', '!=', '')
            ->distinct()
            ->orderBy('quota')
            ->pluck('quota');

        $categories = RankRecord::whereNotNull('category')
            ->where('category', '!=', '')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        // Extract distinct state names from rank records raw payload or model table
        $states = RankRecord::whereNotNull('dataset_id')
            ->whereNotNull('college_name') // just to verify it's a real record
            ->distinct()
            ->orderBy('quota') // fallback order
            ->pluck('quota') // fallback
            ->merge(['Karnataka', 'West Bengal', 'Andhra Pradesh', 'Maharashtra', 'Tamil Nadu', 'Delhi', 'Kerala'])
            ->unique()
            ->sort()
            ->values();

        return view('profile.edit', compact('user', 'quotas', 'categories', 'states'));
    }

    public function update(Request $request)
    {
        $user = auth()->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone' => ['required', 'digits_between:10,12', Rule::unique('users')->ignore($user->id)],
            'neet_rank' => ['nullable', 'integer', 'min:1'],
            'neet_marks' => ['nullable', 'numeric', 'min:0', 'max:720'],
            'quota' => ['nullable', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'current_password' => ['nullable', 'required_with:new_password', 'string'],
            'new_password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        if ($request->filled('new_password')) {
            if (! Hash::check($request->input('current_password'), $user->password)) {
                return back()->withErrors(['current_password' => 'The provided password does not match your current password.']);
            }
            $user->password = $request->input('new_password');
        }

        $user->fill([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'neet_rank' => $data['neet_rank'],
            'neet_marks' => $data['neet_marks'],
            'quota' => $data['quota'],
            'category' => $data['category'],
            'state' => $data['state'],
        ]);

        $user->save();

        return redirect()->route('profile')
            ->with('status', 'Profile updated successfully!');
    }
}
