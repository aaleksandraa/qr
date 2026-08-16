import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/app-layout';
import { Head, useForm } from '@inertiajs/react';
import { FormEvent, useMemo, useState } from 'react';

interface Option {
    id: number;
    public_id: string;
    name: string;
}

interface Props {
    campaigns: Option[];
    folders: Option[];
}

const contentTypes = [
    { value: 'url', label: 'URL' },
    { value: 'text', label: 'Text' },
    { value: 'email', label: 'Email' },
    { value: 'phone', label: 'Phone' },
    { value: 'sms', label: 'SMS' },
    { value: 'wifi', label: 'Wi-Fi' },
    { value: 'vcard', label: 'vCard' },
    { value: 'location', label: 'Location' },
];

export default function CreateQr({ campaigns, folders }: Props) {
    const [step, setStep] = useState(1);
    const form = useForm({
        qr_type: '' as '' | 'static' | 'dynamic',
        name: '',
        description: '',
        content_type: 'url',
        destination_url: '',
        custom_slug: '',
        campaign_id: '',
        folder_id: '',
        tracking_enabled: true,
        starts_at: '',
        expires_at: '',
        max_scans: '',
        password: '',
        fallback_url: '',
        payload: {
            url: '',
            text: '',
            email: '',
            subject: '',
            body: '',
            phone: '',
            message: '',
            ssid: '',
            security: 'WPA',
            password: '',
            hidden: false,
            first_name: '',
            last_name: '',
            company: '',
            job_title: '',
            mobile: '',
            website: '',
            street: '',
            city: '',
            postal_code: '',
            country: '',
            note: '',
            latitude: '',
            longitude: '',
            address: '',
            utm_source: '',
            utm_medium: '',
            utm_campaign: '',
        },
        utm: { utm_source: '', utm_medium: '', utm_campaign: '' },
        design: { foreground: '#111827', background: '#FFFFFF', error_correction: 'M', quiet_zone: 4, cta_text: '' },
    });

    const previewPayload = useMemo(() => {
        if (form.data.qr_type === 'dynamic') {
            return form.data.destination_url || 'https://example.com';
        }
        return form.data.payload.url || form.data.payload.text || 'Preview';
    }, [form.data]);

    const submit = (e: FormEvent) => {
        e.preventDefault();
        form.post('/qr-codes');
    };

    return (
        <AppLayout breadcrumbs={[{ title: 'QR Codes', href: '/qr-codes' }, { title: 'Create', href: '/qr-codes/create' }]}>
            <Head title="Create QR Code" />
            <form onSubmit={submit} className="mx-auto flex w-full max-w-4xl flex-col gap-6 p-4">
                <div>
                    <h1 className="text-2xl font-semibold tracking-tight">Create QR Code</h1>
                    <p className="text-muted-foreground mt-1 text-sm">Choose the type first. This decision cannot be reversed for a printed code.</p>
                </div>

                {step === 1 && (
                    <div className="grid gap-4 md:grid-cols-2">
                        <button
                            type="button"
                            onClick={() => {
                                form.setData('qr_type', 'static');
                                setStep(2);
                            }}
                            className="rounded-xl border p-6 text-left transition-colors hover:bg-muted/40"
                        >
                            <p className="text-xs font-medium tracking-wide uppercase">Static QR</p>
                            <h2 className="mt-1 text-xl font-semibold">Direct QR</h2>
                            <p className="text-muted-foreground mt-2 text-sm leading-6">Content is stored directly in the QR image.</p>
                            <ul className="mt-4 space-y-1 text-sm">
                                <li>✓ No redirect server required</li>
                                <li>✓ Permanent</li>
                                <li>✓ Best for permanent data</li>
                                <li className="text-muted-foreground">✕ Destination cannot be edited after printing</li>
                                <li className="text-muted-foreground">✕ No internal scan analytics</li>
                            </ul>
                        </button>
                        <button
                            type="button"
                            onClick={() => {
                                form.setData('qr_type', 'dynamic');
                                setStep(2);
                            }}
                            className="rounded-xl border p-6 text-left transition-colors hover:bg-muted/40"
                        >
                            <p className="text-xs font-medium tracking-wide uppercase">Dynamic QR</p>
                            <h2 className="mt-1 text-xl font-semibold">Smart QR</h2>
                            <p className="text-muted-foreground mt-2 text-sm leading-6">QR points to our redirect server.</p>
                            <ul className="mt-4 space-y-1 text-sm">
                                <li>✓ Change destination anytime</li>
                                <li>✓ Scan analytics</li>
                                <li>✓ Campaign tracking</li>
                                <li>✓ Device/country analytics</li>
                                <li>✓ Advanced redirect rules</li>
                            </ul>
                        </button>
                    </div>
                )}

                {step >= 2 && form.data.qr_type && (
                    <div className="grid gap-6 lg:grid-cols-[1.2fr_0.8fr]">
                        <div className="space-y-5 rounded-xl border p-5">
                            <div className="grid gap-2">
                                <Label htmlFor="name">Name</Label>
                                <Input id="name" value={form.data.name} onChange={(e) => form.setData('name', e.target.value)} required />
                                <InputError message={form.errors.name} />
                            </div>

                            {form.data.qr_type === 'static' && (
                                <>
                                    <div className="grid gap-2">
                                        <Label htmlFor="content_type">Content type</Label>
                                        <select
                                            id="content_type"
                                            className="border-input h-10 rounded-md border bg-background px-3 text-sm"
                                            value={form.data.content_type}
                                            onChange={(e) => form.setData('content_type', e.target.value)}
                                        >
                                            {contentTypes.map((type) => (
                                                <option key={type.value} value={type.value}>
                                                    {type.label}
                                                </option>
                                            ))}
                                        </select>
                                    </div>
                                    <StaticFields form={form} />
                                    <p className="rounded-md border bg-muted/40 p-3 text-sm">
                                        Changing Static content later generates a <strong>new QR image</strong>. Previously printed codes stay unchanged.
                                    </p>
                                </>
                            )}

                            {form.data.qr_type === 'dynamic' && (
                                <>
                                    <div className="grid gap-2">
                                        <Label htmlFor="destination_url">Destination URL</Label>
                                        <Input
                                            id="destination_url"
                                            value={form.data.destination_url}
                                            onChange={(e) => form.setData('destination_url', e.target.value)}
                                            placeholder="https://example.com/page"
                                            required
                                        />
                                        <InputError message={form.errors.destination_url} />
                                    </div>
                                    <div className="grid gap-2">
                                        <Label htmlFor="custom_slug">Custom short URL (optional)</Label>
                                        <Input
                                            id="custom_slug"
                                            value={form.data.custom_slug}
                                            onChange={(e) => form.setData('custom_slug', e.target.value)}
                                            placeholder="academy"
                                        />
                                        <p className="text-muted-foreground text-xs">The slug cannot be changed after creation. Destination can.</p>
                                        <InputError message={form.errors.custom_slug} />
                                    </div>
                                </>
                            )}

                            <details className="rounded-md border p-4">
                                <summary className="cursor-pointer text-sm font-medium">Design</summary>
                                <div className="mt-4 grid gap-3 sm:grid-cols-2">
                                    <div className="grid gap-2">
                                        <Label>Foreground</Label>
                                        <Input
                                            type="color"
                                            value={form.data.design.foreground}
                                            onChange={(e) => form.setData('design', { ...form.data.design, foreground: e.target.value })}
                                        />
                                    </div>
                                    <div className="grid gap-2">
                                        <Label>Background</Label>
                                        <Input
                                            type="color"
                                            value={form.data.design.background}
                                            onChange={(e) => form.setData('design', { ...form.data.design, background: e.target.value })}
                                        />
                                    </div>
                                    <div className="grid gap-2">
                                        <Label>Error correction</Label>
                                        <select
                                            className="border-input h-10 rounded-md border bg-background px-3 text-sm"
                                            value={form.data.design.error_correction}
                                            onChange={(e) => form.setData('design', { ...form.data.design, error_correction: e.target.value })}
                                        >
                                            <option value="L">L — 7%</option>
                                            <option value="M">M — 15% (default)</option>
                                            <option value="Q">Q — 25%</option>
                                            <option value="H">H — 30% (use with logos)</option>
                                        </select>
                                    </div>
                                </div>
                            </details>

                            <details className="rounded-md border p-4">
                                <summary className="cursor-pointer text-sm font-medium">Organization</summary>
                                <div className="mt-4 grid gap-3 sm:grid-cols-2">
                                    <div className="grid gap-2">
                                        <Label>Campaign</Label>
                                        <select
                                            className="border-input h-10 rounded-md border bg-background px-3 text-sm"
                                            value={form.data.campaign_id}
                                            onChange={(e) => form.setData('campaign_id', e.target.value)}
                                        >
                                            <option value="">None</option>
                                            {campaigns.map((item) => (
                                                <option key={item.id} value={item.id}>
                                                    {item.name}
                                                </option>
                                            ))}
                                        </select>
                                    </div>
                                    <div className="grid gap-2">
                                        <Label>Folder</Label>
                                        <select
                                            className="border-input h-10 rounded-md border bg-background px-3 text-sm"
                                            value={form.data.folder_id}
                                            onChange={(e) => form.setData('folder_id', e.target.value)}
                                        >
                                            <option value="">None</option>
                                            {folders.map((item) => (
                                                <option key={item.id} value={item.id}>
                                                    {item.name}
                                                </option>
                                            ))}
                                        </select>
                                    </div>
                                </div>
                            </details>

                            {form.data.qr_type === 'dynamic' && (
                                <details className="rounded-md border p-4">
                                    <summary className="cursor-pointer text-sm font-medium">Advanced Dynamic options</summary>
                                    <div className="mt-4 grid gap-3">
                                        <Input
                                            placeholder="Fallback URL"
                                            value={form.data.fallback_url}
                                            onChange={(e) => form.setData('fallback_url', e.target.value)}
                                        />
                                        <Input
                                            type="number"
                                            placeholder="Scan limit"
                                            value={form.data.max_scans}
                                            onChange={(e) => form.setData('max_scans', e.target.value)}
                                        />
                                        <Input
                                            type="password"
                                            placeholder="Optional PIN / password"
                                            value={form.data.password}
                                            onChange={(e) => form.setData('password', e.target.value)}
                                        />
                                    </div>
                                </details>
                            )}

                            <div className="flex gap-3">
                                <Button type="button" variant="outline" onClick={() => setStep(1)}>
                                    Back
                                </Button>
                                <Button type="submit" disabled={form.processing}>
                                    Create QR
                                </Button>
                            </div>
                        </div>

                        <aside className="h-fit rounded-xl border p-5">
                            <h2 className="text-sm font-semibold">Preview</h2>
                            <p className="text-muted-foreground mt-1 text-xs">Final downloadable QR is generated on the server.</p>
                            <div className="mt-4 overflow-hidden rounded-lg border bg-white p-4">
                                <img
                                    alt="QR preview"
                                    className="mx-auto h-56 w-56"
                                    src={`/qr-codes/preview?payload=${encodeURIComponent(previewPayload)}&design[foreground]=${encodeURIComponent(form.data.design.foreground)}&design[background]=${encodeURIComponent(form.data.design.background)}`}
                                />
                            </div>
                        </aside>
                    </div>
                )}
            </form>
        </AppLayout>
    );
}

