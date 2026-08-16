<?php

namespace App\Http\Controllers;

use App\Exceptions\Redirect\QrRedirectException;
use App\Models\QrCode;
use App\Services\Redirect\QrRedirectResolver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class RedirectController extends Controller
{
    public function __construct(private readonly QrRedirectResolver $resolver) {}

    public function show(Request $request, string $slug): RedirectResponse|View
    {
        try {
            $result = $this->resolver->resolve($slug, $request);

            return redirect()->away($result['url'], $result['status'])
                ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
                ->header('Pragma', 'no-cache');
        } catch (QrRedirectException $e) {
            if ($e->reason === 'password') {
                return view('redirect.password', ['slug' => $slug]);
            }

            if ($e->fallbackUrl && in_array($e->reason, ['paused', 'expired', 'unavailable', 'limit'], true) === false) {
                return redirect()->away($e->fallbackUrl, (int) config('qr.redirect_status', 302))
                    ->header('Cache-Control', 'no-store');
            }

            return view('redirect.unavailable', [
                'reason' => $e->reason,
            ]);
        }
    }

    public function unlock(Request $request, string $slug): RedirectResponse|View
    {
        $request->validate([
            'password' => ['required', 'string', 'max:64'],
        ]);

        $qr = QrCode::query()->where('slug', $slug)->first();

        if (! $qr || ! $qr->isPasswordProtected() || ! Hash::check($request->string('password'), $qr->password_hash)) {
            return view('redirect.password', [
                'slug' => $slug,
                'error' => 'Incorrect PIN or password.',
            ]);
        }

        $request->session()->put('qr_unlocked_'.$qr->id, true);

        return redirect()->to($request->fullUrlWithoutQuery(['password']));
    }
}
