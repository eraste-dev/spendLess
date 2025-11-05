import { Badge } from '@/components/ui/badge';
import { IncomeCategory } from '@/types/budget';
import { Edit, Eye, EyeOff, Trash2, MoreVertical } from 'lucide-react';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Button } from '@/components/ui/button';

interface IncomeCategoryCardMobileProps {
    category: IncomeCategory;
    onEdit: (category: IncomeCategory) => void;
    onDelete: (category: IncomeCategory) => void;
}

export function IncomeCategoryCardMobile({
    category,
    onEdit,
    onDelete,
}: IncomeCategoryCardMobileProps) {
    return (
        <div className="flex items-center gap-3 rounded-xl border bg-card p-4 active:bg-muted/50 transition-colors">
            {/* Color Indicator */}
            <div
                className="size-12 shrink-0 rounded-lg border-2"
                style={{
                    backgroundColor: category.color,
                }}
            />

            {/* Content */}
            <div className="flex-1 min-w-0">
                <div className="flex items-center gap-2 mb-1">
                    <h3 className="font-semibold text-base truncate">
                        {category.name}
                    </h3>
                    {category.is_active ? (
                        <Badge
                            variant="default"
                            className="shrink-0 bg-green-500 hover:bg-green-600 text-xs"
                        >
                            <Eye className="mr-1 size-3" />
                            Actif
                        </Badge>
                    ) : (
                        <Badge variant="secondary" className="shrink-0 text-xs">
                            <EyeOff className="mr-1 size-3" />
                            Inactif
                        </Badge>
                    )}
                </div>
                {category.description && (
                    <p className="text-sm text-muted-foreground line-clamp-2">
                        {category.description}
                    </p>
                )}
            </div>

            {/* Actions - Mobile Dropdown */}
            <DropdownMenu>
                <DropdownMenuTrigger asChild>
                    <Button
                        variant="ghost"
                        size="icon"
                        className="shrink-0 size-9"
                    >
                        <MoreVertical className="size-5" />
                    </Button>
                </DropdownMenuTrigger>
                <DropdownMenuContent align="end" className="w-48">
                    <DropdownMenuItem
                        onClick={() => onEdit(category)}
                        className="cursor-pointer"
                    >
                        <Edit className="mr-2 size-4" />
                        Modifier
                    </DropdownMenuItem>
                    <DropdownMenuItem
                        onClick={() => onDelete(category)}
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
