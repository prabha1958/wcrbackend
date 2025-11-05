<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminCreateAllianceRequest;
use App\Models\Alliance;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AllianceController extends Controller
{
    /**
     * Admin creates an alliance profile for any member (member_id required).
     */
    public function store(AdminCreateAllianceRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Handle file uploads and store paths (public disk)
        foreach (['profile_photo', 'photo1', 'photo2', 'photo3'] as $field) {
            if ($request->hasFile($field)) {
                $data[$field] = $request->file($field)->store('alliances/photos', 'public');
            }
        }

        // ensure member_id present (AdminCreateAllianceRequest enforces it)
        $alliance = Alliance::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Alliance created by admin successfully.',
            'alliance' => $alliance
        ], 201);
    }
}
