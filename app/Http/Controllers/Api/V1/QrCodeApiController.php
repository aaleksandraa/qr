<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Qr\StoreQrCodeRequest;
use App\Http\Requests\Qr\UpdateQrCodeRequest;
use App\Http\Resources\QrCodeResource;
use App\Models\QrCode;
use App\Services\Analytics\QrAnalyticsService;
use App\Services\Qr\QrCodeService;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QrCodeApiController extends Controller
{
    public function __construct(
        private readonly QrCodeService $qrCodes,
        private readonly QrAnalyticsService $analytics,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $this->authorizeAbility($request, 'qr:read');
        $workspace = $request->user()->currentWorkspace();
        abort_unless($workspace, 403);

        $qrCodes = QrCode::query()
            ->inWorkspace($workspace)
            ->with(['campaign', 'folder'])
            ->latest()
            ->paginate(min((int) $request->integer('per_page', 15), 50));

        return QrCodeResource::collection($qrCodes)->response();
    }

    public function store(StoreQrCodeRequest $request): JsonResponse
    {
        $this->authorizeAbility($request, 'qr:create');
        $workspace = $request->user()->currentWorkspace();
        abort_unless($workspace, 403);

        $qr = $this->qrCodes->create($workspace, $request->user(), $request->validated());

        return (new QrCodeResource($qr))->response()->setStatusCode(201);
    }

    public function show(Request $request, QrCode $qrCode): QrCodeResource
    {
        $this->authorizeAbility($request, 'qr:read');
        $this->authorize('view', $qrCode);

        return new QrCodeResource($qrCode->load(['campaign', 'folder']));
    }

    public function update(UpdateQrCodeRequest $request, QrCode $qrCode): QrCodeResource
    {
        $this->authorizeAbility($request, 'qr:update');
        $qr = $this->qrCodes->update($qrCode, $request->user(), $request->validated());

        return new QrCodeResource($qr);
    }

    public function destroy(Request $request, QrCode $qrCode): JsonResponse
    {
        $this->authorizeAbility($request, 'qr:delete');
        $this->authorize('delete', $qrCode);
        $qrCode->delete();

        return response()->json(['message' => 'Deleted']);
    }

    public function analytics(Request $request, QrCode $qrCode): JsonResponse
    {
        $this->authorizeAbility($request, 'analytics:read');
        $this->authorize('view', $qrCode);

        return response()->json([
            'data' => $this->analytics->forQr(
                $qrCode,
                CarbonImmutable::now('UTC')->subDays(29)->startOfDay(),
                CarbonImmutable::now('UTC')->endOfDay(),
            ),
        ]);
    }

    private function authorizeAbility(Request $request, string $ability): void
    {
        $user = $request->user();
        if ($user && method_exists($user, 'tokenCan') && $user->currentAccessToken() && ! $user->tokenCan($ability)) {
            abort(403, 'Token is missing the '.$ability.' ability.');
        }
    }
}
