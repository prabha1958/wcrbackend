<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateAllianceRequest;
use App\Models\Alliance;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AllianceController extends Controller
{
    /**
     * Member creates an alliance profile for themselves.
     */
    public function store(CreateAllianceRequest $request): JsonResponse
    {
        $user = $request->user();

        $data = $request->validated();

        // Handle file uploads and store paths (public disk)
        foreach (['profile_photo', 'photo1', 'photo2', 'photo3'] as $field) {
            if ($request->hasFile($field)) {
                $data[$field] = $request->file($field)->store('alliances/photos', 'public');
            }
        }

        $data['member_id'] = $user->id;

        $alliance = Alliance::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Alliance profile created successfully.',
            'alliance' => $alliance
        ], 201);
    }
}
