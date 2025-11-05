import AppLayout from '@/layouts/app-layout';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Badge } from '@/components/ui/badge';
import { ConfirmDialog } from '@/components/ui-element-custom/confirm-dialog';
import { Plus, Edit, Trash2, Eye, EyeOff, ChevronRight } from 'lucide-react';
import { Head, useForm, router } from '@inertiajs/react';
import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from '@/components/ui/collapsible';
import { useState, FormEventHandler } from 'react';

interface ExpenseSubcategory {
    id: number;
    name: string;
    description: string | null;
    target_amount: number | null;
    order: number;
    is_active: boolean;
}

interface ExpenseCategory {
    id: number;
    name: string;
    description: string | null;
    color: string;
    target_percentage: number | null;
    target_amount: number | null;
    order: number;
    is_active: boolean;
    is_recurring_monthly: boolean;
    recurrence_day: number;
    subcategories?: ExpenseSubcategory[];
    created_at: string;
    updated_at: string;
}

interface Props {
    categories: ExpenseCategory[];
}

export default function Index({ categories }: Props) {
    const [openCategories, setOpenCategories] = useState<number[]>(
        categories.map((c) => c.id),
    );
    const [editingCategory, setEditingCategory] =
        useState<ExpenseCategory | null>(null);
    const [deletingCategory, setDeletingCategory] =
        useState<ExpenseCategory | null>(null);
    const [editingSubcategory, setEditingSubcategory] =
        useState<ExpenseSubcategory | null>(null);
    const [deletingSubcategory, setDeletingSubcategory] =
        useState<ExpenseSubcategory | null>(null);
    const [isEditCategoryDialogOpen, setIsEditCategoryDialogOpen] =
        useState(false);
    const [isCreateCategoryDialogOpen, setIsCreateCategoryDialogOpen] =
        useState(false);
    const [isDeleteCategoryDialogOpen, setIsDeleteCategoryDialogOpen] =
        useState(false);
    const [isEditSubcategoryDialogOpen, setIsEditSubcategoryDialogOpen] =
        useState(false);
    const [isCreateSubcategoryDialogOpen, setIsCreateSubcategoryDialogOpen] =
        useState(false);
    const [isDeleteSubcategoryDialogOpen, setIsDeleteSubcategoryDialogOpen] =
        useState(false);
    const [currentCategoryForSubcategory, setCurrentCategoryForSubcategory] =
        useState<number | null>(null);

    const {
        data: categoryData,
        setData: setCategoryData,
        post: postCategory,
        put: putCategory,
        processing: processingCategory,
        errors: categoryErrors,
        reset: resetCategory,
    } = useForm({
        name: '',
        description: '',
        color: '#3b82f6',
        target_percentage: '',
        target_amount: '',
        is_recurring_monthly: false,
        recurrence_day: 1,
    });

    const {
        data: subcategoryData,
        setData: setSubcategoryData,
        post: postSubcategory,
        put: putSubcategory,
        processing: processingSubcategory,
        errors: subcategoryErrors,
        reset: resetSubcategory,
    } = useForm({
        expense_category_id: 0,
        name: '',
        description: '',
        target_amount: '',
    });

    const toggleCategory = (id: number) => {
        setOpenCategories((prev) =>
            prev.includes(id)
                ? prev.filter((catId) => catId !== id)
                : [...prev, id],
        );
    };

    // Category CRUD handlers
    const openCreateCategoryDialog = () => {
        resetCategory();
        setIsCreateCategoryDialogOpen(true);
    };

    const handleCreateCategory: FormEventHandler = (e) => {
        e.preventDefault();
        postCategory('/budget/expense-categories', {
            preserveScroll: true,
            onSuccess: () => {
                setIsCreateCategoryDialogOpen(false);
                resetCategory();
            },
        });
    };

    const openEditCategoryDialog = (category: ExpenseCategory) => {
        setEditingCategory(category);
        setCategoryData({
            name: category.name,
            description: category.description || '',
            color: category.color,
            target_percentage: category.target_percentage?.toString() || '',
            target_amount: category.target_amount?.toString() || '',
            is_recurring_monthly: category.is_recurring_monthly,
            recurrence_day: category.recurrence_day,
        });
        setIsEditCategoryDialogOpen(true);
    };

    const handleEditCategory: FormEventHandler = (e) => {
        e.preventDefault();
        if (!editingCategory) return;

        putCategory(`/budget/expense-categories/${editingCategory.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                setIsEditCategoryDialogOpen(false);
                setEditingCategory(null);
                resetCategory();
            },
        });
    };

    const openDeleteCategoryDialog = (category: ExpenseCategory) => {
        setDeletingCategory(category);
        setIsDeleteCategoryDialogOpen(true);
    };

    const handleDeleteCategory = () => {
        if (!deletingCategory) return;

        router.delete(`/budget/expense-categories/${deletingCategory.id}`, {
            preserveScroll: true,
            onSuccess: () => {
                setDeletingCategory(null);
            },
        });
    };

    // Subcategory CRUD handlers
    const openCreateSubcategoryDialog = (categoryId: number) => {
        resetSubcategory();
        setCurrentCategoryForSubcategory(categoryId);
        setSubcategoryData('expense_category_id', categoryId);
        setIsCreateSubcategoryDialogOpen(true);
    };

    const handleCreateSubcategory: FormEventHandler = (e) => {
        e.preventDefault();
        postSubcategory('/budget/expense-subcategories', {
            preserveScroll: true,
            onSuccess: () => {
                setIsCreateSubcategoryDialogOpen(false);
                setCurrentCategoryForSubcategory(null);
                resetSubcategory();
            },
        });
    };

    const openEditSubcategoryDialog = (subcategory: ExpenseSubcategory) => {
        setEditingSubcategory(subcategory);
        setSubcategoryData({
            expense_category_id: 0,
            name: subcategory.name,
            description: subcategory.description || '',
            target_amount: subcategory.target_amount?.toString() || '',
        });
        setIsEditSubcategoryDialogOpen(true);
    };

    const handleEditSubcategory: FormEventHandler = (e) => {
        e.preventDefault();
        if (!editingSubcategory) return;

        putSubcategory(
            `/budget/expense-subcategories/${editingSubcategory.id}`,
            {
                preserveScroll: true,
                onSuccess: () => {
                    setIsEditSubcategoryDialogOpen(false);
                    setEditingSubcategory(null);
                    resetSubcategory();
                },
            },
        );
    };

    const openDeleteSubcategoryDialog = (subcategory: ExpenseSubcategory) => {
        setDeletingSubcategory(subcategory);
        setIsDeleteSubcategoryDialogOpen(true);
    };

    const handleDeleteSubcategory = () => {
        if (!deletingSubcategory) return;

        router.delete(
            `/budget/expense-subcategories/${deletingSubcategory.id}`,
            {
                preserveScroll: true,
                onSuccess: () => {
                    setDeletingSubcategory(null);
                },
            },
        );
    };

    return (
        <AppLayout
            breadcrumbs={[
                { title: 'Dashboard', href: '/dashboard' },
                { title: 'Budget', href: '#' },
                {
                    title: 'Catégories de dépenses',
                    href: '/budget/expense-categories',
                },
            ]}
        >
            <Head title="Catégories de dépenses" />

            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-3xl font-bold tracking-tight">
                            Catégories de dépenses
                        </h1>
                        <p className="text-muted-foreground">
                            Gérez vos catégories et sous-catégories de dépenses
                        </p>
                    </div>
                    <Button onClick={openCreateCategoryDialog}>
                        <Plus className="mr-2 size-4" />
                        Nouvelle catégorie
                    </Button>
                </div>

                <div className="space-y-4">
                    {categories.length === 0 ? (
                        <Card>
                            <CardContent className="flex flex-col items-center justify-center py-12 text-center">
                                <p className="text-muted-foreground">
                                    Aucune catégorie de dépenses
                                </p>
                                <p className="text-sm text-muted-foreground">
                                    Commencez par créer votre première
                                    catégorie
                                </p>
                                <Button
                                    className="mt-4"
                                    onClick={openCreateCategoryDialog}
                                >
                                    <Plus className="mr-2 size-4" />
                                    Créer une catégorie
                                </Button>
                            </CardContent>
                        </Card>
                    ) : (
                        categories.map((category) => (
                            <Card key={category.id}>
                                <CardHeader>
                                    <div className="flex items-start justify-between">
                                        <div className="flex items-center gap-4">
                                            <div
                                                className="size-12 rounded-lg border"
                                                style={{
                                                    backgroundColor:
                                                        category.color,
                                                }}
                                            />
                                            <div>
                                                <div className="flex items-center gap-2">
                                                    <CardTitle>
                                                        {category.name}
                                                    </CardTitle>
                                                    {category.is_active ? (
                                                        <Badge
                                                            variant="default"
                                                            className="bg-green-500 hover:bg-green-600"
                                                        >
                                                            <Eye className="mr-1 size-3" />
                                                            Actif
                                                        </Badge>
                                                    ) : (
                                                        <Badge variant="secondary">
                                                            <EyeOff className="mr-1 size-3" />
                                                            Inactif
                                                        </Badge>
                                                    )}
                                                    {category.is_recurring_monthly && (
                                                        <Badge variant="outline">
                                                            Récurrent
                                                        </Badge>
                                                    )}
                                                </div>
                                                <CardDescription className="mt-1">
                                                    {category.description ||
                                                        'Aucune description'}
                                                    {category.target_percentage && (
                                                        <span className="ml-2 font-medium">
                                                            • {category.target_percentage}% du budget
                                                        </span>
                                                    )}
                                                </CardDescription>
                                            </div>
                                        </div>
                                        <div className="flex gap-2">
                                            <Button
                                                variant="outline"
                                                size="sm"
                                                onClick={() =>
                                                    openEditCategoryDialog(
                                                        category,
                                                    )
                                                }
                                            >
                                                <Edit className="mr-2 size-4" />
                                                Modifier
                                            </Button>
                                            <Button
                                                variant="outline"
                                                size="sm"
                                                className="text-destructive hover:text-destructive"
                                                onClick={() =>
                                                    openDeleteCategoryDialog(
                                                        category,
                                                    )
                                                }
                                            >
                                                <Trash2 className="mr-2 size-4" />
                                                Supprimer
                                            </Button>
                                        </div>
                                    </div>
                                </CardHeader>

                                {category.subcategories &&
                                    category.subcategories.length > 0 && (
                                        <CardContent>
                                            <Collapsible
                                                open={openCategories.includes(
                                                    category.id,
                                                )}
                                                onOpenChange={() =>
                                                    toggleCategory(category.id)
                                                }
                                            >
                                                <div className="flex items-center justify-between">
                                                    <CollapsibleTrigger asChild>
                                                        <Button
                                                            variant="ghost"
                                                            size="sm"
                                                            className="w-full justify-start"
                                                        >
                                                            <ChevronRight
                                                                className={`mr-2 size-4 transition-transform ${
                                                                    openCategories.includes(
                                                                        category.id,
                                                                    )
                                                                        ? 'rotate-90'
                                                                        : ''
                                                                }`}
                                                            />
                                                            {category.subcategories.length}{' '}
                                                            sous-catégorie(s)
                                                        </Button>
                                                    </CollapsibleTrigger>
                                                    <Button
                                                        variant="outline"
                                                        size="sm"
                                                        onClick={() =>
                                                            openCreateSubcategoryDialog(
                                                                category.id,
                                                            )
                                                        }
                                                    >
                                                        <Plus className="mr-2 size-4" />
                                                        Ajouter
                                                    </Button>
                                                </div>

                                                <CollapsibleContent className="mt-4">
                                                    <div className="space-y-2 rounded-lg border bg-muted/30 p-4">
                                                        {category.subcategories.map(
                                                            (subcategory) => (
                                                                <div
                                                                    key={
                                                                        subcategory.id
                                                                    }
                                                                    className="flex items-center justify-between rounded-md border bg-background p-3 hover:bg-muted/50"
                                                                >
                                                                    <div className="flex-1">
                                                                        <div className="flex items-center gap-2">
                                                                            <p className="font-medium">
                                                                                {
                                                                                    subcategory.name
                                                                                }
                                                                            </p>
                                                                            {subcategory.is_active ? (
                                                                                <Badge
                                                                                    variant="default"
                                                                                    className="bg-green-500 hover:bg-green-600"
                                                                                >
                                                                                    Actif
                                                                                </Badge>
                                                                            ) : (
                                                                                <Badge variant="secondary">
                                                                                    Inactif
                                                                                </Badge>
                                                                            )}
                                                                        </div>
                                                                        {subcategory.description && (
                                                                            <p className="text-sm text-muted-foreground">
                                                                                {
                                                                                    subcategory.description
                                                                                }
                                                                            </p>
                                                                        )}
                                                                    </div>
                                                                    <div className="flex items-center gap-2">
                                                                        <Button
                                                                            variant="ghost"
                                                                            size="sm"
                                                                            onClick={() =>
                                                                                openEditSubcategoryDialog(
                                                                                    subcategory,
                                                                                )
                                                                            }
                                                                        >
                                                                            <Edit className="size-4" />
                                                                        </Button>
                                                                        <Button
                                                                            variant="ghost"
                                                                            size="sm"
                                                                            className="text-destructive hover:text-destructive"
                                                                            onClick={() =>
                                                                                openDeleteSubcategoryDialog(
                                                                                    subcategory,
                                                                                )
                                                                            }
                                                                        >
                                                                            <Trash2 className="size-4" />
                                                                        </Button>
                                                                    </div>
                                                                </div>
                                                            ),
                                                        )}
                                                    </div>
                                                </CollapsibleContent>
                                            </Collapsible>
                                        </CardContent>
                                    )}
                            </Card>
                        ))
                    )}
                </div>
            </div>

            {/* Category Create Modal */}
            <Dialog
                open={isCreateCategoryDialogOpen}
                onOpenChange={setIsCreateCategoryDialogOpen}
            >
                <DialogContent className="sm:max-w-[500px]">
                    <form onSubmit={handleCreateCategory}>
                        <DialogHeader>
                            <DialogTitle>
                                Nouvelle catégorie de dépense
                            </DialogTitle>
                            <DialogDescription>
                                Créez une nouvelle catégorie pour organiser vos
                                dépenses
                            </DialogDescription>
                        </DialogHeader>

                        <div className="space-y-4 py-4">
                            <div className="space-y-2">
                                <Label htmlFor="create-category-name">
                                    Nom de la catégorie *
                                </Label>
                                <Input
                                    id="create-category-name"
                                    value={categoryData.name}
                                    onChange={(e) =>
                                        setCategoryData('name', e.target.value)
                                    }
                                    placeholder="Ex: Logement, Transport..."
                                    className={
                                        categoryErrors.name
                                            ? 'border-red-500'
                                            : ''
                                    }
                                />
                                {categoryErrors.name && (
                                    <p className="text-sm text-red-500">
                                        {categoryErrors.name}
                                    </p>
                                )}
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="create-category-description">
                                    Description
                                </Label>
                                <Textarea
                                    id="create-category-description"
                                    value={categoryData.description}
                                    onChange={(e) =>
                                        setCategoryData(
                                            'description',
                                            e.target.value,
                                        )
                                    }
                                    placeholder="Décrivez cette catégorie..."
                                    rows={3}
                                />
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="create-category-color">
                                    Couleur
                                </Label>
                                <div className="flex gap-2">
                                    <Input
                                        id="create-category-color"
                                        type="color"
                                        value={categoryData.color}
                                        onChange={(e) =>
                                            setCategoryData(
                                                'color',
                                                e.target.value,
                                            )
                                        }
                                        className="h-10 w-20 cursor-pointer"
                                    />
                                    <Input
                                        type="text"
                                        value={categoryData.color}
                                        onChange={(e) =>
                                            setCategoryData(
                                                'color',
                                                e.target.value,
                                            )
                                        }
                                        placeholder="#3b82f6"
                                        className="flex-1"
                                    />
                                </div>
                            </div>

                            <div className="grid grid-cols-2 gap-4">
                                <div className="space-y-2">
                                    <Label htmlFor="create-category-target-percentage">
                                        Pourcentage cible (%)
                                    </Label>
                                    <Input
                                        id="create-category-target-percentage"
                                        type="number"
                                        step="0.01"
                                        value={categoryData.target_percentage}
                                        onChange={(e) =>
                                            setCategoryData(
                                                'target_percentage',
                                                e.target.value,
                                            )
                                        }
                                        placeholder="Ex: 30"
                                    />
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="create-category-target-amount">
                                        Montant cible
                                    </Label>
                                    <Input
                                        id="create-category-target-amount"
                                        type="number"
                                        step="0.01"
                                        value={categoryData.target_amount}
                                        onChange={(e) =>
                                            setCategoryData(
                                                'target_amount',
                                                e.target.value,
                                            )
                                        }
                                        placeholder="Ex: 5000"
                                    />
                                </div>
                            </div>

                            <div className="space-y-2">
                                <div className="flex items-center gap-2">
                                    <input
                                        id="create-category-recurring"
                                        type="checkbox"
                                        checked={
                                            categoryData.is_recurring_monthly
                                        }
                                        onChange={(e) =>
                                            setCategoryData(
                                                'is_recurring_monthly',
                                                e.target.checked,
                                            )
                                        }
                                        className="size-4"
                                    />
                                    <Label htmlFor="create-category-recurring">
                                        Récurrent mensuel
                                    </Label>
                                </div>
                            </div>

                            {categoryData.is_recurring_monthly && (
                                <div className="space-y-2">
                                    <Label htmlFor="create-category-recurrence-day">
                                        Jour de récurrence (1-31)
                                    </Label>
                                    <Input
                                        id="create-category-recurrence-day"
                                        type="number"
                                        min="1"
                                        max="31"
                                        value={categoryData.recurrence_day}
                                        onChange={(e) =>
                                            setCategoryData(
                                                'recurrence_day',
                                                parseInt(e.target.value),
                                            )
                                        }
                                    />
                                </div>
                            )}
                        </div>

                        <DialogFooter>
                            <Button
                                type="button"
                                variant="outline"
                                onClick={() =>
                                    setIsCreateCategoryDialogOpen(false)
                                }
                                disabled={processingCategory}
                            >
                                Annuler
                            </Button>
                            <Button
                                type="submit"
                                disabled={processingCategory}
                            >
                                {processingCategory ? 'Création...' : 'Créer'}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            {/* Category Edit Modal */}
            <Dialog
                open={isEditCategoryDialogOpen}
                onOpenChange={setIsEditCategoryDialogOpen}
            >
                <DialogContent className="sm:max-w-[500px]">
                    <form onSubmit={handleEditCategory}>
                        <DialogHeader>
                            <DialogTitle>Modifier la catégorie</DialogTitle>
                            <DialogDescription>
                                Modifiez les informations de la catégorie de
                                dépense
                            </DialogDescription>
                        </DialogHeader>

                        <div className="space-y-4 py-4">
                            <div className="space-y-2">
                                <Label htmlFor="edit-category-name">
                                    Nom de la catégorie *
                                </Label>
                                <Input
                                    id="edit-category-name"
                                    value={categoryData.name}
                                    onChange={(e) =>
                                        setCategoryData('name', e.target.value)
                                    }
                                    placeholder="Ex: Logement, Transport..."
                                    className={
                                        categoryErrors.name
                                            ? 'border-red-500'
                                            : ''
                                    }
                                />
                                {categoryErrors.name && (
                                    <p className="text-sm text-red-500">
                                        {categoryErrors.name}
                                    </p>
                                )}
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="edit-category-description">
                                    Description
                                </Label>
                                <Textarea
                                    id="edit-category-description"
                                    value={categoryData.description}
                                    onChange={(e) =>
                                        setCategoryData(
                                            'description',
                                            e.target.value,
                                        )
                                    }
                                    placeholder="Décrivez cette catégorie..."
                                    rows={3}
                                />
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="edit-category-color">
                                    Couleur
                                </Label>
                                <div className="flex gap-2">
                                    <Input
                                        id="edit-category-color"
                                        type="color"
                                        value={categoryData.color}
                                        onChange={(e) =>
                                            setCategoryData(
                                                'color',
                                                e.target.value,
                                            )
                                        }
                                        className="h-10 w-20 cursor-pointer"
                                    />
                                    <Input
                                        type="text"
                                        value={categoryData.color}
                                        onChange={(e) =>
                                            setCategoryData(
                                                'color',
                                                e.target.value,
                                            )
                                        }
                                        placeholder="#3b82f6"
                                        className="flex-1"
                                    />
                                </div>
                            </div>

                            <div className="grid grid-cols-2 gap-4">
                                <div className="space-y-2">
                                    <Label htmlFor="edit-category-target-percentage">
                                        Pourcentage cible (%)
                                    </Label>
                                    <Input
                                        id="edit-category-target-percentage"
                                        type="number"
                                        step="0.01"
                                        value={categoryData.target_percentage}
                                        onChange={(e) =>
                                            setCategoryData(
                                                'target_percentage',
                                                e.target.value,
                                            )
                                        }
                                        placeholder="Ex: 30"
                                    />
                                </div>

                                <div className="space-y-2">
                                    <Label htmlFor="edit-category-target-amount">
                                        Montant cible
                                    </Label>
                                    <Input
                                        id="edit-category-target-amount"
                                        type="number"
                                        step="0.01"
                                        value={categoryData.target_amount}
                                        onChange={(e) =>
                                            setCategoryData(
                                                'target_amount',
                                                e.target.value,
                                            )
                                        }
                                        placeholder="Ex: 5000"
                                    />
                                </div>
                            </div>

                            <div className="space-y-2">
                                <div className="flex items-center gap-2">
                                    <input
                                        id="edit-category-recurring"
                                        type="checkbox"
                                        checked={
                                            categoryData.is_recurring_monthly
                                        }
                                        onChange={(e) =>
                                            setCategoryData(
                                                'is_recurring_monthly',
                                                e.target.checked,
                                            )
                                        }
                                        className="size-4"
                                    />
                                    <Label htmlFor="edit-category-recurring">
                                        Récurrent mensuel
                                    </Label>
                                </div>
                            </div>

                            {categoryData.is_recurring_monthly && (
                                <div className="space-y-2">
                                    <Label htmlFor="edit-category-recurrence-day">
                                        Jour de récurrence (1-31)
                                    </Label>
                                    <Input
                                        id="edit-category-recurrence-day"
                                        type="number"
                                        min="1"
                                        max="31"
                                        value={categoryData.recurrence_day}
                                        onChange={(e) =>
                                            setCategoryData(
                                                'recurrence_day',
                                                parseInt(e.target.value),
                                            )
                                        }
                                    />
                                </div>
                            )}
                        </div>

                        <DialogFooter>
                            <Button
                                type="button"
                                variant="outline"
                                onClick={() =>
                                    setIsEditCategoryDialogOpen(false)
                                }
                                disabled={processingCategory}
                            >
                                Annuler
                            </Button>
                            <Button
                                type="submit"
                                disabled={processingCategory}
                            >
                                {processingCategory
                                    ? 'Enregistrement...'
                                    : 'Enregistrer'}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            {/* Category Delete Confirmation */}
            <ConfirmDialog
                open={isDeleteCategoryDialogOpen}
                onOpenChange={setIsDeleteCategoryDialogOpen}
                onConfirm={handleDeleteCategory}
                title="Supprimer cette catégorie ?"
                description={`Êtes-vous sûr de vouloir supprimer la catégorie "${deletingCategory?.name}" ? Toutes les sous-catégories associées seront également supprimées. Cette action est irréversible.`}
                confirmText="Supprimer"
                cancelText="Annuler"
                variant="danger"
            />

            {/* Subcategory Create Modal */}
            <Dialog
                open={isCreateSubcategoryDialogOpen}
                onOpenChange={setIsCreateSubcategoryDialogOpen}
            >
                <DialogContent className="sm:max-w-[500px]">
                    <form onSubmit={handleCreateSubcategory}>
                        <DialogHeader>
                            <DialogTitle>
                                Nouvelle sous-catégorie
                            </DialogTitle>
                            <DialogDescription>
                                Créez une nouvelle sous-catégorie pour mieux
                                organiser vos dépenses
                            </DialogDescription>
                        </DialogHeader>

                        <div className="space-y-4 py-4">
                            <div className="space-y-2">
                                <Label htmlFor="create-subcategory-name">
                                    Nom de la sous-catégorie *
                                </Label>
                                <Input
                                    id="create-subcategory-name"
                                    value={subcategoryData.name}
                                    onChange={(e) =>
                                        setSubcategoryData(
                                            'name',
                                            e.target.value,
                                        )
                                    }
                                    placeholder="Ex: Électricité, Internet..."
                                    className={
                                        subcategoryErrors.name
                                            ? 'border-red-500'
                                            : ''
                                    }
                                />
                                {subcategoryErrors.name && (
                                    <p className="text-sm text-red-500">
                                        {subcategoryErrors.name}
                                    </p>
                                )}
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="create-subcategory-description">
                                    Description
                                </Label>
                                <Textarea
                                    id="create-subcategory-description"
                                    value={subcategoryData.description}
                                    onChange={(e) =>
                                        setSubcategoryData(
                                            'description',
                                            e.target.value,
                                        )
                                    }
                                    placeholder="Décrivez cette sous-catégorie..."
                                    rows={3}
                                />
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="create-subcategory-target-amount">
                                    Montant cible
                                </Label>
                                <Input
                                    id="create-subcategory-target-amount"
                                    type="number"
                                    step="0.01"
                                    value={subcategoryData.target_amount}
                                    onChange={(e) =>
                                        setSubcategoryData(
                                            'target_amount',
                                            e.target.value,
                                        )
                                    }
                                    placeholder="Ex: 500"
                                />
                            </div>
                        </div>

                        <DialogFooter>
                            <Button
                                type="button"
                                variant="outline"
                                onClick={() =>
                                    setIsCreateSubcategoryDialogOpen(false)
                                }
                                disabled={processingSubcategory}
                            >
                                Annuler
                            </Button>
                            <Button
                                type="submit"
                                disabled={processingSubcategory}
                            >
                                {processingSubcategory
                                    ? 'Création...'
                                    : 'Créer'}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            {/* Subcategory Edit Modal */}
            <Dialog
                open={isEditSubcategoryDialogOpen}
                onOpenChange={setIsEditSubcategoryDialogOpen}
            >
                <DialogContent className="sm:max-w-[500px]">
                    <form onSubmit={handleEditSubcategory}>
                        <DialogHeader>
                            <DialogTitle>
                                Modifier la sous-catégorie
                            </DialogTitle>
                            <DialogDescription>
                                Modifiez les informations de la sous-catégorie
                            </DialogDescription>
                        </DialogHeader>

                        <div className="space-y-4 py-4">
                            <div className="space-y-2">
                                <Label htmlFor="edit-subcategory-name">
                                    Nom de la sous-catégorie *
                                </Label>
                                <Input
                                    id="edit-subcategory-name"
                                    value={subcategoryData.name}
                                    onChange={(e) =>
                                        setSubcategoryData(
                                            'name',
                                            e.target.value,
                                        )
                                    }
                                    placeholder="Ex: Électricité, Internet..."
                                    className={
                                        subcategoryErrors.name
                                            ? 'border-red-500'
                                            : ''
                                    }
                                />
                                {subcategoryErrors.name && (
                                    <p className="text-sm text-red-500">
                                        {subcategoryErrors.name}
                                    </p>
                                )}
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="edit-subcategory-description">
                                    Description
                                </Label>
                                <Textarea
                                    id="edit-subcategory-description"
                                    value={subcategoryData.description}
                                    onChange={(e) =>
                                        setSubcategoryData(
                                            'description',
                                            e.target.value,
                                        )
                                    }
                                    placeholder="Décrivez cette sous-catégorie..."
                                    rows={3}
                                />
                            </div>

                            <div className="space-y-2">
                                <Label htmlFor="edit-subcategory-target-amount">
                                    Montant cible
                                </Label>
                                <Input
                                    id="edit-subcategory-target-amount"
                                    type="number"
                                    step="0.01"
                                    value={subcategoryData.target_amount}
                                    onChange={(e) =>
                                        setSubcategoryData(
                                            'target_amount',
                                            e.target.value,
                                        )
                                    }
                                    placeholder="Ex: 500"
                                />
                            </div>
                        </div>

                        <DialogFooter>
                            <Button
                                type="button"
                                variant="outline"
                                onClick={() =>
                                    setIsEditSubcategoryDialogOpen(false)
                                }
                                disabled={processingSubcategory}
                            >
                                Annuler
                            </Button>
                            <Button
                                type="submit"
                                disabled={processingSubcategory}
                            >
                                {processingSubcategory
                                    ? 'Enregistrement...'
                                    : 'Enregistrer'}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            {/* Subcategory Delete Confirmation */}
            <ConfirmDialog
                open={isDeleteSubcategoryDialogOpen}
                onOpenChange={setIsDeleteSubcategoryDialogOpen}
                onConfirm={handleDeleteSubcategory}
                title="Supprimer cette sous-catégorie ?"
                description={`Êtes-vous sûr de vouloir supprimer la sous-catégorie "${deletingSubcategory?.name}" ? Cette action est irréversible.`}
                confirmText="Supprimer"
                cancelText="Annuler"
                variant="danger"
            />
        </AppLayout>
    );
}
