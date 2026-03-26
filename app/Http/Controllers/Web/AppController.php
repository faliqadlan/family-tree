<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AccessRequest;
use App\Models\Event;
use App\Models\Profile;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AppController extends Controller
{
    public function welcome(): Response
    {
        return Inertia::render('Welcome', [
            'canLogin' => true,
            'canRegister' => true,
            'laravelVersion' => Application::VERSION,
            'phpVersion' => PHP_VERSION,
        ]);
    }

    public function dashboard(Request $request): Response
    {
        return Inertia::render('Dashboard', [
            'stats' => [
                'totalProfiles' => Profile::count(),
                'totalEvents' => Event::count(),
                'pendingAccessRequests' => AccessRequest::query()
                    ->where('target_id', $request->user()->id)
                    ->where('status', 'pending')
                    ->count(),
            ],
        ]);
    }

    public function familyTree(Request $request): Response
    {
        return Inertia::render('FamilyTree', [
            'initialAncestorUuid' => $request->user()?->profile?->graph_node_id,
            'defaultDepth' => 4,
        ]);
    }

    public function profileManagement(Request $request): Response
    {
        return Inertia::render('ProfileManagement', [
            'profileId' => $request->user()?->profile?->id,
        ]);
    }
}
