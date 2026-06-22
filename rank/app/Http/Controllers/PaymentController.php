<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Razorpay\Api\Api;

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

        // Amount in paise for Razorpay
        $amountPaise = $price * 100;
        $api = new Api(config('services.razorpay.key'), config('services.razorpay.secret'));
        $order = $api->order->create([
            'receipt' => 'order_rcpt_' . Str::random(8),
            'amount' => $amountPaise,
            'currency' => 'INR',
        ]);

        $orderId = $order['id'];
        $keyId = config('services.razorpay.key');
        return view('plans.checkout', compact('plan', 'planName', 'price', 'orderId', 'keyId'));
    }

    public function verify(Request $request)
    {
        $request->validate([
            'plan' => ['required', 'string', 'in:basic,premium'],
            'razorpay_payment_id' => ['required', 'string'],
            'razorpay_order_id' => ['required', 'string'],
            'razorpay_signature' => ['required', 'string'],
        ]);

        $attributes = $request->only('razorpay_payment_id', 'razorpay_order_id', 'razorpay_signature');
        $generatedSignature = hash_hmac('sha256', $attributes['razorpay_order_id'] . '|' . $attributes['razorpay_payment_id'], config('services.razorpay.secret'));
        if ($generatedSignature !== $attributes['razorpay_signature']) {
            return back()->with('error', 'Payment verification failed.');
        }

        $user = auth()->user();
        $plan = $request->input('plan');
        $amount = $request->input('razorpay_payment_id') ? (int) $request->input('razorpay_payment_id') : 0; // placeholder, actual amount not needed

        // Record payment
        Payment::create([
            'user_id' => $user->id,
            'plan' => $plan,
            'amount' => $request->input('amount', 0),
            'transaction_id' => $attributes['razorpay_payment_id'],
            'order_id' => $attributes['razorpay_order_id'],
            'status' => 'completed',
        ]);

        // Update user
        $user->update([
            'plan' => $plan,
            'payment_status' => 'paid',
        ]);

        return redirect()->route('payment.success', ['plan' => $plan]);
    }

    public function webhook(Request $request)
    {
        $payload = $request->getContent();
        $signature = $request->header('X-Razorpay-Signature');
        $expectedSignature = hash_hmac('sha256', $payload, config('services.razorpay.secret'));
        if ($signature !== $expectedSignature) {
            return response('Invalid signature', 400);
        }
        $data = json_decode($payload, true);
        if (isset($data['event']) && $data['event'] === 'payment.captured') {
            $paymentId = $data['payload']['payment']['entity']['id'] ?? null;
            $orderId = $data['payload']['payment']['entity']['order_id'] ?? null;
            if ($orderId) {
                $payment = Payment::where('order_id', $orderId)->first();
                if ($payment) {
                    $payment->update(['status' => 'captured']);
                    $user = $payment->user;
                    $user->update(['payment_status' => 'paid']);
                }
            }
        }
        return response('OK', 200);
    }

    public function success(Request $request)
    {
        $plan = $request->input('plan', 'basic');
        return view('plans.success', compact('plan'));
    }
}
