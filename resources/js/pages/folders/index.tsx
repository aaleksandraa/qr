import { EmptyState } from '@/components/empty-state';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/app-layout';
import { Folder } from '@/types';
import { Head, Link, router, useForm } from '@inertiajs/react';
import { FormEvent } from 'react';

interface Props {
    folders: Folder[];
}

export default function FoldersIndex({ folders }: Props) {
    const form = useForm({ name: '' });

    const submit = (e: FormEvent) => {
        e.preventDefault();
        form.post('/folders', { onSuccess: () => form.reset() });
    };

    return (
        <AppLayout breadcrumbs={[{ title: 'Folders', href: '/folders' }]}>
            <Head title="Folders" />
            <div className="flex flex-col gap-6 p-4">
                <div>
                    <h1 className="text-2xl font-semibold tracking-tight">Folders</h1>
                    <p className="text-muted-foreground mt-1 text-sm">Simple one-level organization for QR codes.</p>
                </div>
                <form onSubmit={submit} className="flex max-w-lg gap-3">
                    <Input placeholder="Folder name" value={form.data.name} onChange={(e) => form.setData('name', e.target.value)} required />
                    <Button type="submit">Create</Button>
                </form>
                {folders.length === 0 ? (
                    <EmptyState title="No folders yet." description="Create a folder to keep client or campaign QR codes together." />
                ) : (
                    <ul className="divide-y rounded-xl border">
                        {folders.map((folder) => (
                            <li key={folder.public_id} className="flex items-center justify-between px-4 py-3 text-sm">
                                <div>
                                    <div className="font-medium">{folder.name}</div>
                                    <div className="text-muted-foreground">{folder.qr_codes_count ?? 0} QR codes</div>
                                </div>
                                <div className="flex gap-3">
                                    <Link href={`/qr-codes?folder=${folder.public_id}`} className="underline-offset-4 hover:underline">
                                        View
                                    </Link>
                                    <button className="text-destructive" onClick={() => router.delete(`/folders/${folder.public_id}`)}>
                                        Delete
                                    </button>
                                </div>
                            </li>
                        ))}
                    </ul>
                )}
            </div>
        </AppLayout>
    );
}
