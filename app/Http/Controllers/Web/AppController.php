<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AccessRequest;
use App\Models\Event;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

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
        $isSuperAdmin = $request->user()->isSuperAdmin();

        return Inertia::render('Dashboard', [
            'isSuperAdmin' => $isSuperAdmin,
            'stats' => [
                'totalProfiles' => Profile::count(),
                'totalEvents' => Event::count(),
                'pendingAccessRequests' => AccessRequest::query()
                    ->where('target_id', $request->user()->id)
                    ->where('status', 'pending')
                    ->count(),
                'allAccessRequests' => $isSuperAdmin
                    ? AccessRequest::query()->count()
                    : null,
                'totalUsers' => $isSuperAdmin
                    ? User::query()->count()
                    : null,
            ],
        ]);
    }

    public function familyTree(Request $request): Response
    {
        return Inertia::render('FamilyTree');
    }

    public function profileManagement(Request $request): Response
    {
        return Inertia::render('ProfileManagement', [
            'profileId' => $request->user()?->profile?->id,
        ]);
    }

    public function adminTools(Request $request): Response
    {
        abort_unless($request->user()->isSuperAdmin(), 403);

        return Inertia::render('AdminTools', [
            'templateDownloadUrl' => route('admin.stub-template.download'),
        ]);
    }

    public function downloadStubTemplate(Request $request): BinaryFileResponse
    {
        abort_unless($request->user()->isSuperAdmin(), 403);

        $path = 'stub-imports/family_import_template.csv';

        abort_unless(Storage::disk('local')->exists($path), 404, 'Template file not found.');

        return response()->download(Storage::disk('local')->path($path), 'family_import_template.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }
}
