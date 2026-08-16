import { StatCard } from '@/components/stat-card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/app-layout';
import { QrAnalytics, QrCode, RedirectRule } from '@/types';
import { Head, Link, router, useForm } from '@inertiajs/react';
import { FormEvent } from 'react';

interface Props {
    qrCode: QrCode;
    history: Array<{ old_url?: string | null; new_url: string; changed_by?: string | null; created_at?: string }>;
    rules: RedirectRule[];
    analytics: QrAnalytics;
}

export default function ShowQr({ qrCode, history, rules, analytics }: Props) {
    const destinationForm = useForm({ destination_url: qrCode.destination_url ?? '' });
    const ruleForm = useForm({
        type: 'country',
        priority: 10,
        destination_url: '',
        configuration: { destinations: { BA: '' } },
    });

    const confirm = (url: string, message: string) => {
        if (window.confirm(message)) {
            router.post(url);
        }
    };

    const updateDestination = (e: FormEvent) => {
        e.preventDefault();
        destinationForm.put(`/qr-codes/${qrCode.id}`);
    };

    return (
        <AppLayout breadcrumbs={[{ title: 'QR Codes', href: '/qr-codes' }, { title: qrCode.name, href: `/qr-codes/${qrCode.id}` }]}>
            <Head title={qrCode.name} />
            <div className="flex flex-col gap-6 p-4">
                <div className="flex flex-col justify-between gap-3 lg:flex-row lg:items-start">
                    <div>
                        <div className="flex items-center gap-2">
                            <h1 className="text-2xl font-semibold tracking-tight">{qrCode.name}</h1>
                            <Badge>{qrCode.type}</Badge>
                            <Badge variant="secondary">{qrCode.status}</Badge>
                        </div>
                        {qrCode.short_url ? <p className="text-muted-foreground mt-2 text-sm">{qrCode.short_url}</p> : null}
                    </div>
                    <div className="flex flex-wrap gap-2">
                        <Button asChild variant="outline">
                            <a href={`/qr-codes/${qrCode.id}/download?format=svg`}>Download SVG</a>
                        </Button>
                        <Button asChild variant="outline">
                            <a href={`/qr-codes/${qrCode.id}/download?format=png&size=1024`}>Download PNG</a>
                        </Button>
                        <Button asChild variant="outline">
                            <Link href={`/qr-codes/${qrCode.id}/edit`}>Edit</Link>
                        </Button>
                    </div>
                </div>

                <div className="grid gap-6 lg:grid-cols-[280px_1fr]">
                    <div className="rounded-xl border bg-white p-4 dark:bg-card">
                        <img
                            alt="QR preview"
                            className="mx-auto h-56 w-56"
                            src={`/qr-codes/preview?payload=${encodeURIComponent(qrCode.encoded_payload)}`}
                        />
                    </div>
                    <div className="space-y-4">
                        {qrCode.type === 'static' ? (
                            <div className="rounded-xl border p-5">
                                <h2 className="font-semibold">This is a Static QR code.</h2>
                                <p className="text-muted-foreground mt-2 text-sm leading-6">
                                    The content is embedded directly in the QR image. Changing the content requires generating a new QR code.
                                    Previously downloaded or printed QR codes will remain unchanged.
                                </p>
                                <p className="mt-3 font-mono text-sm break-all">{qrCode.encoded_payload}</p>
                                {qrCode.content_type === 'url' ? (
                                    <p className="text-muted-foreground mt-3 text-xs">
                                        Traffic may be measured by analytics installed on the destination website.
                                    </p>
                                ) : null}
                            </div>
                        ) : (
                            <form onSubmit={updateDestination} className="space-y-3 rounded-xl border p-5">
                                <h2 className="font-semibold">Current destination</h2>
                                <p className="text-muted-foreground text-sm">The printed QR stays the same when you change this URL.</p>
                                <Input
                                    value={destinationForm.data.destination_url}
                                    onChange={(e) => destinationForm.setData('destination_url', e.target.value)}
                                />
                                <Button type="submit" disabled={destinationForm.processing}>
                                    Update destination
                                </Button>
                            </form>
                        )}

                        <div className="flex flex-wrap gap-2">
                            {qrCode.status === 'active' ? (
                                <Button variant="outline" onClick={() => confirm(`/qr-codes/${qrCode.id}/pause`, 'Pause this QR redirect?')}>
                                    Pause
                                </Button>
                            ) : (
                                <Button variant="outline" onClick={() => router.post(`/qr-codes/${qrCode.id}/activate`)}>
                                    Activate
                                </Button>
                            )}
                            <Button variant="outline" onClick={() => router.post(`/qr-codes/${qrCode.id}/duplicate`)}>
                                Duplicate
                            </Button>
                            {qrCode.type === 'static' ? (
                                <Button
                                    variant="outline"
                                    onClick={() =>
                                        confirm(
                                            `/qr-codes/${qrCode.id}/convert-dynamic`,
                                            'A new QR image will be generated. Previously printed Static QR codes will not change.',
                                        )
                                    }
                                >
                                    Convert to Dynamic
                                </Button>
                            ) : (
                                <Button
                                    variant="outline"
                                    onClick={() =>
                                        confirm(
                                            `/qr-codes/${qrCode.id}/convert-static`,
                                            'A new QR image will be generated. Previously printed Dynamic QR codes will not change.',
                                        )
                                    }
                                >
                                    Convert to Static
                                </Button>
                            )}
                            <Button
                                variant="destructive"
                                onClick={() => confirm(`/qr-codes/${qrCode.id}/archive`, 'Archive this QR? Printed Dynamic codes will stop working.')}
                            >
                                Archive
                            </Button>
                        </div>
                    </div>
                </div>

                {qrCode.type === 'dynamic' && analytics.supported ? (
                    <section className="space-y-4">
                        <h2 className="text-lg font-semibold">Analytics</h2>
                        <p className="text-muted-foreground text-xs">Unique scans are estimates. Location is approximate based on IP.</p>
                        <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                            <StatCard label="Total scans" value={analytics.total_scans ?? 0} />
                            <StatCard label="Human scans" value={analytics.human_scans ?? 0} />
                            <StatCard label="Estimated unique" value={analytics.estimated_unique_scans ?? 0} />
                            <StatCard label="Bot requests" value={analytics.bot_scans ?? 0} />
                        </div>
                    </section>
                ) : null}

                {qrCode.type === 'dynamic' ? (
                    <section className="grid gap-4 lg:grid-cols-2">
                        <div className="rounded-xl border p-5">
                            <h2 className="mb-3 font-semibold">Destination history</h2>
                            {history.length === 0 ? (
                                <p className="text-muted-foreground text-sm">No destination changes yet.</p>
                            ) : (
                                <ul className="space-y-2 text-sm">
                                    {history.map((row, index) => (
                                        <li key={index} className="border-b pb-2 last:border-0">
                                            <div className="break-all">{row.old_url} → {row.new_url}</div>
                                            <div className="text-muted-foreground text-xs">{row.changed_by}</div>
                                        </li>
                                    ))}
                                </ul>
                            )}
                        </div>
                        <div className="rounded-xl border p-5">
                            <h2 className="mb-3 font-semibold">Redirect rules</h2>
                            <ul className="mb-4 space-y-2 text-sm">
                                {rules.map((rule) => (
                                    <li key={rule.id} className="flex items-center justify-between">
                                        <span>
                                            {rule.type} · priority {rule.priority}
                                        </span>
                                        <button
                                            className="text-destructive text-xs"
                                            onClick={() => router.delete(`/qr-codes/${qrCode.id}/rules/${rule.id}`)}
                                        >
                                            Remove
                                        </button>
                                    </li>
                                ))}
                            </ul>
                            <form
                                className="grid gap-2"
                                onSubmit={(e) => {
                                    e.preventDefault();
                                    ruleForm.post(`/qr-codes/${qrCode.id}/rules`);
                                }}
                            >
                                <Input
                                    placeholder="BA destination URL"
                                    value={(ruleForm.data.configuration.destinations as { BA?: string }).BA ?? ''}
                                    onChange={(e) =>
                                        ruleForm.setData('configuration', { destinations: { BA: e.target.value } })
                                    }
                                />
                                <Button type="submit" variant="outline" size="sm">
                                    Add country rule for BA
                                </Button>
                            </form>
                        </div>
                    </section>
                ) : null}
            </div>
        </AppLayout>
    );
}
