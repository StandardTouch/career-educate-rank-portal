<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    public function index()
    {
        if (auth()->user()->payment_status === 'paid') {
            return redirect()->route('dashboard');
        }

        return view('plans.index');
    }

    public function checkout(string $plan)
    {
        if (auth()->user()->payment_status === 'paid') {
            return redirect()->route('dashboard');
        }

        abort_unless(in_array($plan, ['basic', 'premium'], true), 404);

        $planName = $plan === 'basic' 
            ? 'Counselling Information & Web Access Support' 
            : 'Complete Counselling Guidance (Phone & Physical Support)';
        
        $price = $plan === 'basic' ? 2000 : 5000;

        return view('plans.checkout', compact('plan', 'planName', 'price'));
    }

    public function pay(Request $request)
    {
        if (auth()->user()->payment_status === 'paid') {
            return redirect()->route('dashboard');
        }

        $request->validate([
            'plan' => ['required', 'string', 'in:basic,premium'],
            'amount' => ['required', 'numeric'],
            'card_number' => ['required', 'string'],
            'card_name' => ['required', 'string'],
        ]);

        $user = auth()->user();
        $plan = $request->input('plan');
        $amount = (float) $request->input('amount');

        // Create transaction record
        Payment::create([
            'user_id' => $user->id,
            'plan' => $plan,
            'amount' => $amount,
            'transaction_id' => 'TXN-' . strtoupper(Str::random(10)),
            'status' => 'completed',
        ]);

        // Update user
        $user->update([
            'plan' => $plan,
            'payment_status' => 'paid',
        ]);

        return redirect()->route('payment.success', ['plan' => $plan]);
    }

    public function success(Request $request)
    {
        $plan = $request->input('plan', 'basic');
        return view('plans.success', compact('plan'));
    }
}
