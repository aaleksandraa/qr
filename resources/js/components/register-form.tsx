import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { type SharedData } from '@/types';
import { useForm, usePage } from '@inertiajs/react';
import { LoaderCircle } from 'lucide-react';
import { FormEventHandler, useEffect, useRef } from 'react';

interface Props {
    compact?: boolean;
}

export function RegisterForm({ compact = false }: Props) {
    const { registration } = usePage<SharedData>().props;
    const startedAt = useRef(registration.formStartedAt);
    const turnstileRef = useRef<HTMLDivElement>(null);

    const { data, setData, post, processing, errors, reset } = useForm({
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
        website: '',
        form_started_at: startedAt.current,
        'cf-turnstile-response': '',
    });

    useEffect(() => {
        const key = registration.turnstileSiteKey;
        if (!key || !turnstileRef.current) {
            return;
        }

        const scriptId = 'cf-turnstile-script';
        const render = () => {
            const turnstile = (window as Window & { turnstile?: { render: (el: HTMLElement, opts: Record<string, unknown>) => void } }).turnstile;
            if (!turnstile || !turnstileRef.current) {
                return;
            }
            turnstileRef.current.innerHTML = '';
            turnstile.render(turnstileRef.current, {
                sitekey: key,
                callback: (token: string) => setData('cf-turnstile-response', token),
            });
        };

        if (!document.getElementById(scriptId)) {
            const script = document.createElement('script');
            script.id = scriptId;
            script.src = 'https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit';
            script.async = true;
            script.onload = render;
            document.body.appendChild(script);
        } else {
            render();
        }
    }, [registration.turnstileSiteKey, setData]);

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('register'), {
            onFinish: () => reset('password', 'password_confirmation'),
        });
    };

    return (
        <form className="relative grid gap-4" onSubmit={submit} noValidate>
            <div className="grid gap-2">
                <Label htmlFor="register-name">Name</Label>
                <Input
                    id="register-name"
                    value={data.name}
                    onChange={(e) => setData('name', e.target.value)}
                    autoComplete="name"
                    required
                    placeholder="Your name"
                />
                <InputError message={errors.name} />
            </div>
            <div className="grid gap-2">
                <Label htmlFor="register-email">Work email</Label>
                <Input
                    id="register-email"
                    type="email"
                    value={data.email}
                    onChange={(e) => setData('email', e.target.value)}
                    autoComplete="email"
                    required
                    placeholder="you@company.com"
                />
                <InputError message={errors.email} />
            </div>
            <div className={compact ? 'grid gap-4 sm:grid-cols-2' : 'grid gap-4'}>
                <div className="grid gap-2">
                    <Label htmlFor="register-password">Password</Label>
                    <Input
                        id="register-password"
                        type="password"
                        value={data.password}
                        onChange={(e) => setData('password', e.target.value)}
                        autoComplete="new-password"
                        required
                    />
                    <InputError message={errors.password} />
                </div>
                <div className="grid gap-2">
                    <Label htmlFor="register-password-confirmation">Confirm password</Label>
                    <Input
                        id="register-password-confirmation"
                        type="password"
                        value={data.password_confirmation}
                        onChange={(e) => setData('password_confirmation', e.target.value)}
                        autoComplete="new-password"
                        required
                    />
                    <InputError message={errors.password_confirmation} />
                </div>
            </div>

            <div aria-hidden="true" className="absolute -left-[10000px] h-px w-px overflow-hidden">
                <label htmlFor="register-website">Website</label>
                <input
                    id="register-website"
                    name="website"
                    type="text"
                    tabIndex={-1}
                    autoComplete="off"
                    value={data.website}
                    onChange={(e) => setData('website', e.target.value)}
                />
            </div>

            {registration.turnstileSiteKey ? <div ref={turnstileRef} className="min-h-16" /> : null}

            <Button type="submit" disabled={processing} className="w-full">
                {processing ? <LoaderCircle className="h-4 w-4 animate-spin" /> : null}
                Create free account
            </Button>
            <p className="text-muted-foreground text-xs leading-5">
                Protected against automated signups. Disposable email addresses are blocked.
            </p>
        </form>
    );
}
