import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/react';
import { Folder, LayoutDashboard, Plus, QrCode, Settings2, BarChart3, Megaphone } from 'lucide-react';
import AppLogo from './app-logo';

const mainNavItems: NavItem[] = [
    { title: 'Dashboard', url: '/dashboard', icon: LayoutDashboard },
    { title: 'QR Codes', url: '/qr-codes', icon: QrCode },
    { title: 'Create QR', url: '/qr-codes/create', icon: Plus },
    { title: 'Campaigns', url: '/campaigns', icon: Megaphone },
    { title: 'Folders', url: '/folders', icon: Folder },
    { title: 'Analytics', url: '/analytics', icon: BarChart3 },
    { title: 'API tokens', url: '/settings/api-tokens', icon: Settings2 },
];

export function AppSidebar() {
    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href="/dashboard" prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain items={mainNavItems} />
            </SidebarContent>

            <SidebarFooter>
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
