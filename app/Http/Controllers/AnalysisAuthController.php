<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AnalysisAuthController extends Controller
{
    public function showPhoneForm()
    {
        return view('auth.analysis-verify-mobile');
    }

    public function sendOtp(Request $request)
    {
        $data = $request->validate([
            'phone' => ['required', 'digits_between:10,12'],
        ]);

        $phone = $this->normalizePhone($data['phone']);
        $otp = (string) random_int(100000, 999999);

        $request->session()->put('analysis_login_phone', $phone);
        $request->session()->put('analysis_login_otp', Hash::make($otp));
        $request->session()->put('analysis_login_otp_expires_at', now()->addMinutes(10)->timestamp);

        $message = "Dear Student, {$otp} is your Career Educate mobile verification code. It is valid for 10 minutes. Shaheen Group";
        $sent = sendSmsToPatient($phone, $message);

        if (! $sent) {
            return back()
                ->withErrors(['phone' => 'We could not send the OTP right now. Please try again.'])
                ->onlyInput('phone');
        }

        return redirect()->route('analysis.verify')
            ->with('status', 'OTP sent to your mobile number.');
    }

    public function showVerifyForm(Request $request)
    {
        if (! $request->session()->has('analysis_login_phone')) {
            return redirect()->route('analysis.login');
        }

        return view('auth.verify-mobile', [
            'phone' => $request->session()->get('analysis_login_phone'),
            'action' => route('analysis.verify.store'),
        ]);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => ['required', 'digits:6'],
        ]);

        $hashedOtp = $request->session()->get('analysis_login_otp');
        $expiresAt = $request->session()->get('analysis_login_otp_expires_at');

        if (! $hashedOtp || ! $expiresAt || now()->timestamp > $expiresAt) {
            return redirect()->route('analysis.login')
                ->withErrors(['phone' => 'Your OTP has expired. Please request a new one.']);
        }

        if (! Hash::check($request->input('otp'), $hashedOtp)) {
            return back()->withErrors(['otp' => 'The OTP you entered is incorrect.']);
        }

        $request->session()->put('analysis_verified', true);
        $request->session()->forget(['analysis_login_phone', 'analysis_login_otp', 'analysis_login_otp_expires_at']);

        // Redirect to the first available analysis page, or home if none exist
        $dataset = \App\Models\AnalysisDataset::where('is_active', true)->first();
        if ($dataset) {
            return redirect()->route('analysis.show', $dataset->slug);
        }

        return redirect()->route('home')->with('status', 'Analysis data verified, but no data available yet.');
    }

    private function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/\D+/', '', $phone);

        return str_starts_with($phone, '91') ? $phone : '91' . ltrim($phone, '0');
    }
}
