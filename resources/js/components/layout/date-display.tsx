import { Calendar } from 'lucide-react';
import { cn } from '@/lib/utils';

interface DateDisplayProps {
    date: string;
    compact?: boolean;
    className?: string;
}

export function DateDisplay({ date, compact = false, className }: DateDisplayProps) {
    if (compact) {
        // Compact version for mobile - just the day
        const dayNumber = new Date().getDate();
        return (
            <div className={cn("flex items-center gap-1 px-2 py-1 rounded-md bg-muted/50", className)}>
                <Calendar className="size-3.5 text-muted-foreground flex-shrink-0" />
                <span className="text-xs font-medium">{dayNumber}</span>
            </div>
        );
    }

    // Full version for desktop
    return (
        <div className={cn("flex items-center gap-2 px-3 py-1.5 rounded-lg bg-muted/50", className)}>
            <Calendar className="size-4 text-muted-foreground" />
            <span className="text-sm font-medium">{date}</span>
        </div>
    );
}
