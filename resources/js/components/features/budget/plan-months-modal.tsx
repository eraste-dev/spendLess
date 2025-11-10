import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Checkbox } from '@/components/ui/checkbox';
import { Label } from '@/components/ui/label';
import { ScrollArea } from '@/components/ui/scroll-area';
import { router } from '@inertiajs/react';
import { useState } from 'react';
import { Calendar, Loader2 } from 'lucide-react';

interface PlanMonthsModalProps {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    currentYear: number;
    currentMonth: number;
    plannedMonths: { year: number; month: number }[];
}

export function PlanMonthsModal({
    open,
    onOpenChange,
    currentYear,
    currentMonth,
    plannedMonths,
}: PlanMonthsModalProps) {
    const [selectedMonths, setSelectedMonths] = useState<
        { year: number; month: number }[]
    >([]);
    const [isSubmitting, setIsSubmitting] = useState(false);

    // Generate future months (next 12 months from current date)
    const generateFutureMonths = () => {
        const now = new Date();
        const currentDate = new Date(now.getFullYear(), now.getMonth(), 1);
        const months: { year: number; month: number; name: string }[] = [];

        for (let i = 1; i <= 12; i++) {
            const futureDate = new Date(
                currentDate.getFullYear(),
                currentDate.getMonth() + i,
                1
            );
            const year = futureDate.getFullYear();
            const month = futureDate.getMonth() + 1;

            // Skip if already planned
            const isPlanned = plannedMonths.some(
                (pm) => pm.year === year && pm.month === month
            );

            if (!isPlanned) {
                months.push({
                    year,
                    month,
                    name: futureDate.toLocaleDateString('fr-FR', {
                        month: 'long',
                        year: 'numeric',
                    }),
                });
            }
        }

        return months;
    };

    const availableMonths = generateFutureMonths();

    const handleToggleMonth = (year: number, month: number) => {
        setSelectedMonths((prev) => {
            const exists = prev.some(
                (m) => m.year === year && m.month === month
            );
            if (exists) {
                return prev.filter(
                    (m) => !(m.year === year && m.month === month)
                );
            } else {
                return [...prev, { year, month }];
            }
        });
    };

    const handleSubmit = () => {
        if (selectedMonths.length === 0) return;

        setIsSubmitting(true);

        router.post(
            '/budget/incomes/plan-months',
            {
                source_year: currentYear,
                source_month: currentMonth,
                target_months: selectedMonths,
            },
            {
                preserveState: true,
                preserveScroll: true,
                onSuccess: () => {
                    setSelectedMonths([]);
                    onOpenChange(false);
                },
                onFinish: () => {
                    setIsSubmitting(false);
                },
            }
        );
    };

    const handleClose = () => {
        if (!isSubmitting) {
            setSelectedMonths([]);
            onOpenChange(false);
        }
    };

    return (
        <Dialog open={open} onOpenChange={handleClose}>
            <DialogContent className="sm:max-w-[500px]">
                <DialogHeader>
                    <DialogTitle className="flex items-center gap-2">
                        <Calendar className="size-5" />
                        Planifier les revenus
                    </DialogTitle>
                    <DialogDescription>
                        Sélectionnez les mois futurs où vous souhaitez dupliquer
                        vos revenus actuels. Les revenus du{' '}
                        {new Date(currentYear, currentMonth - 1, 1).toLocaleDateString('fr-FR', {
                            month: 'long',
                            year: 'numeric',
                        })}{' '}
                        seront copiés.
                    </DialogDescription>
                </DialogHeader>

                {availableMonths.length === 0 ? (
                    <div className="py-8 text-center text-muted-foreground">
                        <Calendar className="mx-auto mb-4 size-12 opacity-50" />
                        <p>Aucun mois disponible pour la planification.</p>
                        <p className="mt-1 text-sm">
                            Tous les mois futurs sont déjà planifiés.
                        </p>
                    </div>
                ) : (
                    <>
                        <ScrollArea className="max-h-[400px] pr-4">
                            <div className="space-y-3">
                                {availableMonths.map((month) => {
                                    const isSelected = selectedMonths.some(
                                        (m) =>
                                            m.year === month.year &&
                                            m.month === month.month
                                    );

                                    return (
                                        <div
                                            key={`${month.year}-${month.month}`}
                                            className="flex items-center space-x-3 rounded-lg border p-3 hover:bg-muted/50"
                                        >
                                            <Checkbox
                                                id={`month-${month.year}-${month.month}`}
                                                checked={isSelected}
                                                onCheckedChange={() =>
                                                    handleToggleMonth(
                                                        month.year,
                                                        month.month
                                                    )
                                                }
                                                disabled={isSubmitting}
                                            />
                                            <Label
                                                htmlFor={`month-${month.year}-${month.month}`}
                                                className="flex-1 cursor-pointer capitalize"
                                            >
                                                {month.name}
                                            </Label>
                                        </div>
                                    );
                                })}
                            </div>
                        </ScrollArea>

                        <DialogFooter>
                            <Button
                                variant="outline"
                                onClick={handleClose}
                                disabled={isSubmitting}
                            >
                                Annuler
                            </Button>
                            <Button
                                onClick={handleSubmit}
                                disabled={
                                    selectedMonths.length === 0 || isSubmitting
                                }
                            >
                                {isSubmitting && (
                                    <Loader2 className="mr-2 size-4 animate-spin" />
                                )}
                                Planifier {selectedMonths.length > 0 && `(${selectedMonths.length})`}
                            </Button>
                        </DialogFooter>
                    </>
                )}
            </DialogContent>
        </Dialog>
    );
}
