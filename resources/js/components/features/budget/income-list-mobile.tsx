import { Income, IncomeCategory, UserSettings } from '@/types/budget';
import { IncomeCardMobile } from './income-card-mobile';
import { IncomeFormModalMobile } from './income-form-modal-mobile';
import { ConfirmDialog } from '@/components/ui-element-custom/confirm-dialog';
import { Inbox } from 'lucide-react';
import { useState } from 'react';
import { router } from '@inertiajs/react';

interface IncomeListMobileProps {
    incomes: Income[];
    categories: IncomeCategory[];
    settings: UserSettings;
    onCreate: () => void;
}

export function IncomeListMobile({
    incomes,
    categories,
    settings,
    onCreate,
}: IncomeListMobileProps) {
    const [editingIncome, setEditingIncome] = useState<Income | null>(null);
    const [deletingIncome, setDeletingIncome] = useState<Income | null>(null);
    const [isFormOpen, setIsFormOpen] = useState(false);

    const handleCreate = () => {
        setEditingIncome(null);
        setIsFormOpen(true);
        onCreate();
    };

    const handleEdit = (income: Income) => {
        setEditingIncome(income);
        setIsFormOpen(true);
    };

    const handleDelete = (income: Income) => {
        setDeletingIncome(income);
    };

    const confirmDelete = () => {
        if (deletingIncome) {
            router.delete(route('budget.incomes.destroy', deletingIncome.id), {
                onSuccess: () => setDeletingIncome(null),
            });
        }
    };

    if (incomes.length === 0) {
        return (
            <>
                <div className="flex flex-col items-center py-16 px-4 text-center">
                    <div className="flex size-16 items-center justify-center rounded-full bg-muted mb-4">
                        <Inbox className="size-8 text-muted-foreground" />
                    </div>
                    <h3 className="text-lg font-semibold mb-2">Aucun revenu</h3>
                    <p className="text-sm text-muted-foreground mb-6 max-w-sm">
                        Commencez par ajouter votre premier revenu pour ce mois
                    </p>
                </div>

                <IncomeFormModalMobile
                    isOpen={isFormOpen}
                    onClose={() => setIsFormOpen(false)}
                    income={editingIncome}
                    categories={categories}
                />
            </>
        );
    }

    return (
        <>
            <div className="space-y-3">
                {incomes.map((income) => (
                    <IncomeCardMobile
                        key={income.id}
                        income={income}
                        settings={settings}
                        onEdit={handleEdit}
                        onDelete={handleDelete}
                    />
                ))}
            </div>

            <IncomeFormModalMobile
                isOpen={isFormOpen}
                onClose={() => setIsFormOpen(false)}
                income={editingIncome}
                categories={categories}
            />

            <ConfirmDialog
                open={!!deletingIncome}
                onOpenChange={(open) => !open && setDeletingIncome(null)}
                title="Supprimer ce revenu ?"
                description="Cette action est irréversible. Le revenu sera définitivement supprimé."
                onConfirm={confirmDelete}
                confirmText="Supprimer"
                cancelText="Annuler"
            />
        </>
    );
}
