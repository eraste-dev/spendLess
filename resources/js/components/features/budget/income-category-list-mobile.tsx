import { Button } from '@/components/ui/button';
import { Plus, Inbox } from 'lucide-react';
import { IncomeCategoryCardMobile } from './income-category-card-mobile';
import { IncomeCategory } from '@/types/budget';

interface IncomeCategoryListMobileProps {
    categories: IncomeCategory[];
    onEdit: (category: IncomeCategory) => void;
    onDelete: (category: IncomeCategory) => void;
    onCreate: () => void;
}

export function IncomeCategoryListMobile({
    categories,
    onEdit,
    onDelete,
    onCreate,
}: IncomeCategoryListMobileProps) {
    if (categories.length === 0) {
        return (
            <div className="flex flex-col items-center justify-center py-16 px-4 text-center">
                <div className="flex size-16 items-center justify-center rounded-full bg-muted mb-4">
                    <Inbox className="size-8 text-muted-foreground" />
                </div>
                <h3 className="text-lg font-semibold mb-2">
                    Aucune catégorie
                </h3>
                <p className="text-sm text-muted-foreground mb-6 max-w-sm">
                    Commencez par créer votre première catégorie de revenus
                </p>
                <Button onClick={onCreate} size="lg" className="rounded-full">
                    <Plus className="mr-2 size-5" />
                    Créer une catégorie
                </Button>
            </div>
        );
    }

    return (
        <div className="space-y-3">
            {/* Header avec compteur */}
            <div className="flex items-center justify-between px-1">
                <p className="text-sm text-muted-foreground">
                    {categories.length}{' '}
                    {categories.length > 1 ? 'catégories' : 'catégorie'}
                </p>
            </div>

            {/* Liste des catégories */}
            <div className="space-y-3">
                {categories.map((category) => (
                    <IncomeCategoryCardMobile
                        key={category.id}
                        category={category}
                        onEdit={onEdit}
                        onDelete={onDelete}
                    />
                ))}
            </div>
        </div>
    );
}
