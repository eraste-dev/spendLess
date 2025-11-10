import { Button } from '@/components/ui/button';
import { ScrollArea } from '@/components/ui/scroll-area';
import { cn } from '@/lib/utils';
import { ChevronLeft, ChevronRight } from 'lucide-react';
import { useState } from 'react';

interface MonthYearPickerProps {
    selected: Date;
    onSelect: (date: Date) => void;
    startYear?: number;
    endYear?: number;
    isMonthAvailable?: (year: number, month: number) => boolean;
    locale?: 'fr' | 'en';
}

const MONTHS_FR = ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'];

const MONTHS_EN = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

export function MonthYearPicker({ selected, onSelect, startYear = 2020, endYear = new Date().getFullYear() + 5, isMonthAvailable, locale = 'fr' }: MonthYearPickerProps) {
    const [viewYear, setViewYear] = useState(selected.getFullYear());
    const months = locale === 'fr' ? MONTHS_FR : MONTHS_EN;
    const selectedMonth = selected.getMonth();
    const selectedYear = selected.getFullYear();

    const handleMonthClick = (monthIndex: number) => {
        const newDate = new Date(viewYear, monthIndex, 1);
        if (!isMonthAvailable || isMonthAvailable(viewYear, monthIndex + 1)) {
            onSelect(newDate);
        }
    };

    const handlePreviousYear = () => {
        if (viewYear > startYear) {
            setViewYear(viewYear - 1);
        }
    };

    const handleNextYear = () => {
        if (viewYear < endYear) {
            setViewYear(viewYear + 1);
        }
    };

    const now = new Date();
    const currentMonth = now.getMonth();
    const currentYear = now.getFullYear();

    return (
        <div className="w-80 p-4">
            {/* Year selector */}
            <div className="mb-4 flex items-center justify-between">
                <Button variant="ghost" size="icon" onClick={handlePreviousYear} disabled={viewYear <= startYear} className="size-8">
                    <ChevronLeft className="size-4" />
                </Button>
                <div className="text-center">
                    <h3 className="text-lg font-semibold">{viewYear}</h3>
                </div>
                <Button variant="ghost" size="icon" onClick={handleNextYear} disabled={viewYear >= endYear} className="size-8">
                    <ChevronRight className="size-4" />
                </Button>
            </div>

            {/* Months grid */}
            <div className="grid grid-cols-3 gap-2">
                {months.map((month, index) => {
                    const isSelected = viewYear === selectedYear && index === selectedMonth;
                    const isCurrent = viewYear === currentYear && index === currentMonth;
                    const isAvailable = !isMonthAvailable || isMonthAvailable(viewYear, index + 1);

                    return (
                        <Button
                            key={month}
                            variant={isSelected ? 'default' : 'ghost'}
                            onClick={() => handleMonthClick(index)}
                            disabled={!isAvailable}
                            className={cn(
                                'h-14 text-sm font-medium transition-all',
                                isSelected && 'shadow-md ring-2 ring-primary/20',
                                isCurrent && !isSelected && 'border border-primary/50',
                                !isAvailable && 'opacity-40 cursor-not-allowed'
                            )}
                        >
                            {month.slice(0, 4)}
                        </Button>
                    );
                })}
            </div>

            {/* Year list for quick jump */}
            <div className="mt-4 border-t pt-4">
                <p className="mb-2 text-xs font-medium text-muted-foreground">Aller à l'année</p>
                <ScrollArea className="h-32">
                    <div className="grid grid-cols-4 gap-1">
                        {Array.from({ length: endYear - startYear + 1 }, (_, i) => startYear + i).map((year) => (
                            <Button
                                key={year}
                                variant={viewYear === year ? 'default' : 'ghost'}
                                size="sm"
                                onClick={() => setViewYear(year)}
                                className={cn('h-8 text-xs', viewYear === year && 'font-bold')}
                            >
                                {year}
                            </Button>
                        ))}
                    </div>
                </ScrollArea>
            </div>
        </div>
    );
}
