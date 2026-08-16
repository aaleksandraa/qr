import { EmptyState } from '@/components/empty-state';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/app-layout';
import { Campaign } from '@/types';
import { Head, Link, useForm } from '@inertiajs/react';
import { FormEvent } from 'react';

interface Props {
    campaigns: { data: Campaign[] };
}

export default function CampaignsIndex({ campaigns }: Props) {
    const form = useForm({ name: '', description: '' });
    const items = campaigns.data ?? [];

    const submit = (e: FormEvent) => {
        e.preventDefault();
        form.post('/campaigns', { onSuccess: () => form.reset() });
    };

    return (
        <AppLayout breadcrumbs={[{ title: 'Campaigns', href: '/campaigns' }]}>
            <Head title="Campaigns" />
            <div className="flex flex-col gap-6 p-4">
                <div>
                    <h1 className="text-2xl font-semibold tracking-tight">Campaigns</h1>
                    <p className="text-muted-foreground mt-1 text-sm">Group Dynamic QR codes that share a destination but need separate tracking.</p>
                </div>
                <form onSubmit={submit} className="grid gap-3 rounded-xl border p-5 md:grid-cols-[1fr_1fr_auto]">
                    <Input placeholder="Campaign name" value={form.data.name} onChange={(e) => form.setData('name', e.target.value)} required />
                    <Input placeholder="Description" value={form.data.description} onChange={(e) => form.setData('description', e.target.value)} />
                    <Button type="submit">Create</Button>
                </form>
                {items.length === 0 ? (
                    <EmptyState title="No campaigns yet." description="Create a campaign, then assign Dynamic QR codes to it." />
                ) : (
                    <div className="overflow-x-auto rounded-xl border">
                        <table className="w-full text-left text-sm">
                            <thead className="bg-muted/50 text-muted-foreground">
                                <tr>
                                    <th className="px-4 py-3 font-medium">Name</th>
                                    <th className="px-4 py-3 font-medium">QR codes</th>
                                    <th className="px-4 py-3 font-medium">Human scans</th>
                                </tr>
                            </thead>
                            <tbody>
                                {items.map((campaign) => (
                                    <tr key={campaign.public_id} className="border-t">
                                        <td className="px-4 py-3">
                                            <Link href={`/campaigns/${campaign.public_id}`} className="font-medium hover:underline">
                                                {campaign.name}
                                            </Link>
                                        </td>
                                        <td className="px-4 py-3">{campaign.qr_codes_count ?? 0}</td>
                                        <td className="px-4 py-3">{campaign.human_scans ?? 0}</td>
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
