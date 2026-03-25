<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\FinancialContribution;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FinancialContributionController extends Controller
{
    public function index(Event $event): JsonResponse
    {
        $this->authorize('manageFinances', $event);

        $contributions = $event->financialContributions()
            ->with('contributor:id,name')
            ->paginate(20);

        return response()->json($contributions);
    }

    public function store(Request $request, Event $event): JsonResponse
    {
        $validated = $request->validate([
            'amount'           => 'required|numeric|min:0.01',
            'currency'         => 'nullable|string|size:3',
            'payment_method'   => 'required|in:transfer,cash,other',
            'reference_number' => 'nullable|string|max:100',
            'note'             => 'nullable|string',
        ]);

        $contribution = $event->financialContributions()->create([
            ...$validated,
            'contributor_id' => $request->user()->id,
            'status'         => 'pending',
        ]);

        return response()->json($contribution, 201);
    }

    public function confirm(Request $request, Event $event, FinancialContribution $contribution): JsonResponse
    {
        $this->authorize('manageFinances', $event);

        $contribution->update([
            'status'       => 'confirmed',
            'confirmed_by' => $request->user()->id,
            'confirmed_at' => now(),
        ]);

        return response()->json($contribution);
    }
}
