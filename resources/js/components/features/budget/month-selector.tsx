import { Button } from '@/components/ui/button';
import { MonthYearPicker } from '@/components/ui/month-year-picker';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { cn } from '@/lib/utils';
import { router } from '@inertiajs/react';
import { format } from 'date-fns';
import { fr } from 'date-fns/locale';
import { Calendar as CalendarIcon } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';

interface MonthSelectorProps {
    currentYear: number;
    currentMonth: number;
    plannedMonths: { year: number; month: number }[];
    className?: string;
}

export function MonthSelector({ currentYear, currentMonth, plannedMonths, className }: MonthSelectorProps) {
    const now = new Date();
    const scrollContainerRef = useRef<HTMLDivElement>(null);
    const [calendarOpen, setCalendarOpen] = useState(false);

    // Generate list of available months (24 months before and after current for better scrolling)
    const generateMonthsList = () => {
        const months: { year: number; month: number; date: Date }[] = [];
        const current = new Date(currentYear, currentMonth - 1, 1);

        // Generate 24 months before and 24 months after for smooth scrolling
        for (let i = -24; i <= 24; i++) {
            const date = new Date(current.getFullYear(), current.getMonth() + i, 1);
            months.push({
                year: date.getFullYear(),
                month: date.getMonth() + 1,
                date,
            });
        }

        return months;
    };

    const monthsList = generateMonthsList();

    // Check if the month is available (past, current, or planned future)
    const isMonthAvailable = (year: number, month: number): boolean => {
        const checkDate = new Date(year, month - 1, 1);
        const today = new Date(now.getFullYear(), now.getMonth(), 1);

        // Past or current month
        if (checkDate <= today) {
            return true;
        }

        // Planned future month
        return plannedMonths.some((pm) => pm.year === year && pm.month === month);
    };

    // Navigate to a specific month
    const navigateToMonth = (year: number, month: number) => {
        if (isMonthAvailable(year, month)) {
            router.get('/budget/income-entries', { year, month }, { preserveState: true, preserveScroll: true });
        }
    };

    // Handle calendar month selection
    const handleCalendarSelect = (date: Date | undefined) => {
        if (date) {
            const year = date.getFullYear();
            const month = date.getMonth() + 1;
            navigateToMonth(year, month);
            setCalendarOpen(false);
        }
    };

    // Check if current month is a future planned month
    const isCurrentFuturePlanned = (() => {
        const checkDate = new Date(currentYear, currentMonth - 1, 1);
        const today = new Date(now.getFullYear(), now.getMonth(), 1);
        return checkDate > today && plannedMonths.some((pm) => pm.year === currentYear && pm.month === currentMonth);
    })();

    // Scroll to current month on mount and when current month changes - CENTER IT PERFECTLY
    useEffect(() => {
        if (scrollContainerRef.current) {
            const currentIndex = monthsList.findIndex((m) => m.year === currentYear && m.month === currentMonth);
            if (currentIndex !== -1) {
                const container = scrollContainerRef.current;
                const monthButton = container.children[currentIndex] as HTMLElement;
                if (monthButton) {
                    // Perfect centering: button center = container center
                    const scrollLeft = monthButton.offsetLeft - container.offsetWidth / 2 + monthButton.offsetWidth / 2;

                    // Use instant scroll on mount, smooth on change
                    const behavior = container.scrollLeft === 0 ? 'instant' : 'smooth';
                    container.scrollTo({
                        left: scrollLeft,
                        behavior: behavior as ScrollBehavior,
                    });
                }
            }
        }
    }, [currentYear, currentMonth, monthsList]);

    return (
        <div className={cn('flex items-center gap-3 overflow-hidden', className)}>
            {/* Calendar picker button */}
            <Popover open={calendarOpen} onOpenChange={setCalendarOpen}>
                <PopoverTrigger asChild>
                    <Button variant="outline" size="icon" className="size-10 shrink-0 rounded-lg">
                        <CalendarIcon className="size-4" />
                        <span className="sr-only">Sélectionner un mois</span>
                    </Button>
                </PopoverTrigger>
                <PopoverContent className="w-auto p-0" align="start">
                    <MonthYearPicker
                        selected={new Date(currentYear, currentMonth - 1, 1)}
                        onSelect={handleCalendarSelect}
                        startYear={2020}
                        endYear={new Date().getFullYear() + 5}
                        isMonthAvailable={isMonthAvailable}
                        locale="fr"
                    />
                </PopoverContent>
            </Popover>

            {/* Scrollable months carousel */}
            <div
                ref={scrollContainerRef}
                className="no-scrollbar -mx-4 flex flex-1 gap-3 overflow-x-auto px-4 sm:mx-0 sm:gap-2 sm:px-0"
                style={{
                    scrollbarWidth: 'none',
                    msOverflowStyle: 'none',
                }}
            >
                {monthsList.map(({ year, month, date }) => {
                    const isActive = year === currentYear && month === currentMonth;
                    const isAvailable = isMonthAvailable(year, month);
                    const isFuturePlanned = date > now && plannedMonths.some((pm) => pm.year === year && pm.month === month);

                    const monthLabel = format(date, 'MMM', { locale: fr });
                    const yearLabel = format(date, 'yyyy');
                    const isToday = date.getFullYear() === now.getFullYear() && date.getMonth() === now.getMonth();

                    return (
                        <Button
                            key={`${year}-${month}`}
                            variant={isActive ? 'default' : 'outline'}
                            size="sm"
                            onClick={() => navigateToMonth(year, month)}
                            disabled={!isAvailable}
                            className={cn(
                                'relative h-20 min-w-[80px] shrink-0 flex-col justify-center gap-1 rounded-2xl px-2 transition-all duration-200',
                                isActive && 'scale-110 border-2 border-primary shadow-lg ring-2 ring-primary/20',
                                !isActive && isAvailable && 'scale-95 opacity-70 hover:scale-100 hover:opacity-100',
                                isToday && !isActive && 'border-2 border-primary/40',
                                !isAvailable && 'scale-90 opacity-40',
                            )}
                        >
                            <span className={cn('text-xs font-semibold uppercase tracking-wider', isActive ? 'text-primary-foreground' : 'text-foreground/80')}>{monthLabel}</span>
                            <span className={cn('text-lg font-bold', isActive ? 'text-primary-foreground' : 'text-foreground')}>{yearLabel}</span>
                            {isFuturePlanned && <span className="absolute right-2 top-2 size-2 animate-pulse rounded-full bg-primary shadow-lg" />}
                            {isCurrentFuturePlanned && isActive && (
                                <span className="absolute -top-2 left-1/2 -translate-x-1/2 whitespace-nowrap rounded-full bg-primary px-2 py-0.5 text-[9px] font-bold uppercase tracking-wide text-primary-foreground shadow-md">
                                    Planifié
                                </span>
                            )}
                        </Button>
                    );
                })}
            </div>
        </div>
    );
}
