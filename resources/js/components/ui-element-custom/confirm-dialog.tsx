import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
} from '@/components/ui/alert-dialog';
import { AlertTriangle, Trash2, Info, AlertCircle } from 'lucide-react';

type ConfirmDialogVariant = 'danger' | 'warning' | 'info';

interface ConfirmDialogProps {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    onConfirm: () => void;
    title?: string;
    description?: string;
    confirmText?: string;
    cancelText?: string;
    variant?: ConfirmDialogVariant;
    isLoading?: boolean;
}

const variantConfig = {
    danger: {
        icon: Trash2,
        iconColor: 'text-red-500',
        buttonClass:
            'bg-red-500 hover:bg-red-600 text-white focus:ring-red-500',
        defaultTitle: 'Confirmer la suppression',
        defaultDescription:
            'Cette action est irréversible. Êtes-vous sûr de vouloir continuer ?',
    },
    warning: {
        icon: AlertTriangle,
        iconColor: 'text-yellow-500',
        buttonClass:
            'bg-yellow-500 hover:bg-yellow-600 text-white focus:ring-yellow-500',
        defaultTitle: 'Attention',
        defaultDescription:
            'Veuillez confirmer cette action avant de continuer.',
    },
    info: {
        icon: Info,
        iconColor: 'text-blue-500',
        buttonClass:
            'bg-blue-500 hover:bg-blue-600 text-white focus:ring-blue-500',
        defaultTitle: 'Confirmer',
        defaultDescription: 'Êtes-vous sûr de vouloir continuer ?',
    },
};

export function ConfirmDialog({
    open,
    onOpenChange,
    onConfirm,
    title,
    description,
    confirmText = 'Confirmer',
    cancelText = 'Annuler',
    variant = 'danger',
    isLoading = false,
}: ConfirmDialogProps) {
    const config = variantConfig[variant];
    const Icon = config.icon;

    const handleConfirm = () => {
        onConfirm();
        onOpenChange(false);
    };

    return (
        <AlertDialog open={open} onOpenChange={onOpenChange}>
            <AlertDialogContent className="sm:max-w-[425px]">
                <AlertDialogHeader>
                    <div className="flex items-center gap-3">
                        <div
                            className={`flex size-10 items-center justify-center rounded-full bg-muted ${config.iconColor}`}
                        >
                            <Icon className="size-5" />
                        </div>
                        <AlertDialogTitle>
                            {title || config.defaultTitle}
                        </AlertDialogTitle>
                    </div>
                    <AlertDialogDescription className="pt-2">
                        {description || config.defaultDescription}
                    </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                    <AlertDialogCancel disabled={isLoading}>
                        {cancelText}
                    </AlertDialogCancel>
                    <AlertDialogAction
                        onClick={handleConfirm}
                        className={config.buttonClass}
                        disabled={isLoading}
                    >
                        {isLoading ? 'Traitement...' : confirmText}
                    </AlertDialogAction>
                </AlertDialogFooter>
            </AlertDialogContent>
        </AlertDialog>
    );
}

// Hook personnalisé pour gérer facilement le dialog de confirmation
export function useConfirmDialog() {
    const [isOpen, setIsOpen] = React.useState(false);
    const [config, setConfig] = React.useState<Partial<ConfirmDialogProps>>(
        {},
    );

    const confirm = (options: Partial<ConfirmDialogProps>) => {
        return new Promise<boolean>((resolve) => {
            setConfig({
                ...options,
                onConfirm: () => {
                    options.onConfirm?.();
                    resolve(true);
                    setIsOpen(false);
                },
            });
            setIsOpen(true);
        });
    };

    const ConfirmDialogComponent = () => (
        <ConfirmDialog
            open={isOpen}
            onOpenChange={setIsOpen}
            onConfirm={() => config.onConfirm?.()}
            {...config}
        />
    );

    return {
        confirm,
        ConfirmDialog: ConfirmDialogComponent,
    };
}

// Import React pour le hook
import React from 'react';
