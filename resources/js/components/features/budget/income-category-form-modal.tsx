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

interface IncomeCategoryFormData {
    name: string;
    description: string;
    color: string;
}

interface IncomeCategoryFormModalProps {
    isOpen: boolean;
    onClose: () => void;
    onSubmit: FormEventHandler;
    data: IncomeCategoryFormData;
    setData: (field: keyof IncomeCategoryFormData, value: string) => void;
    errors: Partial<Record<keyof IncomeCategoryFormData, string>>;
    processing: boolean;
    mode: 'create' | 'edit';
}

export function IncomeCategoryFormModal({
    isOpen,
    onClose,
    onSubmit,
    data,
    setData,
    errors,
    processing,
    mode,
}: IncomeCategoryFormModalProps) {
    const isCreate = mode === 'create';

    return (
        <Dialog open={isOpen} onOpenChange={onClose}>
            <DialogContent className="sm:max-w-[500px]">
                <form onSubmit={onSubmit}>
                    <DialogHeader>
                        <DialogTitle>
                            {isCreate
                                ? 'Nouvelle catégorie de revenu'
                                : 'Modifier la catégorie'}
                        </DialogTitle>
                        <DialogDescription>
                            {isCreate
                                ? 'Créez une nouvelle catégorie pour classer vos revenus'
                                : 'Modifiez les informations de la catégorie de revenu'}
                        </DialogDescription>
                    </DialogHeader>

                    <div className="space-y-4 py-4">
                        <div className="space-y-2">
                            <Label
                                htmlFor={`${mode}-name`}
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
                                className={
                                    errors.name ? 'border-red-500' : ''
                                }
                            />
                            {errors.name && (
                                <p className="text-sm text-red-500">
                                    {errors.name}
                                </p>
                            )}
                        </div>

                        <div className="space-y-2">
                            <Label htmlFor={`${mode}-description`}>
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
                            />
                        </div>

                        <div className="space-y-2">
                            <Label htmlFor={`${mode}-color`}>Couleur</Label>
                            <div className="flex gap-2">
                                <Input
                                    id={`${mode}-color`}
                                    type="color"
                                    value={data.color}
                                    onChange={(e) =>
                                        setData('color', e.target.value)
                                    }
                                    className="h-10 w-20 cursor-pointer"
                                />
                                <Input
                                    type="text"
                                    value={data.color}
                                    onChange={(e) =>
                                        setData('color', e.target.value)
                                    }
                                    placeholder="#3b82f6"
                                    className="flex-1"
                                />
                            </div>
                        </div>
                    </div>

                    <DialogFooter>
                        <Button
                            type="button"
                            variant="outline"
                            onClick={onClose}
                            disabled={processing}
                        >
                            Annuler
                        </Button>
                        <Button type="submit" disabled={processing}>
                            {processing
                                ? isCreate
                                    ? 'Création...'
                                    : 'Enregistrement...'
                                : isCreate
                                  ? 'Créer'
                                  : 'Enregistrer'}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}
