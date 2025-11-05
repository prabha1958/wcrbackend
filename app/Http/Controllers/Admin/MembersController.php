<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateMemberRequest;
use App\Http\Requests\StoreMemberRequest;
use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MembersController extends Controller
{
    /**
     * List members (paginated).
     */
    public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 20);
        $query = Member::query()->orderBy('family_name')->orderBy('first_name');

        // optional filters (active members)
        if ($request->has('active')) {
            $active = filter_var($request->query('active'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($active !== null) {
                $query->where('status_flag', (bool) $active);
            }
        }

        $page = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $page,
        ]);
    }

    /**
     * Store new member (create).
     */
    public function store(StoreMemberRequest $request)
    {
        $data = $request->validated();

        // handle profile photo if your app uses it
        if ($request->hasFile('profile_photo')) {
            $data['profile_photo'] = $request->file('profile_photo')->store('members/photos', 'public');
        }

        // set defaults if not provided
        if (! array_key_exists('status_flag', $data)) {
            $data['status_flag'] = true; // default active
        }

        if (isset($data['status_flag']) && ! $request->user()->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized to update status'], 403);
        }

        $member = Member::create($data);


        return response()->json([
            'success' => true,
            'message' => 'Member created.',
            'data' => $member,
        ], 201);
    }

    /**
     * Show single member
     */
    public function show(Member $member)
    {
        return response()->json([
            'success' => true,
            'data' => $member,
        ]);
    }

    /**
     * Update existing member
     */
    public function update(UpdateMemberRequest $request, Member $member)
    {
        $data = $request->validated();

        if ($request->hasFile('profile_photo')) {
            // delete old photo if present
            if ($member->profile_photo && Storage::disk('public')->exists($member->profile_photo)) {
                Storage::disk('public')->delete($member->profile_photo);
            }
            $data['profile_photo'] = $request->file('profile_photo')->store('members/photos', 'public');
        }

        if (isset($data['status_flag']) && ! $request->user()->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized to update status'], 403);
        }

        $member->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Member updated.',
            'data' => $member,
        ]);
    }

    /**
     * Delete member
     */
    public function destroy(Member $member)
    {
        // optional: remove photo file
        if ($member->profile_photo && Storage::disk('public')->exists($member->profile_photo)) {
            Storage::disk('public')->delete($member->profile_photo);
        }

        $member->delete();

        return response()->json([
            'success' => true,
            'message' => 'Member deleted.',
        ]);
    }
}
