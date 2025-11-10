import { IncomeEntry, Income, UserSettings } from '@/types/budget';
import { IncomeEntryCard } from './income-entry-card';
import { IncomeEntryFormModal } from './income-entry-form-modal';
import { ConfirmDialog } from '@/components/ui-element-custom/confirm-dialog';
import { Inbox } from 'lucide-react';
import { useState, useEffect } from 'react';
import { router } from '@inertiajs/react';

interface IncomeEntryListProps {
    incomeEntries: IncomeEntry[];
    incomes: Income[];
    settings: UserSettings;
    isCreating?: boolean;
    onCreateClick: () => void;
    currentYear?: number;
    currentMonth?: number;
}

export function IncomeEntryList({
    incomeEntries,
    incomes,
    settings,
    isCreating = false,
    onCreateClick,
    currentYear,
    currentMonth,
}: IncomeEntryListProps) {
    const [editingIncomeEntry, setEditingIncomeEntry] = useState<IncomeEntry | null>(null);
    const [deletingIncomeEntry, setDeletingIncomeEntry] = useState<IncomeEntry | null>(null);
    const [isFormOpen, setIsFormOpen] = useState(false);

    // Ouvrir le formulaire quand isCreating change
    useEffect(() => {
        if (isCreating) {
            setEditingIncomeEntry(null);
            setIsFormOpen(true);
        }
    }, [isCreating]);

    const handleEdit = (incomeEntry: IncomeEntry) => {
        setEditingIncomeEntry(incomeEntry);
        setIsFormOpen(true);
    };

    const handleDelete = (incomeEntry: IncomeEntry) => {
        setDeletingIncomeEntry(incomeEntry);
    };

    const confirmDelete = () => {
        if (deletingIncomeEntry) {
            router.delete(`/budget/income-entries/${deletingIncomeEntry.id}`, {
                onSuccess: () => setDeletingIncomeEntry(null),
            });
        }
    };

    if (incomeEntries.length === 0) {
        return (
            <>
                <div className="flex flex-col items-center py-16 px-4 text-center">
                    <div className="flex size-16 items-center justify-center rounded-full bg-muted mb-4">
                        <Inbox className="size-8 text-muted-foreground" />
                    </div>
                    <h3 className="text-lg font-semibold mb-2">Aucune entrée de revenu</h3>
                    <p className="text-sm text-muted-foreground mb-6 max-w-sm">
                        Commencez par ajouter votre première entrée de revenu pour ce mois
                    </p>
                </div>

                <IncomeEntryFormModal
                    isOpen={isFormOpen}
                    onClose={() => setIsFormOpen(false)}
                    incomeEntry={editingIncomeEntry}
                    incomes={incomes}
                    currentYear={currentYear}
                    currentMonth={currentMonth}
                />
            </>
        );
    }

    return (
        <>
            <div className="space-y-3">
                {incomeEntries.map((incomeEntry) => (
                    <IncomeEntryCard
                        key={incomeEntry.id}
                        incomeEntry={incomeEntry}
                        settings={settings}
                        onEdit={handleEdit}
                        onDelete={handleDelete}
                    />
                ))}
            </div>

            <IncomeEntryFormModal
                isOpen={isFormOpen}
                onClose={() => setIsFormOpen(false)}
                incomeEntry={editingIncomeEntry}
                incomes={incomes}
                currentYear={currentYear}
                currentMonth={currentMonth}
            />

            <ConfirmDialog
                open={!!deletingIncomeEntry}
                onOpenChange={(open) => !open && setDeletingIncomeEntry(null)}
                title="Supprimer cette entrée ?"
                description="Cette action est irréversible. L'entrée de revenu sera définitivement supprimée."
                onConfirm={confirmDelete}
                confirmText="Supprimer"
                cancelText="Annuler"
            />
        </>
    );
}
