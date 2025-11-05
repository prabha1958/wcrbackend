<?php

namespace App\Http\Controllers;

use App\Http\Requests\WomenFellowshipRequest;
use App\Models\WomenFellowship;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Arr;

class WomenFellowshipController extends Controller
{
    // Public: list events (paginated)
    public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 15);
        $query = WomenFellowship::orderByDesc('date_of_event');

        if ($request->has('from')) {
            $query->where('date_of_event', '>=', $request->query('from'));
        }
        if ($request->has('to')) {
            $query->where('date_of_event', '<=', $request->query('to'));
        }

        $page = $query->paginate($perPage);

        // convert photo paths to public URLs in the returned collection
        $page->getCollection()->transform(function ($item) {
            $item->event_photos = array_map(fn($p) => $p ? asset('storage/' . $p) : null, $item->event_photos ?? []);
            return $item;
        });

        return response()->json(['success' => true, 'data' => $page]);
    }

    // Public: show one
    public function show(WomenFellowship $womenFellowship)
    {
        $womenFellowship->event_photos = array_map(fn($p) => $p ? asset('storage/' . $p) : null, $womenFellowship->event_photos ?? []);
        return response()->json(['success' => true, 'data' => $womenFellowship]);
    }

    // Admin: create (supports multiple photos)
    public function store(WomenFellowshipRequest $request)
    {
        $data = $request->validated();

        $photos = [];
        if ($request->hasFile('event_photos')) {
            foreach ($request->file('event_photos') as $file) {
                $photos[] = $file->store('women_fellowships/photos', 'public');
            }
        }
        $data['event_photos'] = $photos;

        $wf = WomenFellowship::create($data);
        return response()->json(['success' => true, 'data' => $wf], 201);
    }

    // Admin: update (append_photos=1 to append; otherwise replace)
    public function update(WomenFellowshipRequest $request, WomenFellowship $womenFellowship)
    {
        $data = $request->validated();
        $existing = $womenFellowship->event_photos ?? [];

        if ($request->hasFile('event_photos')) {
            $uploaded = [];
            foreach ($request->file('event_photos') as $file) {
                $uploaded[] = $file->store('women_fellowships/photos', 'public');
            }

            if ($request->boolean('append_photos')) {
                $data['event_photos'] = array_slice(array_merge($existing, $uploaded), 0, 4);
            } else {
                // delete old files
                foreach ($existing as $path) {
                    if ($path && Storage::disk('public')->exists($path)) {
                        Storage::disk('public')->delete($path);
                    }
                }
                $data['event_photos'] = $uploaded;
            }
        }

        $womenFellowship->update($data);
        return response()->json(['success' => true, 'data' => $womenFellowship]);
    }

    // Admin: delete record (and photos)
    public function destroy(WomenFellowship $womenFellowship)
    {
        foreach ($womenFellowship->event_photos ?? [] as $path) {
            if ($path && Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        }
        $womenFellowship->delete();
        return response()->json(['success' => true, 'message' => 'Deleted']);
    }

    // Admin: remove one photo by storage path (body: photo_path)
    public function removePhoto(Request $request, WomenFellowship $womenFellowship)
    {
        $photo = (string) $request->input('photo_path');
        if ($photo === '') {
            return response()->json(['success' => false, 'message' => 'photo_path is required'], 422);
        }

        $photos = $womenFellowship->event_photos ?? [];
        $new = Arr::where($photos, fn($p) => $p !== $photo);

        if (in_array($photo, $photos) && Storage::disk('public')->exists($photo)) {
            Storage::disk('public')->delete($photo);
        }

        $womenFellowship->event_photos = array_values($new);
        $womenFellowship->save();

        return response()->json(['success' => true, 'data' => $womenFellowship]);
    }
}
