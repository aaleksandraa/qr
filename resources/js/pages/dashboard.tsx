import { EmptyState } from '@/components/empty-state';
import { StatCard } from '@/components/stat-card';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/react';

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Dashboard', href: '/dashboard' }];

interface DashboardProps {
    stats: {
        total_qr_codes: number;
        static_qr_codes: number;
        dynamic_qr_codes: number;
        scans_today: number;
        scans_this_month: number;
        active_campaigns: number;
        timeline: { date: string; scans: number }[];
        top_qr_codes: { public_id: string; name: string; human_scans: number }[];
        top_campaigns: { public_id: string; name: string; human_scans?: number }[];
        devices: { label: string; value: number }[];
        countries: { label: string; value: number }[];
    };
}

export default function Dashboard({ stats }: DashboardProps) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Dashboard" />
            <div className="flex flex-col gap-6 p-4">
                <div className="flex flex-col justify-between gap-3 sm:flex-row sm:items-end">
                    <div>
                        <h1 className="text-2xl font-semibold tracking-tight">Dashboard</h1>
                        <p className="text-muted-foreground mt-1 text-sm">Scan analytics apply only to Dynamic QR codes.</p>
                    </div>
                    <Link
                        href="/qr-codes/create"
                        className="bg-primary text-primary-foreground inline-flex h-10 items-center justify-center rounded-md px-4 text-sm font-medium"
                    >
                        Create QR Code
                    </Link>
                </div>

                <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                    <StatCard label="Total QR codes" value={stats.total_qr_codes} />
                    <StatCard label="Static QR" value={stats.static_qr_codes} hint="Direct payload, no scan analytics" />
                    <StatCard label="Dynamic QR" value={stats.dynamic_qr_codes} hint="Editable destination + analytics" />
                    <StatCard label="Scans today" value={stats.scans_today} hint="Human scans" />
                    <StatCard label="Scans this month" value={stats.scans_this_month} />
                    <StatCard label="Active campaigns" value={stats.active_campaigns} />
                </div>

                {stats.total_qr_codes === 0 ? (
                    <EmptyState
                        title="You don't have any QR codes yet."
                        description="Create a Static QR for permanent content or a Dynamic QR if you need tracking and editable destinations."
                        actionHref="/qr-codes/create"
                        actionLabel="Create QR Code"
                    />
                ) : (
                    <div className="grid gap-4 lg:grid-cols-2">
                        <section className="rounded-xl border p-5">
                            <h2 className="mb-4 text-sm font-semibold">Top Dynamic QR codes</h2>
                            {stats.top_qr_codes.length === 0 ? (
                                <p className="text-muted-foreground text-sm">No Dynamic scans yet.</p>
                            ) : (
                                <ul className="space-y-3">
                                    {stats.top_qr_codes.map((item) => (
                                        <li key={item.public_id} className="flex items-center justify-between text-sm">
                                            <Link href={`/qr-codes/${item.public_id}`} className="hover:underline">
                                                {item.name}
                                            </Link>
                                            <span className="text-muted-foreground">{item.human_scans}</span>
                                        </li>
                                    ))}
                                </ul>
                            )}
                        </section>
                        <section className="rounded-xl border p-5">
                            <h2 className="mb-4 text-sm font-semibold">Devices</h2>
                            {stats.devices.length === 0 ? (
                                <p className="text-muted-foreground text-sm">No device data yet.</p>
                            ) : (
                                <ul className="space-y-3">
                                    {stats.devices.map((item) => (
                                        <li key={item.label} className="flex items-center justify-between text-sm">
                                            <span className="capitalize">{item.label}</span>
                                            <span className="text-muted-foreground">{item.value}</span>
                                        </li>
                                    ))}
                                </ul>
                            )}
                        </section>
                    </div>
                )}
            </div>
        </AppLayout>
    );
}
