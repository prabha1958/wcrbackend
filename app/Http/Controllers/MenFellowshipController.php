<?php

namespace App\Http\Controllers;

use App\Http\Requests\MenFellowshipRequest;
use App\Models\MenFellowship;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Arr;

class MenFellowshipController extends Controller
{
    /**
     * Public: list all men fellowships.
     */
    public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 15);
        $query = MenFellowship::orderByDesc('date_of_event');

        if ($request->has('from')) {
            $query->where('date_of_event', '>=', $request->query('from'));
        }
        if ($request->has('to')) {
            $query->where('date_of_event', '<=', $request->query('to'));
        }

        $fellowships = $query->paginate($perPage);
        return response()->json(['success' => true, 'data' => $fellowships]);
    }

    /**
     * Public: show single record
     */
    public function show(MenFellowship $menFellowship)
    {
        return response()->json(['success' => true, 'data' => $menFellowship]);
    }

    /**
     * Admin: create record
     */
    public function store(MenFellowshipRequest $request)
    {
        $data = $request->validated();

        $photos = [];
        if ($request->hasFile('event_photos')) {
            foreach ($request->file('event_photos') as $file) {
                $photos[] = $file->store('men_fellowships/photos', 'public');
            }
        }
        $data['event_photos'] = $photos;

        $fellowship = MenFellowship::create($data);
        return response()->json(['success' => true, 'data' => $fellowship], 201);
    }

    /**
     * Admin: update record (can append or replace photos)
     */
    public function update(MenFellowshipRequest $request, MenFellowship $menFellowship)
    {
        $data = $request->validated();
        $existing = $menFellowship->event_photos ?? [];

        if ($request->hasFile('event_photos')) {
            $uploaded = [];
            foreach ($request->file('event_photos') as $file) {
                $uploaded[] = $file->store('men_fellowships/photos', 'public');
            }

            if ($request->boolean('append_photos')) {
                $data['event_photos'] = array_slice(array_merge($existing, $uploaded), 0, 4);
            } else {
                foreach ($existing as $path) {
                    if ($path && Storage::disk('public')->exists($path)) {
                        Storage::disk('public')->delete($path);
                    }
                }
                $data['event_photos'] = $uploaded;
            }
        }

        $menFellowship->update($data);
        return response()->json(['success' => true, 'data' => $menFellowship]);
    }

    /**
     * Admin: delete record
     */
    public function destroy(MenFellowship $menFellowship)
    {
        foreach ($menFellowship->event_photos ?? [] as $path) {
            if ($path && Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        }

        $menFellowship->delete();
        return response()->json(['success' => true, 'message' => 'Record deleted']);
    }

    /**
     * Admin: remove one photo
     */
    public function removePhoto(Request $request, MenFellowship $menFellowship)
    {
        $photo = $request->input('photo_path');
        if (! $photo) {
            return response()->json(['success' => false, 'message' => 'photo_path required'], 422);
        }

        $photos = $menFellowship->event_photos ?? [];
        $new = Arr::where($photos, fn($p) => $p !== $photo);

        if (in_array($photo, $photos) && Storage::disk('public')->exists($photo)) {
            Storage::disk('public')->delete($photo);
        }

        $menFellowship->event_photos = array_values($new);
        $menFellowship->save();

        return response()->json(['success' => true, 'data' => $menFellowship]);
    }
}
