<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Rsvp\StoreRsvpRequest;
use App\Http\Requests\Rsvp\UpdateRsvpRequest;
use App\Http\Resources\RsvpResource;
use App\Models\Event;
use App\Models\Rsvp;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RsvpController extends Controller
{
    public function index(Request $request)
    {
        $query = Rsvp::query()->with(['event', 'user']);

        if (! $request->user()->isSuperAdmin()) {
            $query->where('user_id', $request->user()->id);
        }

        return RsvpResource::collection($query->latest()->paginate(20));
    }

    public function store(StoreRsvpRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $event = Event::findOrFail($validated['event_id']);
        $this->authorize('view', $event);

        $rsvp = Rsvp::updateOrCreate(
            ['event_id' => $event->id, 'user_id' => $request->user()->id],
            [
                'status' => $validated['status'] ?? 'pending',
                'pax' => $validated['pax'] ?? 1,
                'note' => $validated['note'] ?? null,
            ]
        );

        return response()->json(RsvpResource::make($rsvp->load(['event', 'user'])), 201);
    }

    public function show(Request $request, Rsvp $rsvp): JsonResponse
    {
        $event = Event::findOrFail($rsvp->event_id);

        abort_unless(
            $request->user()->isSuperAdmin()
                || $rsvp->user_id === $request->user()->id
                || $request->user()->can('update', $event),
            403
        );

        return response()->json(RsvpResource::make($rsvp->load(['event', 'user'])));
    }

    public function update(UpdateRsvpRequest $request, Rsvp $rsvp): JsonResponse
    {
        $event = Event::findOrFail($rsvp->event_id);

        abort_unless(
            $request->user()->isSuperAdmin()
                || $rsvp->user_id === $request->user()->id
                || $request->user()->can('update', $event),
            403
        );

        $rsvp->update($request->validated());

        return response()->json(RsvpResource::make($rsvp->fresh()->load(['event', 'user'])));
    }

    public function destroy(Request $request, Rsvp $rsvp): JsonResponse
    {
        $event = Event::findOrFail($rsvp->event_id);

        abort_unless(
            $request->user()->isSuperAdmin()
                || $rsvp->user_id === $request->user()->id
                || $request->user()->can('update', $event),
            403
        );

        $rsvp->delete();

        return response()->json(null, 204);
    }
}
