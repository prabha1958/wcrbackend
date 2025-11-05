<?php

namespace App\Http\Controllers;

use App\Services\RazorpayService;
use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\Subscription;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RazorpayWebhookController extends Controller
{
    protected RazorpayService $rz;

    public function __construct(RazorpayService $rz)
    {
        $this->rz = $rz;
    }

    public function handle(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('X-Razorpay-Signature', '');

        $secret = env('RAZORPAY_WEBHOOK_SECRET');

        if (! $this->rz->verifyWebhookSignature($payload, $sigHeader, $secret)) {
            Log::warning('Invalid webhook signature');
            return response()->json(['success' => false, 'message' => 'Invalid signature'], 400);
        }

        $event = $request->input('event');
        $data = $request->input('payload', []);

        // We care about payment.captured or payment.authorized -> payment.captured indicates success
        if ($event === 'payment.captured' || $event === 'payment.authorized') {
            $paymentEntity = data_get($data, 'payment.entity', []);
            $razorpayPaymentId = data_get($paymentEntity, 'id');
            $razorpayOrderId = data_get($paymentEntity, 'order_id');
            $amountPaise = data_get($paymentEntity, 'amount'); // in paise
            $amount = intval($amountPaise / 100);

            // find Payment row by order id
            $payment = Payment::where('razorpay_order_id', $razorpayOrderId)->first();

            if (! $payment) {
                // Could be direct payment not created by our order. Create a record.
                $payment = Payment::create([
                    'member_id' => null,
                    'subscription_id' => null,
                    'razorpay_order_id' => $razorpayOrderId,
                    'razorpay_payment_id' => $razorpayPaymentId,
                    'razorpay_signature' => $request->header('X-Razorpay-Signature'),
                    'amount' => $amount,
                    'status' => 'paid',
                    'raw' => $paymentEntity
                ]);
            } else {
                // Update payment row
                $payment->update([
                    'razorpay_payment_id' => $razorpayPaymentId,
                    'razorpay_signature' => $request->header('X-Razorpay-Signature'),
                    'status' => 'paid',
                    'raw' => $paymentEntity
                ]);
            }

            // Determine which months this payment intends to cover.
            // We stored months selection on client — but webhook doesn't carry it by default.
            // Strategy: store mapping from order->months in Payment.raw when order created (we will add that).
            // Here we try to retrieve months if present:
            $months = data_get($payment->raw, 'months', null);

            // If months not present, you may infer or decide default behavior — for safety, we fail if months unknown.
            if (empty($months)) {
                // If months not available, maybe order.receipt contains info
                // Attempt to parse sub id from receipt
                $receipt = data_get($payment->raw, 'receipt') ?? data_get($payment->raw, 'notes.receipt');
                // fallback: do nothing, admin must reconcile
                Log::warning("Payment received for order {$razorpayOrderId} but months not found. Receipt: " . $receipt);
                return response()->json(['success' => true, 'message' => 'Payment recorded but months unknown'], 200);
            }

            // Update subscription months
            $sub = $payment->subscription;
            if (! $sub) {
                // Try to find subscription via receipt or other
                // If we included sub id in receipt at order creation, we could fetch it.
            }
            if ($sub) {
                DB::transaction(function () use ($sub, $months, $razorpayPaymentId) {
                    foreach ($months as $m) {
                        $paymentCol = "{$m}_payment_id";
                        $paidAtCol = "{$m}_paid_at";
                        if (empty($sub->$paymentCol)) {
                            $sub->update([
                                $paymentCol => $razorpayPaymentId,
                                $paidAtCol => Carbon::now()
                            ]);
                        }
                    }
                });
            }

            return response()->json(['success' => true]);
        }

        // For other events, just ack
        return response()->json(['success' => true]);
    }
}
