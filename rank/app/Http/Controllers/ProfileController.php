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

        // Default Quota intentionally includes quota and category-like values because legacy sheets
        // use both columns for the student's default counselling preference.
        $quotas = RankRecord::whereNotNull('quota')
            ->where('quota', '!=', '')
            ->distinct()
            ->orderBy('quota')
            ->pluck('quota')
            ->merge(
                RankRecord::whereNotNull('category')
                    ->where('category', '!=', '')
                    ->distinct()
                    ->orderBy('category')
                    ->pluck('category')
            )
            ->merge([
                'All India',
                'State Quota',
                'Management Quota',
                'NRI Quota',
                'OPEN',
                'GENERAL',
                'EWS',
                'OBC',
                'SC',
                'ST',
            ])
            ->map(fn ($value) => trim((string) $value))
            ->filter()
            ->unique()
            ->sort()
            ->values();

        $states = collect([
            'Andaman and Nicobar Islands',
            'Andhra Pradesh',
            'Arunachal Pradesh',
            'Assam',
            'Bihar',
            'Chandigarh',
            'Chhattisgarh',
            'Dadra and Nagar Haveli and Daman and Diu',
            'Delhi',
            'Goa',
            'Gujarat',
            'Haryana',
            'Himachal Pradesh',
            'Jammu and Kashmir',
            'Jharkhand',
            'Karnataka',
            'Kerala',
            'Ladakh',
            'Lakshadweep',
            'Madhya Pradesh',
            'Maharashtra',
            'Manipur',
            'Meghalaya',
            'Mizoram',
            'Nagaland',
            'Odisha',
            'Puducherry',
            'Punjab',
            'Rajasthan',
            'Sikkim',
            'Tamil Nadu',
            'Telangana',
            'Tripura',
            'Uttar Pradesh',
            'Uttarakhand',
            'West Bengal',
        ]);

        return view('profile.edit', compact('user', 'quotas', 'states'));
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
            'category' => null,
            'state' => $data['state'],
        ]);

        $user->save();

        return redirect()->route('profile')
            ->with('status', 'Profile updated successfully!');
    }
}
