<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ApiTokenController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('settings/api-tokens', [
            'tokens' => $request->user()->tokens()->latest()->get()->map(fn ($token) => [
                'id' => $token->id,
                'name' => $token->name,
                'abilities' => $token->abilities,
                'last_used_at' => $token->last_used_at?->toIso8601String(),
                'created_at' => $token->created_at?->toIso8601String(),
            ]),
            'plainTextToken' => $request->session()->get('plainTextToken'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'abilities' => ['nullable', 'array'],
            'abilities.*' => ['in:qr:read,qr:create,qr:update,qr:delete,analytics:read'],
        ]);

        $token = $request->user()->createToken(
            $data['name'],
            $data['abilities'] ?? ['qr:read', 'qr:create', 'qr:update', 'analytics:read'],
        );

        return back()->with('plainTextToken', $token->plainTextToken);
    }

    public function destroy(Request $request, string $token): RedirectResponse
    {
        $request->user()->tokens()->where('id', $token)->delete();

        return back()->with('success', 'Token revoked.');
    }
}
