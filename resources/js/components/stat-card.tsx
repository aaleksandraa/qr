interface StatCardProps {
    label: string;
    value: string | number;
    hint?: string;
}

export function StatCard({ label, value, hint }: StatCardProps) {
    return (
        <div className="rounded-xl border bg-card p-5">
            <p className="text-muted-foreground text-sm">{label}</p>
            <p className="mt-2 text-2xl font-semibold tracking-tight">{value}</p>
            {hint ? <p className="text-muted-foreground mt-1 text-xs">{hint}</p> : null}
        </div>
    );
}
