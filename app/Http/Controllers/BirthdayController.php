<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Member;
use Carbon\Carbon;

class BirthdayController extends Controller
{
    /**
     * Get all members whose birthday is today.
     */
    public function today(Request $request)
    {
        $today = Carbon::today();

        $members = Member::query()
            ->whereMonth('date_of_birth', $today->month)
            ->whereDay('date_of_birth', $today->day)
            ->orderBy('first_name')
            ->get(['id', 'family_name', 'first_name', 'last_name', 'date_of_birth', 'mobile_number', 'email']);

        return response()->json([
            'success' => true,
            'count' => $members->count(),
            'date' => $today->toDateString(),
            'data' => $members,
        ]);
    }


    public function upcomingWeek()
    {
        $today = Carbon::today();
        $nextWeek = $today->copy()->addDays(7);

        $members = Member::query()
            ->whereRaw("DATE_FORMAT(date_of_birth, '%m-%d') BETWEEN ? AND ?", [
                $today->format('m-d'),
                $nextWeek->format('m-d'),
            ])
            ->orderByRaw("DATE_FORMAT(date_of_birth, '%m-%d')")
            ->get();

        return response()->json([
            'success' => true,
            'range' => [$today->toDateString(), $nextWeek->toDateString()],
            'data' => $members,
        ]);
    }
}
