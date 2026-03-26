<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\FinancialContribution\ConfirmFinancialContributionRequest;
use App\Http\Requests\FinancialContribution\StoreFinancialContributionRequest;
use App\Http\Requests\FinancialContribution\UpdateFinancialContributionRequest;
use App\Http\Resources\FinancialContributionResource;
use App\Models\Event;
use App\Models\FinancialContribution;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FinancialContributionController extends Controller
{
    public function index(Request $request)
    {
        $eventId = $request->query('event_id');

        $query = FinancialContribution::query()->with(['event', 'contributor', 'confirmedBy']);

        if ($eventId) {
            $event = Event::findOrFail($eventId);

            if (! $request->user()->isSuperAdmin() && ! $request->user()->can('manageFinances', $event)) {
                $query->where('contributor_id', $request->user()->id);
            }

            $query->where('event_id', $event->id);
        } elseif (! $request->user()->isSuperAdmin()) {
            $query->where('contributor_id', $request->user()->id);
        }

        return FinancialContributionResource::collection($query->latest()->paginate(20));
    }

    public function store(StoreFinancialContributionRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $contribution = FinancialContribution::create([
            ...$validated,
            'contributor_id' => $request->user()->id,
            'status' => 'pending',
        ]);

        return response()->json(FinancialContributionResource::make($contribution->load(['event', 'contributor', 'confirmedBy'])), 201);
    }

    public function show(Request $request, FinancialContribution $financialContribution): JsonResponse
    {
        $event = Event::findOrFail($financialContribution->event_id);
        $canManage = $request->user()->isSuperAdmin() || $request->user()->can('manageFinances', $event);
        $isOwner = $financialContribution->contributor_id === $request->user()->id;

        abort_unless($canManage || $isOwner, 403);

        return response()->json(FinancialContributionResource::make($financialContribution->load(['event', 'contributor', 'confirmedBy'])));
    }

    public function update(UpdateFinancialContributionRequest $request, FinancialContribution $financialContribution): JsonResponse
    {
        $event = Event::findOrFail($financialContribution->event_id);
        $canManage = $request->user()->isSuperAdmin() || $request->user()->can('manageFinances', $event);
        $isOwner = $financialContribution->contributor_id === $request->user()->id;

        abort_unless($canManage || $isOwner, 403);

        if (! $canManage) {
            abort_if($financialContribution->status !== 'pending', 422, 'Only pending contributions can be updated.');
        }

        $validated = $request->validated();

        if (! $canManage) {
            unset($validated['status']);
        }

        $financialContribution->update($validated);

        return response()->json(FinancialContributionResource::make($financialContribution->fresh()->load(['event', 'contributor', 'confirmedBy'])));
    }

    public function destroy(Request $request, FinancialContribution $financialContribution): JsonResponse
    {
        $event = Event::findOrFail($financialContribution->event_id);
        $canManage = $request->user()->isSuperAdmin() || $request->user()->can('manageFinances', $event);
        $isOwner = $financialContribution->contributor_id === $request->user()->id;

        abort_unless($canManage || $isOwner, 403);

        $financialContribution->delete();

        return response()->json(null, 204);
    }

    public function confirm(ConfirmFinancialContributionRequest $request, FinancialContribution $financialContribution): JsonResponse
    {
        $event = Event::findOrFail($financialContribution->event_id);
        $this->authorize('manageFinances', $event);

        $financialContribution->update([
            'status'       => $request->validated()['status'] ?? 'confirmed',
            'confirmed_by' => $request->user()->id,
            'confirmed_at' => now(),
        ]);

        return response()->json(FinancialContributionResource::make($financialContribution->fresh()->load(['event', 'contributor', 'confirmedBy'])));
    }
}
