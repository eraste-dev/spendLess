import { Button } from '@/components/ui/button';
import { Plus, Inbox } from 'lucide-react';
import { IncomeCard } from './income-card';
import { Income } from '@/types/budget';

interface IncomeListProps {
    incomes: Income[];
    onEdit: (income: Income) => void;
    onDelete: (income: Income) => void;
    onCreate: () => void;
}

export function IncomeList({
    incomes,
    onEdit,
    onDelete,
    onCreate,
}: IncomeListProps) {
    if (incomes.length === 0) {
        return (
            <div className="flex flex-col items-center justify-center py-16 px-4 text-center">
                <div className="flex size-16 items-center justify-center rounded-full bg-muted mb-4">
                    <Inbox className="size-8 text-muted-foreground" />
                </div>
                <h3 className="text-lg font-semibold mb-2">
                    Aucun revenu
                </h3>
                <p className="text-sm text-muted-foreground mb-6 max-w-sm">
                    Commencez par créer votre première source de revenu
                </p>
                <Button onClick={onCreate} size="lg" className="rounded-full">
                    <Plus className="mr-2 size-5" />
                    Créer un revenu
                </Button>
            </div>
        );
    }

    return (
        <div className="space-y-3">
            {/* Header avec compteur */}
            <div className="flex items-center justify-between px-1">
                <p className="text-sm text-muted-foreground">
                    {incomes.length}{' '}
                    {incomes.length > 1 ? 'revenus' : 'revenu'}
                </p>
            </div>

            {/* Liste des revenus */}
            <div className="space-y-3">
                {incomes.map((income) => (
                    <IncomeCard
                        key={income.id}
                        income={income}
                        onEdit={onEdit}
                        onDelete={onDelete}
                    />
                ))}
            </div>
        </div>
    );
}
