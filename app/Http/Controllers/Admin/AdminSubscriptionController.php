<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\Payment;
use App\Models\Member;
use App\Services\RazorpayService;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AdminSubscriptionController extends Controller
{
    protected RazorpayService $rz;

    public function __construct(RazorpayService $rz)
    {
        $this->rz = $rz;
    }

    /**
     * Create/ensure subscription for member
     */
    public function createSubscription(Request $request, Member $member)
    {
        $fy = Subscription::financialYearForDate();
        $sub = Subscription::firstOrCreate(
            ['member_id' => $member->id, 'financial_year' => $fy],
            ['monthly_fee' => $request->input('monthly_fee', 100)]
        );

        return response()->json(['success' => true, 'subscription' => $sub]);
    }

    /**
     * Admin: compute due for a member
     */
    public function due(Request $request, Member $member)
    {
        $fy = Subscription::financialYearForDate();
        $sub = Subscription::firstOrCreate(['member_id' => $member->id, 'financial_year' => $fy], ['monthly_fee' => 100]);
        $unpaid = $sub->unpaidMonthsUpTo(Carbon::now());
        $dueAmount = count($unpaid) * $sub->monthly_fee;
        return response()->json(['success' => true, 'unpaid_months' => $unpaid, 'due_amount' => $dueAmount, 'subscription' => $sub]);
    }

    /**
     * Admin: pay on behalf. Admin chooses months to mark paid and supplies a payment source.
     *
     * For admin, you might:
     * - Create Razorpay order and let admin pay via UI.
     * - Or record an offline payment (cash/cheque) and directly mark months paid by storing a payment record.
     *
     * This method creates a Razorpay order similar to member pay, intended for admin UI to complete checkout.
     */
    public function payOnBehalf(Request $request, Member $member)
    {
        $months = (array)$request->input('months', []);
        if (empty($months)) {
            return response()->json(['success' => false, 'message' => 'No months selected'], 422);
        }

        $fy = Subscription::financialYearForDate();
        $sub = Subscription::firstOrCreate(['member_id' => $member->id, 'financial_year' => $fy], ['monthly_fee' => $request->input('monthly_fee', 100)]);

        foreach ($months as $m) {
            if (! in_array($m, Subscription::fyMonths())) {
                return response()->json(['success' => false, 'message' => "Invalid month {$m}"], 422);
            }
            if (! empty($sub->{"{$m}_payment_id"})) {
                return response()->json(['success' => false, 'message' => "Month {$m} already paid"], 422);
            }
        }

        $amount = count($months) * $sub->monthly_fee;
        $amountPaise = $amount * 100;
        $receipt = 'admin_sub_' . $sub->id . '_' . time();

        $order = $this->rz->createOrder($amountPaise, 'INR', $receipt);

        $payment = Payment::create([
            'member_id' => $member->id,
            'subscription_id' => $sub->id,
            'razorpay_order_id' => $order['id'],
            'amount' => $amount,
            'status' => 'created',
            'raw' => array_merge($order, ['months' => $months])
        ]);

        return response()->json(['success' => true, 'order' => $order, 'payment_id' => $payment->id, 'amount' => $amount, 'months' => $months]);
    }
}
