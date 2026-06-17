<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'phone' => ['required', 'digits_between:10,12'],
        ]);

        $phone = $this->normalizePhone($data['phone']);
        $user = User::where('phone', $phone)->first();

        if (! $user) {
            return back()
                ->withErrors(['phone' => 'No account found for this mobile number.'])
                ->onlyInput('phone');
        }

        if (! $user->mobile_verified_at) {
            return back()
                ->withErrors(['phone' => 'Please register and verify this mobile number first.'])
                ->onlyInput('phone');
        }

        $otp = (string) random_int(100000, 999999);
        $request->session()->put('login_phone', $phone);
        $request->session()->put('login_otp', Hash::make($otp));
        $request->session()->put('login_otp_expires_at', now()->addMinutes(10)->timestamp);

        $message = "Dear Student, {$otp} is your Career Educate login OTP. It is valid for 10 minutes.";
        $sent = sendSmsToPatient($phone, $message);

        if (! $sent) {
            return back()
                ->withErrors(['phone' => 'We could not send the OTP right now. Please try again.'])
                ->onlyInput('phone');
        }

        return redirect()->route('login.verify')
            ->with('status', 'OTP sent to your mobile number.');
    }

    public function showRegister()
    {
        return view('auth.register-phone');
    }

    public function showLoginOtp(Request $request)
    {
        if (! $request->session()->has('login_phone')) {
            return redirect()->route('login');
        }

        return view('auth.verify-mobile', [
            'phone' => $request->session()->get('login_phone'),
            'action' => route('login.verify.store'),
        ]);
    }

    public function verifyLoginOtp(Request $request)
    {
        $request->validate([
            'otp' => ['required', 'digits:6'],
        ]);

        $hashedOtp = $request->session()->get('login_otp');
        $expiresAt = $request->session()->get('login_otp_expires_at');
        $phone = $request->session()->get('login_phone');

        if (! $hashedOtp || ! $expiresAt || ! $phone || now()->timestamp > $expiresAt) {
            return redirect()->route('login')
                ->withErrors(['phone' => 'Your OTP has expired. Please request a new one.']);
        }

        if (! Hash::check($request->input('otp'), $hashedOtp)) {
            return back()->withErrors(['otp' => 'The OTP you entered is incorrect.']);
        }

        $user = User::where('phone', $phone)->firstOrFail();
        $request->session()->forget(['login_phone', 'login_otp', 'login_otp_expires_at']);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended($this->postAuthRedirect($user));
    }

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
            'action' => route('register.verify.store'),
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
            'neet_rank' => ['nullable', 'integer', 'min:1'],
            'neet_marks' => ['nullable', 'numeric', 'min:0', 'max:720'],
            'quota' => ['nullable', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $phone,
            'password' => Str::random(32),
            'mobile_verified_at' => now(),
            'is_admin' => false,
            'neet_rank' => $data['neet_rank'] ?? null,
            'neet_marks' => $data['neet_marks'] ?? null,
            'quota' => $data['quota'] ?? null,
            'category' => $data['category'] ?? null,
            'state' => $data['state'] ?? null,
            'plan' => 'none',
            'payment_status' => 'unpaid',
        ]);

        $request->session()->forget(['registration_phone', 'registration_mobile_verified']);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('plans.index')
            ->with('status', 'Profile created successfully. Choose a subscription to access the portal.');
    }

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

    private function postAuthRedirect(User $user): string
    {
        if ($user->is_admin) {
            return route('admin.dashboard');
        }

        if ($user->payment_status !== 'paid') {
            return route('plans.index');
        }

        return route('dashboard');
    }
}
