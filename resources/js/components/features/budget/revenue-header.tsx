import { UserSettings } from '@/types/budget';

interface RevenueHeaderProps {
    total: number;
    settings: UserSettings;
}

export function RevenueHeader({ total, settings }: RevenueHeaderProps) {
    const today = new Date();
    const formattedDate = today.toLocaleDateString('fr-FR', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });

    const formattedTotal = new Intl.NumberFormat('fr-FR', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
    }).format(total);

    return (
        <div className="mb-6 space-y-2">
            <p className="text-sm text-muted-foreground capitalize">{formattedDate}</p>
            <div className="flex items-baseline gap-2">
                <h2 className="text-3xl sm:text-4xl font-bold tracking-tight">
                    {formattedTotal}
                </h2>
                <span className="text-xl sm:text-2xl text-muted-foreground font-medium">
                    {settings.currency_symbol}
                </span>
            </div>
            <p className="text-sm text-muted-foreground">Total des revenus ce mois</p>
        </div>
    );
}
