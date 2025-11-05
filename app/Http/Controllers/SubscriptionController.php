<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Models\Payment;
use App\Services\RazorpayService;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SubscriptionController extends Controller
{
    protected RazorpayService $rz;

    public function __construct(RazorpayService $rz)
    {
        $this->rz = $rz;
    }

    /**
     * Return member's current FY subscription and due months + amount
     */
    public function myDue(Request $request)
    {
        /** @var \App\Models\Member $user */
        $user = $request->user();
        $fy = Subscription::financialYearForDate();

        $sub = Subscription::firstOrCreate(
            ['member_id' => $user->id, 'financial_year' => $fy],
            ['monthly_fee' => $user->membership_fee] // default monthly fee
        );

        $unpaid = $sub->unpaidMonthsUpTo(Carbon::now());
        $dueAmount = count($unpaid) * $sub->monthly_fee;

        return response()->json([
            'success' => true,
            'subscription' => $sub,
            'unpaid_months' => $unpaid,
            'due_amount' => $dueAmount
        ]);
    }

    /**
     * Create a Razorpay order for selected months (member pays via frontend checkout).
     * Expects JSON: { months: ['apr','may'], payment_currency:'INR' }
     */
    public function pay(Request $request)
    {
        $user = $request->user();

        $months = (array) $request->input('months', []); // e.g. ['apr','may']
        if (empty($months)) {
            return response()->json(['success' => false, 'message' => 'No months selected'], 422);
        }

        $fy = Subscription::financialYearForDate();
        $sub = Subscription::firstOrCreate(
            ['member_id' => $user->id, 'financial_year' => $fy],
            ['monthly_fee' => $user->membership_fee]
        );

        // Validate months are in this FY and unpaid
        $validMonths = Subscription::fyMonths();
        foreach ($months as $m) {
            if (! in_array($m, $validMonths)) {
                return response()->json(['success' => false, 'message' => "Invalid month: {$m}"], 422);
            }
            // ensure not already paid
            if (! empty($sub->{"{$m}_payment_id"})) {
                return response()->json(['success' => false, 'message' => "Month {$m} already paid"], 422);
            }
        }

        $amount = count($months) * $sub->monthly_fee; // in rupees
        // Convert to paise for Razorpay:
        $amountPaise = $amount * 100;

        // create receipt id (use subscription id + timestamp)
        $receipt = 'sub_' . $sub->id . '_' . time();

        // create Razorpay order
        $order = $this->rz->createOrder($amountPaise, 'INR', $receipt);

        // create a Payment row with status 'created'
        $payment = Payment::create([
            'member_id' => $user->id,
            'subscription_id' => $sub->id,
            'razorpay_order_id' => $order['id'],
            'amount' => $amount, // rupees
            'status' => 'created',
            'raw' => array_merge($order, ['months' => $months])
        ]);

        return response()->json([
            'success' => true,
            'order' => $order,
            'payment_id' => $payment->id,
            'amount' => $amount,
            'currency' => 'INR',
            'months' => $months
        ]);
    }
}
