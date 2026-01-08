<?php

namespace App\Http\Controllers;

use App\Http\Requests\AnnouncementRequest;
use App\Models\Announcement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    /**
     * Public: list announcements (optionally filter by published or date)
     */
    public function index(Request $request): JsonResponse
    {
        $query = Announcement::query()->orderByDesc('date');

        if ($request->has('published')) {
            $query->where('published', (bool) $request->boolean('published'));
        }

        if ($request->has('from_date')) {
            $query->where('date', '>=', $request->query('from_date'));
        }

        if ($request->has('to_date')) {
            $query->where('date', '<=', $request->query('to_date'));
        }

        $announcements = $query->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $announcements,
        ]);
    }

    /**
     * Public: show a single announcement
     */
    public function show(Announcement $announcement): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $announcement,
        ]);
    }

    /**
     * Admin: create announcement
     */
    public function store(AnnouncementRequest $request): JsonResponse
    {
        $announcement = Announcement::create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Announcement created successfully.',
            'data' => $announcement,
        ], 201);
    }

    /**
     * Admin: update announcement
     */
    public function update(AnnouncementRequest $request, Announcement $announcement): JsonResponse
    {
        $announcement->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Announcement updated successfully.',
            'data' => $announcement,
        ]);
    }

    /**
     * Admin: delete announcement
     */
    public function destroy(Announcement $announcement): JsonResponse
    {
        $announcement->delete();

        return response()->json([
            'success' => true,
            'message' => 'Announcement deleted successfully.',
        ]);
    }
}
