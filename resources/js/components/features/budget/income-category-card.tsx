import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { IncomeCategory } from '@/types/budget';
import { Edit, Eye, EyeOff, Trash2 } from 'lucide-react';

interface IncomeCategoryCardProps {
    category: IncomeCategory;
    onEdit: (category: IncomeCategory) => void;
    onDelete: (category: IncomeCategory) => void;
}

export function IncomeCategoryCard({
    category,
    onEdit,
    onDelete,
}: IncomeCategoryCardProps) {
    return (
        <div className="flex items-center justify-between rounded-lg border bg-card p-4 hover:bg-muted/50">
            <div className="flex items-center gap-4">
                <div
                    className="size-10 rounded-lg border"
                    style={{
                        backgroundColor: category.color,
                    }}
                />
                <div className="flex-1">
                    <div className="flex items-center gap-2">
                        <h3 className="font-semibold">{category.name}</h3>
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
                    </div>
                    {category.description && (
                        <p className="text-sm text-muted-foreground">
                            {category.description}
                        </p>
                    )}
                </div>
            </div>
            <div className="flex gap-2">
                <Button
                    variant="outline"
                    size="sm"
                    onClick={() => onEdit(category)}
                >
                    <Edit className="mr-2 size-4" />
                    Modifier
                </Button>
                <Button
                    variant="outline"
                    size="sm"
                    className="text-destructive hover:text-destructive"
                    onClick={() => onDelete(category)}
                >
                    <Trash2 className="mr-2 size-4" />
                    Supprimer
                </Button>
            </div>
        </div>
    );
}
