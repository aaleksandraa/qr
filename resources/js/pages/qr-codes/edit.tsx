import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/app-layout';
import { QrCode } from '@/types';
import { Head, useForm } from '@inertiajs/react';
import { FormEvent } from 'react';

interface Option {
    id: number;
    public_id: string;
    name: string;
}

interface Props {
    qrCode: QrCode;
    campaigns: Option[];
    folders: Option[];
}

export default function EditQr({ qrCode, campaigns, folders }: Props) {
    const form = useForm({
        name: qrCode.name,
        description: qrCode.description ?? '',
        destination_url: qrCode.destination_url ?? '',
        campaign_id: '',
        folder_id: '',
    });

    const submit = (e: FormEvent) => {
        e.preventDefault();
        form.put(`/qr-codes/${qrCode.id}`);
    };

    return (
        <AppLayout breadcrumbs={[{ title: qrCode.name, href: `/qr-codes/${qrCode.id}` }, { title: 'Edit', href: `/qr-codes/${qrCode.id}/edit` }]}>
            <Head title={`Edit ${qrCode.name}`} />
            <form onSubmit={submit} className="mx-auto flex w-full max-w-xl flex-col gap-5 p-4">
                <h1 className="text-2xl font-semibold tracking-tight">Edit QR</h1>
                {qrCode.type === 'static' ? (
                    <p className="rounded-md border bg-muted/40 p-3 text-sm">
                        A new QR image will be generated. Previously downloaded/printed QR codes will remain unchanged.
                    </p>
                ) : (
                    <p className="text-muted-foreground text-sm">The short URL stays the same. Only the destination and metadata change.</p>
                )}
                <div className="grid gap-2">
                    <Label htmlFor="name">Name</Label>
                    <Input id="name" value={form.data.name} onChange={(e) => form.setData('name', e.target.value)} />
                    <InputError message={form.errors.name} />
                </div>
                <div className="grid gap-2">
                    <Label htmlFor="description">Description</Label>
                    <Textarea id="description" value={form.data.description} onChange={(e) => form.setData('description', e.target.value)} />
                </div>
                {qrCode.type === 'dynamic' ? (
                    <div className="grid gap-2">
                        <Label htmlFor="destination_url">Destination</Label>
                        <Input
                            id="destination_url"
                            value={form.data.destination_url}
                            onChange={(e) => form.setData('destination_url', e.target.value)}
                        />
                    </div>
                ) : null}
                <Button type="submit" disabled={form.processing}>
                    Save
                </Button>
            </form>
        </AppLayout>
    );
}
