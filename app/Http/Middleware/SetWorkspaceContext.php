<?php

namespace App\Http\Middleware;

use App\Services\Workspace\WorkspaceService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetWorkspaceContext
{
    public function __construct(private readonly WorkspaceService $workspaces) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ! $user->currentWorkspace()) {
            $this->workspaces->provisionDefaultWorkspace($user);
        }

        return $next($request);
    }
}
