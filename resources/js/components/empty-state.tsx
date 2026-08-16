import { Link } from '@inertiajs/react';
import { ReactNode } from 'react';

import { Button } from '@/components/ui/button';

interface EmptyStateProps {
    title: string;
    description: string;
    actionHref?: string;
    actionLabel?: string;
    children?: ReactNode;
}

export function EmptyState({ title, description, actionHref, actionLabel, children }: EmptyStateProps) {
    return (
        <div className="flex flex-col items-start gap-3 rounded-xl border border-dashed p-8">
            <h3 className="text-base font-semibold">{title}</h3>
            <p className="text-muted-foreground max-w-xl text-sm leading-6">{description}</p>
            {actionHref && actionLabel ? (
                <Button asChild>
                    <Link href={actionHref}>{actionLabel}</Link>
                </Button>
            ) : null}
            {children}
        </div>
    );
}
