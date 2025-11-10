import { Coins } from 'lucide-react';
import { BudgetSummary, UserSettings } from '@/types/budget';
import { formatCurrency } from '@/lib/currency';

interface BudgetDisplayProps {
    budgetSummary: BudgetSummary;
    userSettings: UserSettings;
    compact?: boolean;
}

export function BudgetDisplay({ budgetSummary, userSettings, compact = false }: BudgetDisplayProps) {
    if (compact) {
        // Compact version for mobile
        return (
            <div className="flex items-center gap-1.5 px-2 py-1 rounded-md bg-primary/10 border border-primary/20">
                <Coins className="size-3.5 text-primary flex-shrink-0" />
                <span className="text-xs font-bold text-primary whitespace-nowrap">
                    {formatCurrency(budgetSummary.today_budget, userSettings)}
                </span>
            </div>
        );
    }

    // Full version for desktop
    return (
        <div className="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-primary/10 border border-primary/20">
            <Coins className="size-4 text-primary" />
            <div className="flex flex-col">
                <span className="text-xs text-muted-foreground">Aujourd'hui</span>
                <span className="text-sm font-bold text-primary">
                    {formatCurrency(budgetSummary.today_budget, userSettings)}
                </span>
            </div>
        </div>
    );
}
