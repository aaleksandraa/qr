<?php

namespace App\Http\Controllers;

use App\Models\Folder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FolderController extends Controller
{
    public function index(Request $request): Response
    {
        $workspace = $request->user()->currentWorkspace();
        abort_unless($workspace, 403);

        $folders = Folder::query()
            ->where('workspace_id', $workspace->id)
            ->withCount('qrCodes')
            ->orderBy('name')
            ->get();

        return Inertia::render('folders/index', [
            'folders' => $folders,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $workspace = $request->user()->currentWorkspace();
        abort_unless($workspace, 403);

        Folder::create([
            'workspace_id' => $workspace->id,
            'name' => $request->validate(['name' => ['required', 'string', 'max:80']])['name'],
        ]);

        return back()->with('success', 'Folder created.');
    }

    public function update(Request $request, Folder $folder): RedirectResponse
    {
        $this->authorize('update', $folder);
        $folder->update($request->validate(['name' => ['required', 'string', 'max:80']]));

        return back()->with('success', 'Folder renamed.');
    }

    public function destroy(Folder $folder): RedirectResponse
    {
        $this->authorize('delete', $folder);
        $folder->qrCodes()->update(['folder_id' => null]);
        $folder->delete();

        return back()->with('success', 'Folder deleted.');
    }
}
