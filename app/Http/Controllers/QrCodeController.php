<?php

namespace App\Http\Controllers;

use App\Enums\QrStatus;
use App\Http\Requests\Qr\StoreQrCodeRequest;
use App\Http\Requests\Qr\UpdateQrCodeRequest;
use App\Http\Resources\QrCodeResource;
use App\Models\QrCode;
use App\Services\Analytics\QrAnalyticsService;
use App\Services\Qr\QrCodeService;
use App\Services\Qr\QrImageGenerator;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class QrCodeController extends Controller
{
    public function __construct(
        private readonly QrCodeService $qrCodes,
        private readonly QrImageGenerator $images,
        private readonly QrAnalyticsService $analytics,
    ) {}

    public function index(Request $request): InertiaResponse
    {
        $workspace = $request->user()->currentWorkspace();
        abort_unless($workspace, 403);

        $query = QrCode::query()
            ->inWorkspace($workspace)
            ->with(['campaign', 'folder'])
            ->latest();

        if ($type = $request->string('type')->toString()) {
            $query->where('qr_type', $type);
        }
        if ($status = $request->string('status')->toString()) {
            $query->where('status', $status);
        }
        if ($search = $request->string('search')->toString()) {
            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('destination_url', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }
        if ($campaign = $request->string('campaign')->toString()) {
            $query->whereHas('campaign', fn ($q) => $q->where('public_id', $campaign));
        }
        if ($folder = $request->string('folder')->toString()) {
            $query->whereHas('folder', fn ($q) => $q->where('public_id', $folder));
        }

        $qrCodes = $query->paginate(min((int) $request->integer('per_page', 15), 50))->withQueryString();

        return Inertia::render('qr-codes/index', [
            'filters' => $request->only(['type', 'status', 'search', 'campaign', 'folder']),
            'qrCodes' => QrCodeResource::collection($qrCodes)->response()->getData(true),
        ]);
    }

    public function create(Request $request): InertiaResponse
    {
        $workspace = $request->user()->currentWorkspace();

        return Inertia::render('qr-codes/create', [
            'campaigns' => $workspace?->campaigns()->orderBy('name')->get(['id', 'public_id', 'name']) ?? [],
            'folders' => $workspace?->folders()->orderBy('name')->get(['id', 'public_id', 'name']) ?? [],
        ]);
    }

    public function store(StoreQrCodeRequest $request): RedirectResponse
    {
        $workspace = $request->user()->currentWorkspace();
        abort_unless($workspace, 403);

        $data = $request->validated();
        $data['folder_id'] = $this->resolveFolderId($workspace->id, $request->input('folder_id'), $request->input('folder_public_id'));
        $data['campaign_id'] = $this->resolveCampaignId($workspace->id, $request->input('campaign_id'), $request->input('campaign_public_id'));
        $data['logo'] = $request->file('logo');

        $qr = $this->qrCodes->create($workspace, $request->user(), $data);

        return redirect()->route('qr-codes.show', $qr)->with('success', 'QR code created.');
    }

    public function show(Request $request, QrCode $qrCode): InertiaResponse
    {
        $this->authorize('view', $qrCode);
        $qrCode->load(['campaign', 'folder', 'destinationHistory.changer', 'redirectRules', 'tags']);

        $from = CarbonImmutable::now('UTC')->subDays(29)->startOfDay();
        $to = CarbonImmutable::now('UTC')->endOfDay();

        return Inertia::render('qr-codes/show', [
            'qrCode' => (new QrCodeResource($qrCode))->resolve(),
            'history' => $qrCode->destinationHistory->take(20)->map(fn ($row) => [
                'old_url' => $row->old_url,
                'new_url' => $row->new_url,
                'changed_by' => $row->changer?->name,
                'created_at' => $row->created_at?->toIso8601String(),
            ]),
            'rules' => $qrCode->redirectRules,
            'analytics' => $this->analytics->forQr($qrCode, $from, $to),
        ]);
    }

    public function edit(QrCode $qrCode): InertiaResponse
    {
        $this->authorize('update', $qrCode);
        $qrCode->load(['campaign', 'folder']);

        return Inertia::render('qr-codes/edit', [
            'qrCode' => (new QrCodeResource($qrCode))->resolve(),
            'campaigns' => $qrCode->workspace->campaigns()->orderBy('name')->get(['id', 'public_id', 'name']),
            'folders' => $qrCode->workspace->folders()->orderBy('name')->get(['id', 'public_id', 'name']),
        ]);
    }

    public function update(UpdateQrCodeRequest $request, QrCode $qrCode): RedirectResponse
    {
        $data = $request->validated();
        $data['logo'] = $request->file('logo');
        $this->qrCodes->update($qrCode, $request->user(), $data);

        return redirect()->route('qr-codes.show', $qrCode)->with('success', 'QR code updated.');
    }

    public function destroy(QrCode $qrCode): RedirectResponse
    {
        $this->authorize('delete', $qrCode);
        $qrCode->delete();

        return redirect()->route('qr-codes.index')->with('success', 'QR code archived.');
    }

    public function pause(Request $request, QrCode $qrCode): RedirectResponse
    {
        $this->authorize('update', $qrCode);
        $this->qrCodes->changeStatus($qrCode, QrStatus::Paused, $request->user());

        return back()->with('success', 'QR code paused.');
    }

    public function activate(Request $request, QrCode $qrCode): RedirectResponse
    {
        $this->authorize('update', $qrCode);
        $this->qrCodes->changeStatus($qrCode, QrStatus::Active, $request->user());

        return back()->with('success', 'QR code activated.');
    }

    public function archive(Request $request, QrCode $qrCode): RedirectResponse
    {
        $this->authorize('update', $qrCode);
        $this->qrCodes->changeStatus($qrCode, QrStatus::Archived, $request->user());

        return back()->with('success', 'QR code archived.');
    }

    public function duplicate(Request $request, QrCode $qrCode): RedirectResponse
    {
        $this->authorize('view', $qrCode);
        $copy = $this->qrCodes->duplicate($qrCode, $request->user());

        return redirect()->route('qr-codes.show', $copy)->with('success', 'QR code duplicated.');
    }

    public function convertToDynamic(Request $request, QrCode $qrCode): RedirectResponse
    {
        $this->authorize('update', $qrCode);
        $copy = $this->qrCodes->convertToDynamic($qrCode, $request->user());

        return redirect()->route('qr-codes.show', $copy)->with('success', 'A new Dynamic QR was created. Printed Static codes are unchanged.');
    }

    public function convertToStatic(Request $request, QrCode $qrCode): RedirectResponse
    {
        $this->authorize('update', $qrCode);
        $copy = $this->qrCodes->convertToStatic($qrCode, $request->user());

        return redirect()->route('qr-codes.show', $copy)->with('success', 'A new Static QR was created. Printed Dynamic codes are unchanged.');
    }

    public function preview(Request $request): Response
    {
        $payload = (string) $request->input('payload', '');
        abort_if($payload === '' || strlen($payload) > 2000, 422);

        $svg = $this->images->svg($payload, $request->input('design', []), 320);

        return response($svg, 200, [
            'Content-Type' => 'image/svg+xml',
            'Cache-Control' => 'no-store',
        ]);
    }

    public function download(Request $request, QrCode $qrCode): StreamedResponse
    {
        $this->authorize('view', $qrCode);

        $format = strtolower((string) $request->input('format', 'svg'));
        abort_unless(in_array($format, ['svg', 'png'], true), 422);

        $size = (int) $request->input('size', $format === 'png' ? 1024 : 512);
        $allowed = $format === 'png' ? config('qr.export.png_sizes', [512, 1024, 2048]) : [256, 512, 1024];
        if (! in_array($size, $allowed, true)) {
            $size = $format === 'png' ? 1024 : 512;
        }

        $binary = $this->images->generate($qrCode->encoded_payload, $format, $qrCode->design_config ?? [], $size);
        $filename = $this->filename($qrCode, $format);

        return response()->streamDownload(function () use ($binary) {
            echo $binary;
        }, $filename, [
            'Content-Type' => $format === 'svg' ? 'image/svg+xml' : 'image/png',
        ]);
    }

    private function filename(QrCode $qr, string $format): string
    {
        $base = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $qr->name) ?: 'qr');

        return trim($base, '-').'-'.$qr->qr_type->value.'.'.$format;
    }

    private function resolveFolderId(int $workspaceId, mixed $id, mixed $publicId): ?int
    {
        if (is_numeric($id)) {
            return (int) $id;
        }

        if (is_string($publicId) && $publicId !== '') {
            return \App\Models\Folder::query()->where('workspace_id', $workspaceId)->where('public_id', $publicId)->value('id');
        }

        return null;
    }

    private function resolveCampaignId(int $workspaceId, mixed $id, mixed $publicId): ?int
    {
        if (is_numeric($id)) {
            return (int) $id;
        }

        if (is_string($publicId) && $publicId !== '') {
            return \App\Models\Campaign::query()->where('workspace_id', $workspaceId)->where('public_id', $publicId)->value('id');
        }

        return null;
    }
}
