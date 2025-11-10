import { IncomeEntryList } from '@/components/features/budget/income-entry-list';
import { MonthSelector } from '@/components/features/budget/month-selector';
import { PlanMonthsModal } from '@/components/features/budget/plan-months-modal';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import { formatCurrency } from '@/lib/currency';
import { Income, IncomeEntry, UserSettings } from '@/types/budget';
import { Head } from '@inertiajs/react';
import { CalendarPlus, Plus } from 'lucide-react';
import { useState } from 'react';

interface Props {
    incomes: Income[];
    incomeEntries: IncomeEntry[];
    settings: UserSettings;
    total: number;
    currentMonth: number;
    currentYear: number;
    plannedMonths: { year: number; month: number }[];
}

export default function Index({
    incomes,
    incomeEntries,
    settings,
    total,
    currentMonth,
    currentYear,
    plannedMonths,
}: Props) {
    const [isCreatingIncomeEntry, setIsCreatingIncomeEntry] = useState(false);
    const [isPlanningOpen, setIsPlanningOpen] = useState(false);

    const handleCreateIncomeEntry = () => {
        setIsCreatingIncomeEntry((prev) => !prev);
    };

    const handlePlanMonths = () => {
        setIsPlanningOpen(true);
    };

    return (
        <AppLayout>
            <Head title="Revenus" />

            {/* Page Header */}
            <div className="mb-6">
                <h1 className="mb-2 text-2xl font-bold tracking-tight sm:text-3xl">
                    Revenus
                </h1>
                <p className="text-sm text-muted-foreground sm:text-base">
                    Gérez vos entrées de revenus mensuels
                </p>
            </div>

            {/* Month Selector */}
            <div className="mb-6">
                <MonthSelector
                    currentYear={currentYear}
                    currentMonth={currentMonth}
                    plannedMonths={plannedMonths}
                />
            </div>

            {/* Total Income Display */}
            <div className="mb-6 rounded-xl border bg-gradient-to-br from-primary/5 to-primary/10 p-6">
                <div className="flex items-baseline justify-between gap-4">
                    <div className="flex-1">
                        <p className="mb-1 text-sm font-medium text-muted-foreground">
                            Total du mois
                        </p>
                        <div className="flex items-baseline gap-2">
                            <span className="text-3xl font-bold tracking-tight sm:text-4xl">
                                {formatCurrency(total, settings)}
                            </span>
                        </div>
                    </div>
                </div>
                <p className="mt-3 text-xs text-muted-foreground">
                    Somme des revenus pour ce mois
                </p>
            </div>

            {/* Income Entry List */}
            <IncomeEntryList
                incomeEntries={incomeEntries}
                incomes={incomes}
                settings={settings}
                isCreating={isCreatingIncomeEntry}
                onCreateClick={handleCreateIncomeEntry}
                currentYear={currentYear}
                currentMonth={currentMonth}
            />

            {/* Floating Action Buttons */}
            <div className="fixed bottom-6 right-6 z-40 flex flex-col gap-3">
                {/* Plan Months FAB - Secondary */}
                <Button
                    onClick={handlePlanMonths}
                    size="icon"
                    variant="outline"
                    className="size-12 rounded-full bg-background shadow-lg transition-all hover:scale-110 hover:shadow-xl"
                    title="Planifier des mois"
                >
                    <CalendarPlus className="size-5" />
                </Button>

                {/* Add Income FAB - Primary */}
                <Button
                    onClick={handleCreateIncomeEntry}
                    size="lg"
                    className="size-16 rounded-full shadow-lg transition-all hover:scale-110 hover:shadow-xl"
                    title="Ajouter un revenu"
                >
                    <Plus className="size-7" />
                </Button>
            </div>

            {/* Plan Months Modal */}
            <PlanMonthsModal
                open={isPlanningOpen}
                onOpenChange={setIsPlanningOpen}
                currentYear={currentYear}
                currentMonth={currentMonth}
                plannedMonths={plannedMonths}
            />
        </AppLayout>
    );
}
