import { IncomeEntry, UserSettings } from '@/types/budget';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Edit, MoreVertical, Trash2, RepeatIcon } from 'lucide-react';

interface IncomeEntryCardProps {
    incomeEntry: IncomeEntry;
    settings: UserSettings;
    onEdit: (incomeEntry: IncomeEntry) => void;
    onDelete: (incomeEntry: IncomeEntry) => void;
}

export function IncomeEntryCard({
    incomeEntry,
    settings,
    onEdit,
    onDelete,
}: IncomeEntryCardProps) {
    const formattedAmount = new Intl.NumberFormat('fr-FR', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
    }).format(incomeEntry.amount);

    const formattedDate = new Date(incomeEntry.income_date).toLocaleDateString('fr-FR', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    });

    return (
        <div className="flex items-center gap-3 rounded-xl border bg-card p-4 active:bg-muted/50 transition-colors">
            <div className="flex-1 min-w-0 space-y-1">
                <div className="flex items-center gap-2">
                    <h3 className="font-semibold text-base truncate">
                        {incomeEntry.income?.name || 'Sans revenu'}
                    </h3>
                    {incomeEntry.is_recurring && (
                        <RepeatIcon className="size-4 text-muted-foreground shrink-0" />
                    )}
                </div>
                {incomeEntry.description && (
                    <p className="text-sm text-muted-foreground line-clamp-2">
                        {incomeEntry.description}
                    </p>
                )}
                <p className="text-xs text-muted-foreground">{formattedDate}</p>
            </div>
            <div className="flex items-center gap-3 shrink-0">
                <div className="text-right">
                    <p className="font-semibold text-lg">
                        {formattedAmount}
                    </p>
                    <p className="text-xs text-muted-foreground">
                        {settings.currency_symbol}
                    </p>
                </div>
                <DropdownMenu>
                    <DropdownMenuTrigger asChild>
                        <Button
                            variant="ghost"
                            size="icon"
                            className="size-9"
                        >
                            <MoreVertical className="size-5" />
                            <span className="sr-only">Actions</span>
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end">
                        <DropdownMenuItem onClick={() => onEdit(incomeEntry)}>
                            <Edit className="mr-2 size-4" />
                            Modifier
                        </DropdownMenuItem>
                        <DropdownMenuItem
                            onClick={() => onDelete(incomeEntry)}
                            className="text-destructive focus:text-destructive"
                        >
                            <Trash2 className="mr-2 size-4" />
                            Supprimer
                        </DropdownMenuItem>
                    </DropdownMenuContent>
                </DropdownMenu>
            </div>
        </div>
    );
}
