<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Event\StoreEventRequest;
use App\Http\Requests\Event\UpdateEventRequest;
use App\Http\Resources\EventResource;
use App\Models\Event;
use App\Services\Contracts\SmartInvitationServiceInterface;
use Illuminate\Http\JsonResponse;

class EventController extends Controller
{
    public function __construct(protected SmartInvitationServiceInterface $invitationService) {}

    public function index()
    {
        $events = Event::query()->latest()->paginate(15);

        return EventResource::collection($events);
    }

    public function store(StoreEventRequest $request): JsonResponse
    {
        $this->authorize('create', Event::class);

        $event = Event::create([
            ...$request->validated(),
            'creator_id' => $request->user()->id,
        ]);

        return response()->json(EventResource::make($event), 201);
    }

    public function show(Event $event): JsonResponse
    {
        $this->authorize('view', $event);

        return response()->json(EventResource::make($event->load(['creator', 'committees.user', 'rsvps', 'financialContributions'])));
    }

    public function update(UpdateEventRequest $request, Event $event): JsonResponse
    {
        $this->authorize('update', $event);

        $event->update($request->validated());

        return response()->json(EventResource::make($event->fresh()));
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
