import { RegisterForm } from '@/components/register-form';
import { type SharedData } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';

export default function Welcome() {
    const { auth, canRegister, name } = usePage<SharedData>().props;

    return (
        <>
            <Head title={`${name} — Static & Dynamic QR codes`} />
            <div className="bg-background min-h-screen">
                <header className="border-b">
                    <div className="mx-auto flex max-w-6xl items-center justify-between px-6 py-4">
                        <a href="#top" className="text-sm font-semibold tracking-tight">
                            {name}
                        </a>
                        <nav className="flex items-center gap-3 text-sm">
                            <a href="#product" className="text-muted-foreground hover:text-foreground hidden sm:inline">
                                Product
                            </a>
                            <a href="#security" className="text-muted-foreground hover:text-foreground hidden sm:inline">
                                Security
                            </a>
                            {auth.user ? (
                                <Link href="/dashboard" className="rounded-md border px-3 py-1.5">
                                    Dashboard
                                </Link>
                            ) : (
                                <Link href="/login" className="rounded-md border px-3 py-1.5">
                                    Log in
                                </Link>
                            )}
                        </nav>
                    </div>
                </header>

                <main id="top">
                    <section className="mx-auto grid max-w-6xl gap-12 px-6 py-16 lg:grid-cols-[1.1fr_0.9fr] lg:items-start">
                        <div>
                            <p className="text-muted-foreground text-xs font-medium tracking-[0.16em] uppercase">Self-hosted QR platform</p>
                            <h1 className="mt-3 max-w-xl text-4xl font-semibold tracking-tight sm:text-5xl">
                                Create Static and Dynamic QR codes you actually own.
                            </h1>
                            <p className="text-muted-foreground mt-5 max-w-xl text-base leading-7">
                                Static codes embed the final content in the image. Dynamic codes use your short URL, so the destination can change
                                without reprinting. Analytics stay on your server.
                            </p>
                            <ul className="mt-8 space-y-3 text-sm leading-6">
                                <li>SVG and PNG exports ready for print</li>
                                <li>Editable Dynamic destinations with 302 redirects</li>
                                <li>Privacy-first scan analytics — no raw IP by default</li>
                            </ul>
                        </div>

                        <div id="register" className="rounded-2xl border bg-card p-6 shadow-sm">
                            {auth.user ? (
                                <div className="space-y-3">
                                    <h2 className="text-lg font-semibold">You are already signed in</h2>
                                    <Link href="/dashboard" className="bg-primary text-primary-foreground inline-flex h-10 items-center rounded-md px-4 text-sm font-medium">
                                        Go to dashboard
                                    </Link>
                                </div>
                            ) : canRegister ? (
                                <>
                                    <h2 className="text-lg font-semibold">Create your workspace</h2>
                                    <p className="text-muted-foreground mt-1 mb-5 text-sm">No credit card. Start with Static or Dynamic QR codes.</p>
                                    <RegisterForm compact />
                                    <p className="text-muted-foreground mt-4 text-sm">
                                        Already have an account?{' '}
                                        <Link href="/login" className="underline underline-offset-4">
                                            Log in
                                        </Link>
                                    </p>
                                </>
                            ) : (
                                <div className="space-y-3">
                                    <h2 className="text-lg font-semibold">Registration is invite-only</h2>
                                    <p className="text-muted-foreground text-sm">Ask an administrator for access, then sign in.</p>
                                    <Link href="/login" className="bg-primary text-primary-foreground inline-flex h-10 items-center rounded-md px-4 text-sm font-medium">
                                        Log in
                                    </Link>
                                </div>
                            )}
                        </div>
                    </section>

                    <section id="product" className="border-t">
                        <div className="mx-auto grid max-w-6xl gap-4 px-6 py-16 md:grid-cols-2">
                            <article className="rounded-xl border p-6">
                                <p className="text-xs font-medium tracking-wide uppercase">Static QR</p>
                                <h2 className="mt-1 text-xl font-semibold">Direct QR</h2>
                                <p className="text-muted-foreground mt-2 text-sm leading-6">
                                    The QR image contains the final URL, Wi-Fi config, vCard, or other payload. No redirect server. Changing content
                                    generates a new image.
                                </p>
                            </article>
                            <article className="rounded-xl border p-6">
                                <p className="text-xs font-medium tracking-wide uppercase">Dynamic QR</p>
                                <h2 className="mt-1 text-xl font-semibold">Smart QR</h2>
                                <p className="text-muted-foreground mt-2 text-sm leading-6">
                                    The QR image contains your short URL. Change the destination anytime. Scans are counted asynchronously so
                                    redirects stay fast.
                                </p>
                            </article>
                        </div>
                    </section>

                    <section id="security" className="border-t">
                        <div className="mx-auto max-w-6xl px-6 py-16">
                            <h2 className="text-2xl font-semibold tracking-tight">Built to keep signups clean</h2>
                            <p className="text-muted-foreground mt-2 max-w-2xl text-sm leading-6">
                                Public registration is rate-limited, honeypot-protected, and ready for Cloudflare Turnstile. Temporary email
                                providers are rejected before an account is created.
                            </p>
                            <div className="mt-8 grid gap-4 sm:grid-cols-3">
                                <div className="rounded-xl border p-5">
                                    <h3 className="font-medium">Bot traps</h3>
                                    <p className="text-muted-foreground mt-2 text-sm leading-6">Hidden honeypot and minimum form time stop scripted signups.</p>
                                </div>
                                <div className="rounded-xl border p-5">
                                    <h3 className="font-medium">Rate limits</h3>
                                    <p className="text-muted-foreground mt-2 text-sm leading-6">Per-IP limits by minute, hour, and day on the register endpoint.</p>
                                </div>
                                <div className="rounded-xl border p-5">
                                    <h3 className="font-medium">CAPTCHA-ready</h3>
                                    <p className="text-muted-foreground mt-2 text-sm leading-6">Set Turnstile keys in production when you want a visible challenge.</p>
                                </div>
                            </div>
                        </div>
                    </section>
                </main>

                <footer className="border-t">
                    <div className="text-muted-foreground mx-auto flex max-w-6xl justify-between px-6 py-6 text-xs">
                        <span>{name}</span>
                        <span>Self-hosted. Your domain, redirects, and analytics.</span>
                    </div>
                </footer>
            </div>
        </>
    );
}
