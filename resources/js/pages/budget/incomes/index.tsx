import { Income, IncomeCategory, UserSettings } from '@/types/budget';
import MobileHeaderLayout from '@/layouts/mobile-header-layout';
import { Head } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Plus } from 'lucide-react';
import { RevenueHeader } from '@/components/features/budget/revenue-header';
import { IncomeListMobile } from '@/components/features/budget/income-list-mobile';
import { useState } from 'react';

interface IncomesIndexProps {
    incomes: Income[];
    total: number;
    categories: IncomeCategory[];
    settings: UserSettings;
    currentMonth: number;
    currentYear: number;
}

export default function IncomesIndex({
    incomes,
    total,
    categories,
    settings,
    currentMonth,
    currentYear,
}: IncomesIndexProps) {
    const [isCreating, setIsCreating] = useState(false);

    const handleCreate = () => {
        setIsCreating(true);
    };

    return (
        <MobileHeaderLayout>
            <Head title="Revenus" />

            <div className="px-4 py-6 md:py-8">
                {/* Page Title */}
                <div className="mb-6">
                    <h1 className="text-2xl sm:text-3xl font-bold">Revenus</h1>
                </div>

                {/* Revenue Header with Date and Total */}
                <RevenueHeader total={total} settings={settings} />

                {/* Income List */}
                <IncomeListMobile
                    incomes={incomes}
                    categories={categories}
                    settings={settings}
                    onCreate={handleCreate}
                />

                {/* FAB - Only show when there are incomes */}
                {incomes.length > 0 && (
                    <Button
                        onClick={handleCreate}
                        className="fixed bottom-6 right-6 size-14 rounded-full shadow-lg z-40"
                    >
                        <Plus className="size-6" />
                        <span className="sr-only">Ajouter un revenu</span>
                    </Button>
                )}
            </div>
        </MobileHeaderLayout>
    );
}
