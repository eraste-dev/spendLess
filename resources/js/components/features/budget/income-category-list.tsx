import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { IncomeCategory } from '@/types/budget';
import { Plus } from 'lucide-react';
import { IncomeCategoryCard } from './income-category-card';

interface IncomeCategoryListProps {
    categories: IncomeCategory[];
    onEdit: (category: IncomeCategory) => void;
    onDelete: (category: IncomeCategory) => void;
    onCreate: () => void;
}

export function IncomeCategoryList({
    categories,
    onEdit,
    onDelete,
    onCreate,
}: IncomeCategoryListProps) {
    if (categories.length === 0) {
        return (
            <Card>
                <CardContent className="flex flex-col items-center justify-center py-12 text-center">
                    <p className="text-muted-foreground">
                        Aucune catégorie de revenus
                    </p>
                    <p className="text-sm text-muted-foreground">
                        Commencez par créer votre première catégorie
                    </p>
                    <Button className="mt-4" onClick={onCreate}>
                        <Plus className="mr-2 size-4" />
                        Créer une catégorie
                    </Button>
                </CardContent>
            </Card>
        );
    }

    return (
        <Card>
            <CardHeader>
                <CardTitle>Toutes les catégories</CardTitle>
                <CardDescription>
                    {categories.length} catégorie(s) de revenus
                </CardDescription>
            </CardHeader>
            <CardContent>
                <div className="space-y-3">
                    {categories.map((category) => (
                        <IncomeCategoryCard
                            key={category.id}
                            category={category}
                            onEdit={onEdit}
                            onDelete={onDelete}
                        />
                    ))}
                </div>
            </CardContent>
        </Card>
    );
}
