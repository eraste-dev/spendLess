import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { Textarea } from '@/components/ui/textarea';
import { X } from 'lucide-react';
import { FormEventHandler } from 'react';

interface IncomeCategoryFormData {
    name: string;
    description: string;
    amount: string;
    is_monthly: boolean;
    income_date: string;
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
            <DialogContent className="max-h-[90vh] overflow-y-auto p-0 sm:max-w-[500px]">
                <form onSubmit={onSubmit} className="flex flex-col">
                    {/* Header collant */}
                    <div className="sticky top-0 z-10 border-b bg-background px-4 py-4 sm:px-6">
                        <div className="flex items-center justify-between">
                            <div className="flex-1">
                                <DialogTitle className="text-lg sm:text-xl">
                                    {isCreate
                                        ? 'Nouvelle catégorie'
                                        : 'Modifier la catégorie'}
                                </DialogTitle>
                                <DialogDescription className="mt-1 text-sm">
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
                                className="ml-2 shrink-0"
                            >
                                <X className="size-5" />
                            </Button>
                        </div>
                    </div>

                    {/* Contenu du formulaire avec padding */}
                    <div className="space-y-5 px-4 py-6 sm:px-6">
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

                        {/* Montant */}
                        <div className="space-y-2">
                            <Label
                                htmlFor={`${mode}-amount`}
                                className="text-base font-medium"
                            >
                                Montant estimé
                            </Label>
                            <Input
                                id={`${mode}-amount`}
                                type="number"
                                step="0.01"
                                min="0"
                                value={data.amount}
                                onChange={(e) =>
                                    setData('amount', e.target.value)
                                }
                                placeholder="0.00"
                                className={`h-12 text-base ${
                                    errors.amount ? 'border-red-500' : ''
                                }`}
                            />
                            {errors.amount && (
                                <p className="text-sm text-red-500">
                                    {errors.amount}
                                </p>
                            )}
                            <p className="text-xs text-muted-foreground">
                                Optionnel - Montant prévu pour cette source de
                                revenu
                            </p>
                        </div>

                        {/* Switch Mensuel */}
                        <div className="flex items-center justify-between space-x-3 rounded-lg border p-4">
                            <div className="flex-1 space-y-1">
                                <Label
                                    htmlFor={`${mode}-is-monthly`}
                                    className="cursor-pointer text-base font-medium"
                                >
                                    Revenu mensuel
                                </Label>
                                <p className="text-sm text-muted-foreground">
                                    Ce revenu est perçu chaque mois
                                </p>
                            </div>
                            <Switch
                                id={`${mode}-is-monthly`}
                                checked={data.is_monthly}
                                onCheckedChange={(checked) =>
                                    setData('is_monthly', checked)
                                }
                            />
                        </div>

                        {/* Date du revenu - Visible uniquement si non mensuel */}
                        {!data.is_monthly && (
                            <div className="space-y-2">
                                <Label
                                    htmlFor={`${mode}-income-date`}
                                    className="text-base font-medium"
                                >
                                    Date du revenu *
                                </Label>
                                <Input
                                    id={`${mode}-income-date`}
                                    type="date"
                                    value={data.income_date}
                                    onChange={(e) =>
                                        setData('income_date', e.target.value)
                                    }
                                    className={`h-12 text-base ${
                                        errors.income_date
                                            ? 'border-red-500'
                                            : ''
                                    }`}
                                />
                                {errors.income_date && (
                                    <p className="text-sm text-red-500">
                                        {errors.income_date}
                                    </p>
                                )}
                                <p className="text-xs text-muted-foreground">
                                    Date à laquelle ce revenu sera perçu
                                </p>
                            </div>
                        )}

                        {/* Couleur */}
                        <div className="space-y-2">
                            <Label
                                htmlFor={`${mode}-color`}
                                className="text-base font-medium"
                            >
                                Couleur de la catégorie
                            </Label>
                            <div className="flex flex-col gap-3">
                                {/* Color preview and picker button */}
                                <div className="flex items-center gap-3">
                                    <div
                                        className="relative size-16 shrink-0 cursor-pointer overflow-hidden rounded-lg border-2 shadow-sm transition-transform hover:scale-105 active:scale-95"
                                        style={{ backgroundColor: data.color }}
                                        onClick={() =>
                                            document
                                                .getElementById(
                                                    `${mode}-color-picker`,
                                                )
                                                ?.click()
                                        }
                                    >
                                        <input
                                            id={`${mode}-color-picker`}
                                            type="color"
                                            value={data.color}
                                            onChange={(e) =>
                                                setData('color', e.target.value)
                                            }
                                            className="absolute inset-0 size-full cursor-pointer opacity-0"
                                        />
                                    </div>
                                    <div className="flex-1">
                                        <p className="text-sm font-medium">
                                            Choisir une couleur
                                        </p>
                                        <p className="text-xs text-muted-foreground">
                                            Cliquez sur le carré pour
                                            sélectionner
                                        </p>
                                    </div>
                                </div>

                                {/* Predefined color palette */}
                                <div className="space-y-2">
                                    <p className="text-sm text-muted-foreground">
                                        Couleurs suggérées
                                    </p>
                                    <div className="grid grid-cols-8 gap-2">
                                        {[
                                            '#10b981',
                                            '#3b82f6',
                                            '#8b5cf6',
                                            '#f59e0b',
                                            '#ef4444',
                                            '#ec4899',
                                            '#06b6d4',
                                            '#84cc16',
                                            '#f97316',
                                            '#6366f1',
                                            '#14b8a6',
                                            '#a855f7',
                                            '#f43f5e',
                                            '#0ea5e9',
                                            '#22c55e',
                                            '#6b7280',
                                        ].map((color) => (
                                            <button
                                                key={color}
                                                type="button"
                                                onClick={() =>
                                                    setData('color', color)
                                                }
                                                className={`size-10 rounded-lg border-2 transition-all hover:scale-110 active:scale-95 ${
                                                    data.color === color
                                                        ? 'border-foreground ring-2 ring-foreground ring-offset-2'
                                                        : 'border-border'
                                                }`}
                                                style={{
                                                    backgroundColor: color,
                                                }}
                                                title={color}
                                            />
                                        ))}
                                    </div>
                                </div>
                            </div>
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
                                className="resize-none text-base"
                            />
                            <p className="text-xs text-muted-foreground">
                                Optionnel
                            </p>
                        </div>
                    </div>

                    {/* Footer collant avec actions */}
                    <div className="sticky bottom-0 mt-auto border-t bg-background px-4 py-4 sm:px-6">
                        <div className="flex gap-3">
                            <Button
                                type="button"
                                variant="outline"
                                onClick={onClose}
                                disabled={processing}
                                className="h-12 flex-1 text-base"
                            >
                                Annuler
                            </Button>
                            <Button
                                type="submit"
                                disabled={processing}
                                className="h-12 flex-1 text-base"
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
