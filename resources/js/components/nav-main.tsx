import { Link } from '@inertiajs/react';
import {
    SidebarGroup,
    SidebarGroupLabel,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    SidebarMenuSub,
    SidebarMenuSubItem,
    SidebarMenuSubButton,
} from '@/components/ui/sidebar';
import { ChevronRight } from 'lucide-react';
import { useCurrentUrl } from '@/hooks/use-current-url';
import type { NavItem } from '@/types';
import { useState } from 'react';

function NavGroup({ item }: { item: NavItem }) {
    const { isCurrentUrl } = useCurrentUrl();
    const hasActiveChild = !!item.items?.some((s) => isCurrentUrl(s.href));
    const [open, setOpen] = useState(hasActiveChild);

    return (
        <SidebarMenuItem>
            <SidebarMenuButton
                isActive={hasActiveChild}
                tooltip={{ children: item.title }}
                aria-expanded={open}
                onClick={() => setOpen((v) => !v)}
            >
                {item.icon && <item.icon />}
                <span>{item.title}</span>
                <ChevronRight
                    className={`ml-auto transition-transform duration-200 ${open ? 'rotate-90' : ''}`}
                />
            </SidebarMenuButton>

            {open && (
                <SidebarMenuSub>
                    {item.items?.map((sub) => (
                        <SidebarMenuSubItem key={sub.title}>
                            <SidebarMenuSubButton asChild isActive={isCurrentUrl(sub.href)}>
                                <Link href={sub.href} prefetch>
                                    <span>{sub.title}</span>
                                </Link>
                            </SidebarMenuSubButton>
                        </SidebarMenuSubItem>
                    ))}
                </SidebarMenuSub>
            )}
        </SidebarMenuItem>
    );
}

export function NavMain({ items = [] }: { items: NavItem[] }) {
    const { isCurrentUrl } = useCurrentUrl();

    return (
        <SidebarGroup className="px-2 py-0">
            <SidebarGroupLabel>Platform</SidebarGroupLabel>
            <SidebarMenu>
                {items.map((item) =>
                    item.items?.length ? (
                        <NavGroup key={item.title} item={item} />
                    ) : (
                        <SidebarMenuItem key={item.title}>
                            <SidebarMenuButton
                                asChild
                                isActive={isCurrentUrl(item.href)}
                                tooltip={{ children: item.title }}
                            >
                                <Link href={item.href} prefetch>
                                    {item.icon && <item.icon />}
                                    <span>{item.title}</span>
                                </Link>
                            </SidebarMenuButton>
                        </SidebarMenuItem>
                    ),
                )}
            </SidebarMenu>
        </SidebarGroup>
    );
}
