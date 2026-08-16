import { StatCard } from '@/components/stat-card';
import AppLayout from '@/layouts/app-layout';
import { Campaign } from '@/types';
import { Head, Link } from '@inertiajs/react';

interface Props {
    campaign: Campaign;
    analytics: {
        qr_count: number;
        total_scans: number;
        human_scans: number;
        estimated_unique: number;
        ranking: Array<{ public_id: string; name: string; human_scans: number }>;
    };
}

export default function CampaignShow({ campaign, analytics }: Props) {
    return (
        <AppLayout breadcrumbs={[{ title: 'Campaigns', href: '/campaigns' }, { title: campaign.name, href: `/campaigns/${campaign.public_id}` }]}>
            <Head title={campaign.name} />
            <div className="flex flex-col gap-6 p-4">
                <div>
                    <h1 className="text-2xl font-semibold tracking-tight">{campaign.name}</h1>
                    <p className="text-muted-foreground mt-1 text-sm">{campaign.description}</p>
                </div>
                <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                    <StatCard label="QR codes" value={analytics.qr_count} />
                    <StatCard label="Total scans" value={analytics.total_scans} />
                    <StatCard label="Human scans" value={analytics.human_scans} />
                    <StatCard label="Estimated unique" value={analytics.estimated_unique} hint="Estimate, not a person count" />
                </div>
                <section className="rounded-xl border p-5">
                    <h2 className="mb-4 text-sm font-semibold">QR ranking</h2>
                    <ul className="space-y-3">
                        {analytics.ranking.map((item) => (
                            <li key={item.public_id} className="flex items-center justify-between text-sm">
                                <Link href={`/qr-codes/${item.public_id}`} className="hover:underline">
                                    {item.name}
                                </Link>
                                <span className="text-muted-foreground">{item.human_scans}</span>
                            </li>
                        ))}
                    </ul>
                </section>
            </div>
        </AppLayout>
    );
}
