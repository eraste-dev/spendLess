import { Link } from '@inertiajs/react';
import { User, LogOut, Settings, TrendingUp, Coins } from 'lucide-react';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { BudgetSummary, UserSettings } from '@/types/budget';
import { formatCurrency } from '@/lib/currency';

interface User {
    name: string;
    email: string;
}

interface UserDropdownProps {
    user: User;
    budgetSummary?: BudgetSummary;
    userSettings?: UserSettings;
}

export function UserDropdown({ user, budgetSummary, userSettings }: UserDropdownProps) {
    return (
        <DropdownMenu>
            <DropdownMenuTrigger asChild>
                <Button
                    variant="ghost"
                    size="icon"
                    className="rounded-full size-8 sm:size-9"
                >
                    <User className="size-4 sm:size-5" />
                </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end" className="w-72">
                <DropdownMenuLabel>
                    <div className="flex flex-col space-y-1">
                        <p className="text-sm font-medium">{user.name}</p>
                        <p className="text-xs text-muted-foreground">{user.email}</p>
                    </div>
                </DropdownMenuLabel>

                {/* Budget Summary in Dropdown */}
                {budgetSummary && userSettings && (
                    <>
                        <DropdownMenuSeparator />
                        <div className="px-2 py-3 space-y-2">
                            <div className="flex items-center justify-between p-2 rounded-lg bg-primary/10">
                                <div className="flex items-center gap-2">
                                    <Coins className="size-4 text-primary" />
                                    <span className="text-xs font-medium text-muted-foreground">
                                        Budget du jour
                                    </span>
                                </div>
                                <span className="text-sm font-bold text-primary">
                                    {formatCurrency(budgetSummary.today_budget, userSettings)}
                                </span>
                            </div>
                            <div className="flex items-center justify-between p-2 rounded-lg bg-muted/50">
                                <div className="flex items-center gap-2">
                                    <TrendingUp className="size-4 text-muted-foreground" />
                                    <span className="text-xs font-medium text-muted-foreground">
                                        Restant ce mois
                                    </span>
                                </div>
                                <span className="text-sm font-semibold">
                                    {formatCurrency(budgetSummary.remaining_this_month, userSettings)}
                                </span>
                            </div>
                            <p className="text-xs text-center text-muted-foreground">
                                {budgetSummary.days_remaining} jour{budgetSummary.days_remaining > 1 ? 's' : ''} restant{budgetSummary.days_remaining > 1 ? 's' : ''}
                            </p>
                        </div>
                    </>
                )}

                <DropdownMenuSeparator />
                <DropdownMenuItem asChild>
                    <Link href="/settings/profile" className="cursor-pointer">
                        <Settings className="mr-2 size-4" />
                        Paramètres
                    </Link>
                </DropdownMenuItem>
                <DropdownMenuSeparator />
                <DropdownMenuItem asChild>
                    <Link
                        href="/logout"
                        method="post"
                        as="button"
                        className="w-full cursor-pointer text-destructive"
                    >
                        <LogOut className="mr-2 size-4" />
                        Déconnexion
                    </Link>
                </DropdownMenuItem>
            </DropdownMenuContent>
        </DropdownMenu>
    );
}
