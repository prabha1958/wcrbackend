<?php
// ... existing namespace and imports ...
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMemberRequest;
use App\Models\Member;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\MemberWelcomeMail;

class MembersController extends Controller
{

    public function store(StoreMemberRequest $request): JsonResponse
    {

        $data = $request->validated();

        DB::beginTransaction();

        try {
            // Handle profile photo if uploaded
            if ($request->hasFile('profile_photo')) {
                $file = $request->file('profile_photo');
                $filename = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME))
                    . '-' . time() . '.' . $file->getClientOriginalExtension();

                $path = $file->storeAs('profile_photos', $filename, 'public');
                $data['profile_photo'] = $path;
            }

            if (array_key_exists('membership_fee', $data) && $data['membership_fee'] !== null) {
                $data['membership_fee'] = number_format((float)$data['membership_fee'], 2, '.', '');
            }

            // Create member
            $member = Member::create($data);

            DB::commit();

            // Send welcome email if email is present
            if (!empty($member->email)) {
                try {
                    Mail::to($member->email)->send(new MemberWelcomeMail($member));
                } catch (\Throwable $mailEx) {
                    // Log the mail error (optional). Do NOT rollback the DB transaction here.
                    // \Log::error('Member welcome mail failed: '.$mailEx->getMessage());
                }
            }

            return response()->json([
                'success' => true,
                'data'    => $member,
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();

            // clean up uploaded file if exists
            if (!empty($data['profile_photo']) && Storage::disk('public')->exists($data['profile_photo'])) {
                Storage::disk('public')->delete($data['profile_photo']);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to create member.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
