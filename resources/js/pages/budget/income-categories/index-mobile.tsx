import { ConfirmDialog } from '@/components/ui-element-custom/confirm-dialog';
import { Button } from '@/components/ui/button';
import MobileHeaderLayout from '@/layouts/mobile-header-layout';
import { Head, router, useForm } from '@inertiajs/react';
import { Plus } from 'lucide-react';
import { FormEventHandler, useState } from 'react';
import { IncomeCategory } from '@/types/budget';
import { IncomeCategoryListMobile } from '@/components/features/budget/income-category-list-mobile';
import { IncomeCategoryFormModalMobile } from '@/components/features/budget/income-category-form-modal-mobile';

interface Props {
    categories: IncomeCategory[];
}

export default function Index({ categories }: Props) {
    const [editingCategory, setEditingCategory] =
        useState<IncomeCategory | null>(null);
    const [deletingCategory, setDeletingCategory] =
        useState<IncomeCategory | null>(null);
    const [isEditDialogOpen, setIsEditDialogOpen] = useState(false);
    const [isCreateDialogOpen, setIsCreateDialogOpen] = useState(false);
    const [isDeleteDialogOpen, setIsDeleteDialogOpen] = useState(false);

    const { data, setData, post, put, processing, errors, reset } = useForm({
        name: '',
        description: '',
        color: '#3b82f6',
    });

    // Création
    const openCreateDialog = () => {
        reset();
        setIsCreateDialogOpen(true);
    };

    const handleCreate: FormEventHandler = (e) => {
        e.preventDefault();
        post('/budget/income-categories', {
            preserveScroll: true,
            onSuccess: () => {
                setIsCreateDialogOpen(false);
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
            color: category.color,
        });
        setIsEditDialogOpen(true);
    };

    const handleEdit: FormEventHandler = (e) => {
        e.preventDefault();
        if (!editingCategory) return;

        put(`/budget/income-categories/${editingCategory.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                setIsEditDialogOpen(false);
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
                <h1 className="text-2xl sm:text-3xl font-bold tracking-tight mb-2">
                    Catégories de revenus
                </h1>
                <p className="text-sm sm:text-base text-muted-foreground">
                    Gérez vos sources de revenus
                </p>
            </div>

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
                    className="fixed bottom-6 right-6 size-14 rounded-full shadow-lg hover:shadow-xl transition-shadow z-40"
                >
                    <Plus className="size-6" />
                </Button>
            )}

            {/* Modal de création */}
            <IncomeCategoryFormModalMobile
                isOpen={isCreateDialogOpen}
                onClose={() => setIsCreateDialogOpen(false)}
                onSubmit={handleCreate}
                data={data}
                setData={setData}
                errors={errors}
                processing={processing}
                mode="create"
            />

            {/* Modal d'édition */}
            <IncomeCategoryFormModalMobile
                isOpen={isEditDialogOpen}
                onClose={() => setIsEditDialogOpen(false)}
                onSubmit={handleEdit}
                data={data}
                setData={setData}
                errors={errors}
                processing={processing}
                mode="edit"
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
