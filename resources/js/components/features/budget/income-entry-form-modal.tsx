import { IncomeEntry, Income } from '@/types/budget';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { Switch } from '@/components/ui/switch';
import { useForm, router } from '@inertiajs/react';
import { FormEventHandler, useEffect } from 'react';

interface IncomeEntryFormModalProps {
    isOpen: boolean;
    onClose: () => void;
    incomeEntry: IncomeEntry | null;
    incomes: Income[];
    currentYear?: number;
    currentMonth?: number;
}

export function IncomeEntryFormModal({
    isOpen,
    onClose,
    incomeEntry,
    incomes,
    currentYear,
    currentMonth,
}: IncomeEntryFormModalProps) {
    const isEditing = !!incomeEntry;

    // Generate default date based on current year/month (first day of the month)
    const getDefaultDate = () => {
        if (incomeEntry?.income_date) {
            return incomeEntry.income_date;
        }
        if (currentYear && currentMonth) {
            // Format: YYYY-MM-DD with first day of the month
            return `${currentYear}-${String(currentMonth).padStart(2, '0')}-01`;
        }
        return new Date().toISOString().split('T')[0];
    };

    const { data, setData, post, put, processing, errors, reset } = useForm({
        income_id: incomeEntry?.income_id?.toString() || '',
        description: incomeEntry?.description || '',
        amount: incomeEntry?.amount?.toString() || '',
        income_date: getDefaultDate(),
        is_recurring: incomeEntry?.is_recurring || false,
        recurrence_day: incomeEntry?.recurrence_day?.toString() || '',
    });

    // Reset form data when incomeEntry or month changes
    useEffect(() => {
        if (isOpen) {
            // Generate date based on context
            const defaultDate = incomeEntry?.income_date || (currentYear && currentMonth
                ? `${currentYear}-${String(currentMonth).padStart(2, '0')}-01`
                : new Date().toISOString().split('T')[0]);

            setData({
                income_id: incomeEntry?.income_id?.toString() || '',
                description: incomeEntry?.description || '',
                amount: incomeEntry?.amount?.toString() || '',
                income_date: defaultDate,
                is_recurring: incomeEntry?.is_recurring || false,
                recurrence_day: incomeEntry?.recurrence_day?.toString() || '',
            });
        }
    }, [incomeEntry, isOpen, currentYear, currentMonth]);

    const handleSubmit: FormEventHandler = (e) => {
        e.preventDefault();

        if (isEditing) {
            put(`/budget/income-entries/${incomeEntry.id}`, {
                onSuccess: () => {
                    reset();
                    onClose();
                },
            });
        } else {
            post('/budget/income-entries', {
                onSuccess: () => {
                    reset();
                    onClose();
                },
            });
        }
    };

    const handleClose = () => {
        reset();
        onClose();
    };

    return (
        <Dialog open={isOpen} onOpenChange={handleClose}>
            <DialogContent className="sm:max-w-[500px] max-h-[90vh] overflow-y-auto p-0">
                <form onSubmit={handleSubmit} className="flex flex-col">
                    {/* Sticky Header */}
                    <div className="sticky top-0 bg-background border-b px-4 sm:px-6 py-4 z-10">
                        <DialogHeader>
                            <DialogTitle>
                                {isEditing ? 'Modifier le revenu' : 'Ajouter un revenu'}
                            </DialogTitle>
                            <DialogDescription>
                                {isEditing
                                    ? 'Modifiez les informations du revenu'
                                    : 'Ajoutez un nouveau revenu à votre budget'}
                            </DialogDescription>
                        </DialogHeader>
                    </div>

                    {/* Scrollable Content */}
                    <div className="space-y-5 px-4 sm:px-6 py-6">
                        {/* Income */}
                        <div className="space-y-2">
                            <Label htmlFor="income_id">Revenu *</Label>
                            <Select
                                value={data.income_id}
                                onValueChange={(value) =>
                                    setData('income_id', value)
                                }
                            >
                                <SelectTrigger className="h-12 text-base">
                                    <SelectValue placeholder="Sélectionner un revenu" />
                                </SelectTrigger>
                                <SelectContent>
                                    {incomes.map((income) => (
                                        <SelectItem
                                            key={income.id}
                                            value={income.id.toString()}
                                        >
                                            {income.name}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                            {errors.income_id && (
                                <p className="text-sm text-destructive">
                                    {errors.income_id}
                                </p>
                            )}
                        </div>

                        {/* Amount */}
                        <div className="space-y-2">
                            <Label htmlFor="amount">Montant *</Label>
                            <Input
                                id="amount"
                                type="number"
                                step="0.01"
                                min="0"
                                placeholder="0.00"
                                value={data.amount}
                                onChange={(e) => setData('amount', e.target.value)}
                                className="h-12 text-base"
                            />
                            {errors.amount && (
                                <p className="text-sm text-destructive">{errors.amount}</p>
                            )}
                        </div>

                        {/* Date */}
                        <div className="space-y-2">
                            <Label htmlFor="income_date">Date *</Label>
                            <Input
                                id="income_date"
                                type="date"
                                value={data.income_date}
                                onChange={(e) => setData('income_date', e.target.value)}
                                className="h-12 text-base"
                            />
                            {errors.income_date && (
                                <p className="text-sm text-destructive">
                                    {errors.income_date}
                                </p>
                            )}
                        </div>

                        {/* Description */}
                        <div className="space-y-2">
                            <Label htmlFor="description">Description</Label>
                            <Textarea
                                id="description"
                                placeholder="Détails sur ce revenu..."
                                value={data.description}
                                onChange={(e) => setData('description', e.target.value)}
                                className="min-h-24 text-base resize-none"
                            />
                            {errors.description && (
                                <p className="text-sm text-destructive">
                                    {errors.description}
                                </p>
                            )}
                        </div>

                        {/* Recurring Switch */}
                        <div className="flex items-center justify-between space-x-2 rounded-lg border p-4">
                            <div className="space-y-0.5">
                                <Label htmlFor="is_recurring" className="text-base">
                                    Revenu mensuel
                                </Label>
                                <p className="text-sm text-muted-foreground">
                                    Ce revenu se répète chaque mois
                                </p>
                            </div>
                            <Switch
                                id="is_recurring"
                                checked={data.is_recurring}
                                onCheckedChange={(checked) =>
                                    setData('is_recurring', checked)
                                }
                            />
                        </div>

                        {/* Recurrence Day (conditional) */}
                        {data.is_recurring && (
                            <div className="space-y-2">
                                <Label htmlFor="recurrence_day">
                                    Jour de récurrence (1-31)
                                </Label>
                                <Input
                                    id="recurrence_day"
                                    type="number"
                                    min="1"
                                    max="31"
                                    placeholder="15"
                                    value={data.recurrence_day}
                                    onChange={(e) =>
                                        setData('recurrence_day', e.target.value)
                                    }
                                    className="h-12 text-base"
                                />
                                <p className="text-xs text-muted-foreground">
                                    Le jour du mois où ce revenu sera créé automatiquement
                                </p>
                                {errors.recurrence_day && (
                                    <p className="text-sm text-destructive">
                                        {errors.recurrence_day}
                                    </p>
                                )}
                            </div>
                        )}
                    </div>

                    {/* Sticky Footer */}
                    <div className="sticky bottom-0 bg-background border-t px-4 sm:px-6 py-4">
                        <div className="flex gap-3">
                            <Button
                                type="button"
                                variant="outline"
                                onClick={handleClose}
                                className="flex-1 h-12"
                                disabled={processing}
                            >
                                Annuler
                            </Button>
                            <Button
                                type="submit"
                                className="flex-1 h-12"
                                disabled={processing}
                            >
                                {processing
                                    ? 'Enregistrement...'
                                    : isEditing
                                      ? 'Modifier'
                                      : 'Ajouter'}
                            </Button>
                        </div>
                    </div>
                </form>
            </DialogContent>
        </Dialog>
    );
}
