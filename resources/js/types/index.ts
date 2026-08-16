import { LucideIcon } from 'lucide-react';

export interface Auth {
    user: User;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavGroup {
    title: string;
    items: NavItem[];
}

export interface NavItem {
    title: string;
    url: string;
    icon?: LucideIcon | null;
    isActive?: boolean;
}

export interface SharedData {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    workspace: { id: string; name: string } | null;
    canRegister: boolean;
    registration: {
        formStartedAt: number;
        turnstileSiteKey?: string | null;
        honeypotField: string;
    };
    flash: { success?: string };
    locale: string;
    [key: string]: unknown;
}

export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
    [key: string]: unknown;
}

export type QrCodeType = 'static' | 'dynamic';
export type QrStatus = 'active' | 'paused' | 'archived';
export type QrContentType = 'url' | 'text' | 'email' | 'phone' | 'sms' | 'wifi' | 'vcard' | 'location';

export interface QrDesignConfig {
    foreground?: string;
    background?: string;
    error_correction?: 'L' | 'M' | 'Q' | 'H';
    quiet_zone?: number;
    cta_text?: string | null;
    warnings?: string[];
}

export interface QrCode {
    id: string;
    name: string;
    description?: string | null;
    type: QrCodeType;
    content_type?: QrContentType | null;
    status: QrStatus;
    slug?: string | null;
    short_url?: string | null;
    destination_url?: string | null;
    encoded_payload: string;
    static_payload?: Record<string, unknown> | null;
    tracking_enabled: boolean;
    starts_at?: string | null;
    expires_at?: string | null;
    max_scans?: number | null;
    password_protected?: boolean;
    fallback_url?: string | null;
    utm_parameters?: Record<string, string> | null;
    design_config?: QrDesignConfig | null;
    total_scans?: number;
    human_scans?: number;
    bot_scans?: number;
    estimated_unique_scans?: number;
    last_scanned_at?: string | null;
    campaign?: { id: string; name: string } | null;
    folder?: { id: string; name: string } | null;
    created_at?: string;
    updated_at?: string;
}

export interface QrAnalytics {
    supported: boolean;
    message?: string;
    unique_is_estimate?: boolean;
    location_note?: string;
    total_scans?: number;
    human_scans?: number;
    bot_scans?: number;
    estimated_unique_scans?: number;
    scans_today?: number;
    last_scanned_at?: string | null;
    timeline?: { date: string; scans: number }[];
    devices?: { label: string; value: number }[];
    os?: { label: string; value: number }[];
    browsers?: { label: string; value: number }[];
    countries?: { label: string; value: number }[];
    latest?: Array<{
        id: number;
        scanned_at: string;
        country_code?: string | null;
        city?: string | null;
        device_type?: string | null;
        os?: string | null;
        browser?: string | null;
        is_bot: boolean;
        referrer?: string | null;
    }>;
}

export interface Campaign {
    id: number;
    public_id: string;
    name: string;
    description?: string | null;
    status: string;
    qr_codes_count?: number;
    human_scans?: number;
}

export interface Folder {
    id: number;
    public_id: string;
    name: string;
    qr_codes_count?: number;
}

export interface RedirectRule {
    id: number;
    type: string;
    configuration: Record<string, unknown>;
    destination_url?: string | null;
    priority: number;
    is_active: boolean;
}
