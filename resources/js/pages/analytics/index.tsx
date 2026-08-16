import { StatCard } from '@/components/stat-card';
import AppLayout from '@/layouts/app-layout';
import { Head, router } from '@inertiajs/react';

interface Props {
    range: string;
    stats: {
        scans_today: number;
        scans_this_month: number;
        dynamic_qr_codes: number;
        timeline: { date: string; scans: number }[];
        top_qr_codes: { public_id: string; name: string; human_scans: number }[];
        devices: { label: string; value: number }[];
        countries: { label: string; value: number }[];
    };
}

export default function AnalyticsIndex({ range, stats }: Props) {
    return (
        <AppLayout breadcrumbs={[{ title: 'Analytics', href: '/analytics' }]}>
            <Head title="Analytics" />
            <div className="flex flex-col gap-6 p-4">
                <div className="flex flex-col justify-between gap-3 sm:flex-row sm:items-end">
                    <div>
                        <h1 className="text-2xl font-semibold tracking-tight">Analytics</h1>
                        <p className="text-muted-foreground mt-1 text-sm">
                            Dynamic QR only. Unique scans are estimates. Location is approximate based on IP.
                        </p>
                    </div>
                    <select
                        className="border-input h-10 rounded-md border bg-background px-3 text-sm"
                        value={range}
                        onChange={(e) => router.get('/analytics', { range: e.target.value }, { preserveState: true })}
                    >
                        <option value="1">Today</option>
                        <option value="7">7 days</option>
                        <option value="30">30 days</option>
                        <option value="90">90 days</option>
                    </select>
                </div>
                <div className="grid gap-4 sm:grid-cols-3">
                    <StatCard label="Scans today" value={stats.scans_today} />
                    <StatCard label="Scans this month" value={stats.scans_this_month} />
                    <StatCard label="Dynamic QR codes" value={stats.dynamic_qr_codes} />
                </div>
                <div className="grid gap-4 lg:grid-cols-2">
                    <section className="rounded-xl border p-5">
                        <h2 className="mb-4 text-sm font-semibold">Top QR codes</h2>
                        <ul className="space-y-3 text-sm">
                            {stats.top_qr_codes.map((item) => (
                                <li key={item.public_id} className="flex justify-between">
                                    <span>{item.name}</span>
                                    <span className="text-muted-foreground">{item.human_scans}</span>
                                </li>
                            ))}
                        </ul>
                    </section>
                    <section className="rounded-xl border p-5">
                        <h2 className="mb-4 text-sm font-semibold">Countries</h2>
                        <ul className="space-y-3 text-sm">
                            {stats.countries.map((item) => (
                                <li key={item.label} className="flex justify-between">
                                    <span>{item.label}</span>
                                    <span className="text-muted-foreground">{item.value}</span>
                                </li>
                            ))}
                        </ul>
                    </section>
                </div>
            </div>
        </AppLayout>
    );
}
