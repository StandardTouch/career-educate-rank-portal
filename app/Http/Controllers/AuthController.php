<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            return redirect()->intended($request->user()->is_admin ? route('admin.dashboard') : route('dashboard'));
        }

        return back()
            ->withErrors(['email' => 'The provided credentials do not match our records.'])
            ->onlyInput('email');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'digits_between:10,12', 'unique:users,phone'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $this->normalizePhone($data['phone']),
            'password' => $data['password'],
            'mobile_verified_at' => now(), // OTP bypassed
            'is_admin' => false,
            'plan' => 'none',
            'payment_status' => 'unpaid',
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('plans.index')
            ->with('status', 'Registration successful! Choose a package to start using the portal.');
    }

    /* Commented out OTP verification process as requested
    
    public function sendRegistrationOtp(Request $request)
    {
        $data = $request->validate([
            'phone' => ['required', 'digits_between:10,12', 'unique:users,phone'],
        ]);

        $phone = $this->normalizePhone($data['phone']);
        $otp = (string) random_int(100000, 999999);

        $request->session()->put('registration_phone', $phone);
        $request->session()->put('registration_otp', Hash::make($otp));
        $request->session()->put('registration_otp_expires_at', now()->addMinutes(10)->timestamp);

        $message = "Dear Student, {$otp} is your Career Educate mobile verification code. It is valid for 10 minutes.";
        $sent = sendSmsToPatient($phone, $message);

        if (! $sent) {
            return back()
                ->withErrors(['phone' => 'We could not send the OTP right now. Please try again.'])
                ->onlyInput('phone');
        }

        return redirect()->route('register.verify')
            ->with('status', 'OTP sent to your mobile number.');
    }

    public function showVerifyMobile(Request $request)
    {
        if (! $request->session()->has('registration_phone')) {
            return redirect()->route('register');
        }

        return view('auth.verify-mobile', [
            'phone' => $request->session()->get('registration_phone'),
        ]);
    }

    public function verifyRegistrationOtp(Request $request)
    {
        $request->validate([
            'otp' => ['required', 'digits:6'],
        ]);

        $hashedOtp = $request->session()->get('registration_otp');
        $expiresAt = $request->session()->get('registration_otp_expires_at');

        if (! $hashedOtp || ! $expiresAt || now()->timestamp > $expiresAt) {
            return redirect()->route('register')
                ->withErrors(['phone' => 'Your OTP has expired. Please request a new one.']);
        }

        if (! Hash::check($request->input('otp'), $hashedOtp)) {
            return back()->withErrors(['otp' => 'The OTP you entered is incorrect.']);
        }

        $request->session()->put('registration_mobile_verified', true);
        $request->session()->forget(['registration_otp', 'registration_otp_expires_at']);

        return redirect()->route('register.details');
    }

    public function showRegisterDetails(Request $request)
    {
        if (! $request->session()->get('registration_mobile_verified')) {
            return redirect()->route('register');
        }

        return view('auth.register-details', [
            'phone' => $request->session()->get('registration_phone'),
        ]);
    }

    public function completeRegistration(Request $request)
    {
        if (! $request->session()->get('registration_mobile_verified')) {
            return redirect()->route('register');
        }

        $phone = $request->session()->get('registration_phone');

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $phone,
            'password' => $data['password'],
            'mobile_verified_at' => now(),
            'is_admin' => false,
        ]);

        $request->session()->forget(['registration_phone', 'registration_mobile_verified']);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }
    
    */

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    private function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/\D+/', '', $phone);

        return str_starts_with($phone, '91') ? $phone : '91' . ltrim($phone, '0');
    }
}
