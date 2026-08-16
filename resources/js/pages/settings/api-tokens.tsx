import HeadingSmall from '@/components/heading-small';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/app-layout';
import SettingsLayout from '@/layouts/settings/layout';
import { Head, router, useForm } from '@inertiajs/react';
import { FormEvent } from 'react';

interface Token {
    id: number;
    name: string;
    abilities: string[];
    last_used_at?: string | null;
    created_at?: string;
}

interface Props {
    tokens: Token[];
    plainTextToken?: string;
}

export default function ApiTokens({ tokens, plainTextToken }: Props) {
    const form = useForm({ name: 'Integration' });

    const submit = (e: FormEvent) => {
        e.preventDefault();
        form.post('/settings/api-tokens');
    };

    return (
        <AppLayout breadcrumbs={[{ title: 'API tokens', href: '/settings/api-tokens' }]}>
            <Head title="API tokens" />
            <SettingsLayout>
                <div className="space-y-6">
                    <HeadingSmall title="API tokens" description="Create Sanctum tokens with scoped abilities for external integrations." />
                    {plainTextToken ? (
                        <p className="rounded-md border bg-muted/40 p-3 font-mono text-sm break-all">
                            Copy this token now. It will not be shown again: {plainTextToken}
                        </p>
                    ) : null}
                    <form onSubmit={submit} className="flex gap-3">
                        <Input value={form.data.name} onChange={(e) => form.setData('name', e.target.value)} />
                        <Button type="submit">Create token</Button>
                    </form>
                    <ul className="divide-y rounded-xl border">
                        {tokens.map((token) => (
                            <li key={token.id} className="flex items-center justify-between px-4 py-3 text-sm">
                                <div>
                                    <div className="font-medium">{token.name}</div>
                                    <div className="text-muted-foreground">{token.abilities.join(', ')}</div>
                                </div>
                                <button className="text-destructive" onClick={() => router.delete(`/settings/api-tokens/${token.id}`)}>
                                    Revoke
                                </button>
                            </li>
                        ))}
                    </ul>
                </div>
            </SettingsLayout>
        </AppLayout>
    );
}
