import { EmptyState } from '@/components/empty-state';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/app-layout';
import { QrCode } from '@/types';
import { Head, Link, router } from '@inertiajs/react';
import { FormEvent, useState } from 'react';

interface Paginated {
    data: QrCode[];
    meta?: { current_page: number; last_page: number };
    links?: Array<{ url: string | null; label: string; active: boolean }>;
}

interface Props {
    qrCodes: Paginated;
    filters: { type?: string; status?: string; search?: string };
}

export default function QrCodesIndex({ qrCodes, filters }: Props) {
    const [search, setSearch] = useState(filters.search ?? '');
    const items = qrCodes.data ?? [];

    const apply = (e: FormEvent) => {
        e.preventDefault();
        router.get('/qr-codes', { ...filters, search }, { preserveState: true });
    };

    return (
        <AppLayout breadcrumbs={[{ title: 'QR Codes', href: '/qr-codes' }]}>
            <Head title="QR Codes" />
            <div className="flex flex-col gap-6 p-4">
                <div className="flex flex-col justify-between gap-3 sm:flex-row sm:items-end">
                    <div>
                        <h1 className="text-2xl font-semibold tracking-tight">QR Codes</h1>
                        <p className="text-muted-foreground mt-1 text-sm">Static codes embed content. Dynamic codes use a short URL you can edit.</p>
                    </div>
                    <Button asChild>
                        <Link href="/qr-codes/create">Create QR Code</Link>
                    </Button>
                </div>

                <form onSubmit={apply} className="flex flex-col gap-3 md:flex-row">
                    <Input value={search} onChange={(e) => setSearch(e.target.value)} placeholder="Search name, slug, destination" />
                    <select
                        className="border-input h-10 rounded-md border bg-background px-3 text-sm"
                        value={filters.type ?? ''}
                        onChange={(e) => router.get('/qr-codes', { ...filters, type: e.target.value }, { preserveState: true })}
                    >
                        <option value="">All types</option>
                        <option value="static">Static</option>
                        <option value="dynamic">Dynamic</option>
                    </select>
                    <select
                        className="border-input h-10 rounded-md border bg-background px-3 text-sm"
                        value={filters.status ?? ''}
                        onChange={(e) => router.get('/qr-codes', { ...filters, status: e.target.value }, { preserveState: true })}
                    >
                        <option value="">All statuses</option>
                        <option value="active">Active</option>
                        <option value="paused">Paused</option>
                        <option value="archived">Archived</option>
                    </select>
                    <Button type="submit" variant="outline">
                        Search
                    </Button>
                </form>

                {items.length === 0 ? (
                    <EmptyState
                        title="No QR codes match these filters."
                        description="Create a Static QR for permanent content or a Dynamic QR if you need tracking and editable destinations."
                        actionHref="/qr-codes/create"
                        actionLabel="Create QR Code"
                    />
                ) : (
                    <div className="overflow-x-auto rounded-xl border">
                        <table className="w-full min-w-[720px] text-left text-sm">
                            <thead className="bg-muted/50 text-muted-foreground">
                                <tr>
                                    <th className="px-4 py-3 font-medium">Name</th>
                                    <th className="px-4 py-3 font-medium">Type</th>
                                    <th className="px-4 py-3 font-medium">Destination / content</th>
                                    <th className="px-4 py-3 font-medium">Status</th>
                                    <th className="px-4 py-3 font-medium">Scans</th>
                                    <th className="px-4 py-3 font-medium"></th>
                                </tr>
                            </thead>
                            <tbody>
                                {items.map((qr) => (
                                    <tr key={qr.id} className="border-t">
                                        <td className="px-4 py-3 font-medium">
                                            <Link href={`/qr-codes/${qr.id}`} className="hover:underline">
                                                {qr.name}
                                            </Link>
                                            {qr.short_url ? <div className="text-muted-foreground mt-1 text-xs">{qr.short_url}</div> : null}
                                        </td>
                                        <td className="px-4 py-3 capitalize">{qr.type}</td>
                                        <td className="text-muted-foreground max-w-xs truncate px-4 py-3">
                                            {qr.type === 'dynamic' ? qr.destination_url : qr.encoded_payload}
                                        </td>
                                        <td className="px-4 py-3">
                                            <Badge variant={qr.status === 'active' ? 'default' : 'secondary'}>{qr.status}</Badge>
                                        </td>
                                        <td className="px-4 py-3">{qr.type === 'dynamic' ? (qr.human_scans ?? 0) : '—'}</td>
                                        <td className="px-4 py-3 text-right">
                                            <Link href={`/qr-codes/${qr.id}`} className="text-sm underline-offset-4 hover:underline">
                                                View
                                            </Link>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                )}
            </div>
        </AppLayout>
    );
}