function StaticFields({ form }: { form: ReturnType<typeof useForm<any>> }) {
    const type = form.data.content_type;
    const setPayload = (key: string, value: string | boolean) => form.setData('payload', { ...form.data.payload, [key]: value });

    if (type === 'url') {
        return (
            <div className="grid gap-2">
                <Label>URL</Label>
                <Input value={form.data.payload.url} onChange={(e) => setPayload('url', e.target.value)} placeholder="https://example.com/page" />
                <p className="text-muted-foreground text-xs">UTM parameters can be measured by the destination website, not by this platform.</p>
            </div>
        );
    }
    if (type === 'text') {
        return (
            <div className="grid gap-2">
                <Label>Text</Label>
                <Textarea value={form.data.payload.text} onChange={(e) => setPayload('text', e.target.value)} />
                <p className="text-muted-foreground text-xs">Larger payloads make QR codes denser and harder to scan.</p>
            </div>
        );
    }
    if (type === 'email') {
        return (
            <div className="grid gap-3">
                <Input placeholder="Email" value={form.data.payload.email} onChange={(e) => setPayload('email', e.target.value)} />
                <Input placeholder="Subject" value={form.data.payload.subject} onChange={(e) => setPayload('subject', e.target.value)} />
                <Textarea placeholder="Message" value={form.data.payload.body} onChange={(e) => setPayload('body', e.target.value)} />
            </div>
        );
    }
    if (type === 'phone') {
        return <Input placeholder="+38765123456" value={form.data.payload.phone} onChange={(e) => setPayload('phone', e.target.value)} />;
    }
    if (type === 'sms') {
        return (
            <div className="grid gap-3">
                <Input placeholder="Phone" value={form.data.payload.phone} onChange={(e) => setPayload('phone', e.target.value)} />
                <Textarea placeholder="Message" value={form.data.payload.message} onChange={(e) => setPayload('message', e.target.value)} />
            </div>
        );
    }
    if (type === 'wifi') {
        return (
            <div className="grid gap-3">
                <Input placeholder="SSID" value={form.data.payload.ssid} onChange={(e) => setPayload('ssid', e.target.value)} />
                <select
                    className="border-input h-10 rounded-md border bg-background px-3 text-sm"
                    value={form.data.payload.security}
                    onChange={(e) => setPayload('security', e.target.value)}
                >
                    <option value="WPA">WPA / WPA2 / WPA3</option>
                    <option value="WEP">WEP</option>
                    <option value="nopass">None</option>
                </select>
                <Input type="password" placeholder="Password" value={form.data.payload.password} onChange={(e) => setPayload('password', e.target.value)} />
            </div>
        );
    }
    if (type === 'vcard') {
        return (
            <div className="grid gap-3 sm:grid-cols-2">
                <Input placeholder="First name" value={form.data.payload.first_name} onChange={(e) => setPayload('first_name', e.target.value)} />
                <Input placeholder="Last name" value={form.data.payload.last_name} onChange={(e) => setPayload('last_name', e.target.value)} />
                <Input placeholder="Company" value={form.data.payload.company} onChange={(e) => setPayload('company', e.target.value)} />
                <Input placeholder="Job title" value={form.data.payload.job_title} onChange={(e) => setPayload('job_title', e.target.value)} />
                <Input placeholder="Phone" value={form.data.payload.phone} onChange={(e) => setPayload('phone', e.target.value)} />
                <Input placeholder="Email" value={form.data.payload.email} onChange={(e) => setPayload('email', e.target.value)} />
            </div>
        );
    }
    return (
        <div className="grid gap-3">
            <Input placeholder="Latitude" value={form.data.payload.latitude} onChange={(e) => setPayload('latitude', e.target.value)} />
            <Input placeholder="Longitude" value={form.data.payload.longitude} onChange={(e) => setPayload('longitude', e.target.value)} />
            <Input placeholder="Or address" value={form.data.payload.address} onChange={(e) => setPayload('address', e.target.value)} />
        </div>
    );
}
