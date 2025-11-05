import { Button } from '@/components/ui/button';
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
import { FormEventHandler } from 'react';
import { X } from 'lucide-react';

interface IncomeCategoryFormData {
    name: string;
    description: string;
    color: string;
}

interface IncomeCategoryFormModalMobileProps {
    isOpen: boolean;
    onClose: () => void;
    onSubmit: FormEventHandler;
    data: IncomeCategoryFormData;
    setData: (field: keyof IncomeCategoryFormData, value: string) => void;
    errors: Partial<Record<keyof IncomeCategoryFormData, string>>;
    processing: boolean;
    mode: 'create' | 'edit';
}

export function IncomeCategoryFormModalMobile({
    isOpen,
    onClose,
    onSubmit,
    data,
    setData,
    errors,
    processing,
    mode,
}: IncomeCategoryFormModalMobileProps) {
    const isCreate = mode === 'create';

    return (
        <Dialog open={isOpen} onOpenChange={onClose}>
            <DialogContent className="sm:max-w-[500px] max-h-[90vh] overflow-y-auto p-0">
                <form onSubmit={onSubmit} className="flex flex-col">
                    {/* Header collant */}
                    <div className="sticky top-0 bg-background border-b px-4 sm:px-6 py-4 z-10">
                        <div className="flex items-center justify-between">
                            <div className="flex-1">
                                <DialogTitle className="text-lg sm:text-xl">
                                    {isCreate
                                        ? 'Nouvelle catégorie'
                                        : 'Modifier la catégorie'}
                                </DialogTitle>
                                <DialogDescription className="text-sm mt-1">
                                    {isCreate
                                        ? 'Créez une catégorie pour classer vos revenus'
                                        : 'Modifiez les informations de la catégorie'}
                                </DialogDescription>
                            </div>
                            <Button
                                type="button"
                                variant="ghost"
                                size="icon"
                                onClick={onClose}
                                disabled={processing}
                                className="shrink-0 ml-2"
                            >
                                <X className="size-5" />
                            </Button>
                        </div>
                    </div>

                    {/* Contenu du formulaire avec padding */}
                    <div className="space-y-5 px-4 sm:px-6 py-6">
                        {/* Nom */}
                        <div className="space-y-2">
                            <Label
                                htmlFor={`${mode}-name`}
                                className="text-base font-medium"
                            >
                                Nom de la catégorie *
                            </Label>
                            <Input
                                id={`${mode}-name`}
                                value={data.name}
                                onChange={(e) =>
                                    setData('name', e.target.value)
                                }
                                placeholder="Ex: Salaire, Freelance..."
                                className={`h-12 text-base ${
                                    errors.name ? 'border-red-500' : ''
                                }`}
                                autoFocus
                            />
                            {errors.name && (
                                <p className="text-sm text-red-500">
                                    {errors.name}
                                </p>
                            )}
                        </div>

                        {/* Description */}
                        <div className="space-y-2">
                            <Label
                                htmlFor={`${mode}-description`}
                                className="text-base font-medium"
                            >
                                Description
                            </Label>
                            <Textarea
                                id={`${mode}-description`}
                                value={data.description}
                                onChange={(e) =>
                                    setData('description', e.target.value)
                                }
                                placeholder="Décrivez cette source de revenu..."
                                rows={3}
                                className="text-base resize-none"
                            />
                            <p className="text-xs text-muted-foreground">
                                Optionnel
                            </p>
                        </div>

                        {/* Couleur */}
                        <div className="space-y-2">
                            <Label
                                htmlFor={`${mode}-color`}
                                className="text-base font-medium"
                            >
                                Couleur
                            </Label>
                            <div className="flex gap-3">
                                <div
                                    className="size-12 shrink-0 rounded-lg border-2 cursor-pointer"
                                    style={{ backgroundColor: data.color }}
                                    onClick={() =>
                                        document
                                            .getElementById(
                                                `${mode}-color-picker`,
                                            )
                                            ?.click()
                                    }
                                />
                                <input
                                    id={`${mode}-color-picker`}
                                    type="color"
                                    value={data.color}
                                    onChange={(e) =>
                                        setData('color', e.target.value)
                                    }
                                    className="sr-only"
                                />
                                <Input
                                    type="text"
                                    value={data.color}
                                    onChange={(e) =>
                                        setData('color', e.target.value)
                                    }
                                    placeholder="#3b82f6"
                                    className="h-12 text-base flex-1"
                                />
                            </div>
                        </div>
                    </div>

                    {/* Footer collant avec actions */}
                    <div className="sticky bottom-0 bg-background border-t px-4 sm:px-6 py-4 mt-auto">
                        <div className="flex gap-3">
                            <Button
                                type="button"
                                variant="outline"
                                onClick={onClose}
                                disabled={processing}
                                className="flex-1 h-12 text-base"
                            >
                                Annuler
                            </Button>
                            <Button
                                type="submit"
                                disabled={processing}
                                className="flex-1 h-12 text-base"
                            >
                                {processing
                                    ? isCreate
                                        ? 'Création...'
                                        : 'Enregistrement...'
                                    : isCreate
                                      ? 'Créer'
                                      : 'Enregistrer'}
                            </Button>
                        </div>
                    </div>
                </form>
            </DialogContent>
        </Dialog>
    );
}
