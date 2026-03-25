<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Services\Contracts\SmartInvitationServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function __construct(protected SmartInvitationServiceInterface $invitationService) {}

    public function index(): JsonResponse
    {
        $events = Event::where('status', 'published')->latest()->paginate(15);
        return response()->json($events);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Event::class);

        $validated = $request->validate([
            'name'             => 'required|string|max:255',
            'description'      => 'nullable|string',
            'location'         => 'nullable|string|max:255',
            'starts_at'        => 'required|date',
            'ends_at'          => 'nullable|date|after:starts_at',
            'ancestor_node_id' => 'nullable|uuid',
            'invitation_depth' => 'nullable|integer|min:1|max:10',
        ]);

        $event = Event::create([...$validated, 'creator_id' => $request->user()->id]);

        return response()->json($event, 201);
    }

    public function show(Event $event): JsonResponse
    {
        $this->authorize('view', $event);
        return response()->json($event->load(['creator', 'committees.user', 'rsvps']));
    }

    public function update(Request $request, Event $event): JsonResponse
    {
        $this->authorize('update', $event);

        $validated = $request->validate([
            'name'             => 'sometimes|string|max:255',
            'description'      => 'nullable|string',
            'location'         => 'nullable|string|max:255',
            'starts_at'        => 'sometimes|date',
            'ends_at'          => 'nullable|date|after:starts_at',
            'status'           => 'sometimes|in:draft,published,cancelled,completed',
            'ancestor_node_id' => 'nullable|uuid',
            'invitation_depth' => 'nullable|integer|min:1|max:10',
        ]);

        $event->update($validated);

        return response()->json($event);
    }

    public function destroy(Event $event): JsonResponse
    {
        $this->authorize('delete', $event);
        $event->delete();
        return response()->json(null, 204);
    }

    public function dispatchInvitations(Event $event): JsonResponse
    {
        $this->authorize('update', $event);

        $this->invitationService->dispatchInvitations($event);

        return response()->json(['message' => 'Invitations dispatched successfully.']);
    }
}
