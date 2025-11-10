import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Income } from '@/types/budget';
import {
    Calendar,
    Edit,
    Eye,
    EyeOff,
    MoreVertical,
    Repeat,
    Trash2,
} from 'lucide-react';

interface IncomeCardProps {
    income: Income;
    onEdit: (income: Income) => void;
    onDelete: (income: Income) => void;
}

const formatAmount = (amount: number | null) => {
    if (!amount) return null;
    return new Intl.NumberFormat('fr-FR', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(amount);
};

const formatDate = (date: string | null) => {
    if (!date) return null;
    return new Date(date).toLocaleDateString('fr-FR', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });
};

export function IncomeCard({
    income,
    onEdit,
    onDelete,
}: IncomeCardProps) {
    return (
        <div className="flex items-center gap-3 rounded-xl border bg-card p-4 transition-colors active:bg-muted/50">
            {/* Clickable area for editing */}
            <div
                className="flex min-w-0 flex-1 cursor-pointer items-center gap-3"
                onClick={() => onEdit(income)}
            >
                {/* Color Indicator */}
                <div
                    className="size-12 shrink-0 rounded-lg border-2"
                    style={{
                        backgroundColor: income.color,
                    }}
                />

                {/* Content */}
                <div className="min-w-0 flex-1 space-y-1">
                    <div className="flex items-center gap-2">
                        <h3 className="truncate text-base font-semibold">
                            {income.name}
                        </h3>
                        {income.is_active ? (
                            <Badge
                                variant="default"
                                className="shrink-0 bg-green-500 text-xs hover:bg-green-600"
                            >
                                <Eye className="mr-1 size-3" />
                            </Badge>
                        ) : (
                            <Badge
                                variant="secondary"
                                className="shrink-0 text-xs"
                            >
                                <EyeOff className="mr-1 size-3" />
                            </Badge>
                        )}
                    </div>

                    {/* Amount and frequency */}
                    {income.amount && (
                        <div className="flex items-center gap-2">
                            <p className="text-lg font-bold text-primary">
                                {formatAmount(income.amount)} FCFA
                            </p>
                            {income.is_monthly ? (
                                <Badge
                                    variant="outline"
                                    className="gap-1 text-xs"
                                >
                                    <Repeat className="size-3" />
                                    Mensuel
                                </Badge>
                            ) : (
                                income.income_date && (
                                    <Badge
                                        variant="outline"
                                        className="gap-1 text-xs"
                                    >
                                        <Calendar className="size-3" />
                                        {formatDate(income.income_date)}
                                    </Badge>
                                )
                            )}
                        </div>
                    )}

                    {income.description && (
                        <p className="line-clamp-2 text-sm text-muted-foreground">
                            {income.description}
                        </p>
                    )}
                </div>
            </div>

            {/* Actions - Mobile Dropdown */}
            <DropdownMenu>
                <DropdownMenuTrigger asChild>
                    <Button
                        variant="ghost"
                        size="icon"
                        className="size-9 shrink-0"
                    >
                        <MoreVertical className="size-5" />
                    </Button>
                </DropdownMenuTrigger>
                <DropdownMenuContent align="end" className="w-48">
                    <DropdownMenuItem
                        onClick={() => onEdit(income)}
                        className="cursor-pointer"
                    >
                        <Edit className="mr-2 size-4" />
                        Modifier
                    </DropdownMenuItem>
                    <DropdownMenuItem
                        onClick={() => onDelete(income)}
                        className="cursor-pointer text-destructive focus:text-destructive"
                    >
                        <Trash2 className="mr-2 size-4" />
                        Supprimer
                    </DropdownMenuItem>
                </DropdownMenuContent>
            </DropdownMenu>
        </div>
    );
}
