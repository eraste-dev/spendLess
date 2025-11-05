import { IncomeCategoryFormModalMobile } from '@/components/features/budget/income-category-form-modal-mobile';
import { IncomeCategoryListMobile } from '@/components/features/budget/income-category-list-mobile';
import { ConfirmDialog } from '@/components/ui-element-custom/confirm-dialog';
import { Button } from '@/components/ui/button';
import MobileHeaderLayout from '@/layouts/mobile-header-layout';
import { formatCurrency } from '@/lib/currency';
import { IncomeCategory, UserSettings } from '@/types/budget';
import { Head, router, useForm } from '@inertiajs/react';
import { Plus } from 'lucide-react';
import { FormEventHandler, useState } from 'react';

interface Props {
    categories: IncomeCategory[];
    settings: UserSettings;
    totalIncome: number;
}

export default function Index({ categories, settings, totalIncome }: Props) {
    const [editingCategory, setEditingCategory] =
        useState<IncomeCategory | null>(null);
    const [deletingCategory, setDeletingCategory] =
        useState<IncomeCategory | null>(null);
    const [isFormDialogOpen, setIsFormDialogOpen] = useState(false);
    const [isDeleteDialogOpen, setIsDeleteDialogOpen] = useState(false);

    const { data, setData, post, put, processing, errors, reset } = useForm({
        name: '',
        description: '',
        amount: '',
        is_monthly: false,
        income_date: '',
        color: '#3b82f6',
    });

    // Création
    const openCreateDialog = () => {
        reset();
        setEditingCategory(null);
        setIsFormDialogOpen(true);
    };

    const handleCreate: FormEventHandler = (e) => {
        e.preventDefault();
        post('/budget/income-categories', {
            preserveScroll: true,
            onSuccess: () => {
                setIsFormDialogOpen(false);
                reset();
            },
        });
    };

    // Édition
    const openEditDialog = (category: IncomeCategory) => {
        setEditingCategory(category);
        setData({
            name: category.name,
            description: category.description || '',
            amount: category.amount?.toString() || '',
            is_monthly: category.is_monthly,
            income_date: category.income_date || '',
            color: category.color,
        });
        setIsFormDialogOpen(true);
    };

    const handleEdit: FormEventHandler = (e) => {
        e.preventDefault();
        if (!editingCategory) return;

        put(`/budget/income-categories/${editingCategory.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                setIsFormDialogOpen(false);
                setEditingCategory(null);
                reset();
            },
        });
    };

    // Suppression
    const openDeleteDialog = (category: IncomeCategory) => {
        setDeletingCategory(category);
        setIsDeleteDialogOpen(true);
    };

    const handleDelete = () => {
        if (!deletingCategory) return;

        router.delete(`/budget/income-categories/${deletingCategory.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                setDeletingCategory(null);
            },
        });
    };

    return (
        <MobileHeaderLayout>
            <Head title="Catégories de revenus" />

            {/* Page Header - Mobile optimized */}
            <div className="mb-6">
                <h1 className="mb-2 text-2xl font-bold tracking-tight sm:text-3xl">
                    Catégories de revenus
                </h1>
                <p className="text-sm text-muted-foreground sm:text-base">
                    Gérez vos sources de revenus
                </p>
            </div>

            {/* Total Income Display */}
            {categories.length > 0 && (
                <div className="mb-6 rounded-xl border bg-gradient-to-br from-primary/5 to-primary/10 p-6">
                    <div className="flex items-baseline justify-between gap-4">
                        <div className="flex-1">
                            <p className="mb-1 text-sm font-medium text-muted-foreground">
                                Total des revenus actifs
                            </p>
                            <div className="flex items-baseline gap-2">
                                <span className="text-3xl font-bold tracking-tight sm:text-4xl">
                                    {formatCurrency(totalIncome, settings)}
                                </span>
                            </div>
                        </div>
                    </div>
                    <p className="mt-3 text-xs text-muted-foreground">
                        Somme des montants des catégories actives
                    </p>
                </div>
            )}

            {/* Liste des catégories */}
            <IncomeCategoryListMobile
                categories={categories}
                onEdit={openEditDialog}
                onDelete={openDeleteDialog}
                onCreate={openCreateDialog}
            />

            {/* Floating Action Button - Visible uniquement si des catégories existent */}
            {categories.length > 0 && (
                <Button
                    onClick={openCreateDialog}
                    size="lg"
                    className="fixed right-6 bottom-6 z-40 size-14 rounded-full shadow-lg transition-shadow hover:shadow-xl"
                >
                    <Plus className="size-6" />
                </Button>
            )}

            {/* Modal unique de formulaire (création/édition) */}
            <IncomeCategoryFormModalMobile
                isOpen={isFormDialogOpen}
                onClose={() => {
                    setIsFormDialogOpen(false);
                    setEditingCategory(null);
                }}
                onSubmit={editingCategory ? handleEdit : handleCreate}
                data={data}
                setData={setData}
                errors={errors}
                processing={processing}
                mode={editingCategory ? 'edit' : 'create'}
            />

            {/* Dialog de confirmation de suppression */}
            <ConfirmDialog
                open={isDeleteDialogOpen}
                onOpenChange={setIsDeleteDialogOpen}
                onConfirm={handleDelete}
                title="Supprimer cette catégorie ?"
                description={`Êtes-vous sûr de vouloir supprimer la catégorie "${deletingCategory?.name}" ? Cette action est irréversible.`}
                confirmText="Supprimer"
                cancelText="Annuler"
                variant="danger"
            />
        </MobileHeaderLayout>
    );
}
