import { BudgetDisplay } from '@/components/layout/budget-display';
import { DateDisplay } from '@/components/layout/date-display';
import { UserDropdown } from '@/components/layout/user-dropdown';
import { Button } from '@/components/ui/button';
import { Sheet, SheetContent, SheetTrigger } from '@/components/ui/sheet';
import { formatCurrency } from '@/lib/currency';
import { BudgetSummary, UserSettings } from '@/types/budget';
import { Link, usePage } from '@inertiajs/react';
import {
    Calendar,
    Coins,
    LogOut,
    Menu,
    Settings,
    TrendingDown,
    TrendingUp,
    User,
    Wallet,
    X,
} from 'lucide-react';
import { useState } from 'react';

interface User {
    name: string;
    email: string;
}

interface PageProps extends Record<string, unknown> {
    auth: { user: User };
    budgetSummary?: BudgetSummary;
    userSettings?: UserSettings;
}

interface AppLayoutProps {
    children: React.ReactNode;
}

export default function AppLayout({
    children,
}: AppLayoutProps) {
    const { props } = usePage<PageProps>();
    const [isMobileMenuOpen, setIsMobileMenuOpen] = useState(false);
    const page = usePage();

    // Format current date
    const today = new Date();
    const formattedDate = today.toLocaleDateString('fr-FR', {
        weekday: 'short',
        day: 'numeric',
        month: 'short',
    });

    const navigation = [
        { name: 'Dashboard', href: '/dashboard', icon: Wallet },
        {
            name: 'Revenus',
            href: '/budget/incomes',
            icon: TrendingUp,
        },
        {
            name: 'Dépenses',
            href: '/budget/expense-categories',
            icon: TrendingDown,
        },
        {
            name: 'Configuration',
            href: '/budget/settings',
            icon: Settings,
        },
    ];

    const isActive = (href: string) => page.url.startsWith(href);

    return (
        <div className="min-h-screen bg-background">
            {/* Mobile & Desktop Header */}
            <header className="sticky top-0 z-50 w-full border-b bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60">
                <div className="container flex h-16 items-center justify-between px-4">
                    {/* Logo */}
                    <Link
                        href="/dashboard"
                        className="flex items-center gap-2 text-xl font-bold"
                    >
                        <Wallet className="size-6" />
                        <span className="hidden sm:inline">Budget App</span>
                    </Link>

                    {/* Desktop Navigation */}
                    <nav className="hidden items-center gap-1 md:flex">
                        {navigation.map((item) => (
                            <Link
                                key={item.name}
                                href={item.href}
                                className={`rounded-md px-4 py-2 text-sm font-medium transition-colors ${
                                    isActive(item.href)
                                        ? 'bg-primary text-primary-foreground'
                                        : 'text-muted-foreground hover:bg-muted hover:text-foreground'
                                }`}
                            >
                                {item.name}
                            </Link>
                        ))}
                    </nav>

                    {/* Right side - Visible on all devices */}
                    <div className="flex items-center gap-1.5 sm:gap-2 md:gap-3">
                        {/* Date - Compact on mobile, full on desktop */}
                        <DateDisplay
                            date={formattedDate}
                            compact={true}
                            className="md:hidden"
                        />
                        <DateDisplay
                            date={formattedDate}
                            className="hidden md:flex"
                        />

                        {/* Budget Info - Always visible with responsive sizing */}
                        {props.budgetSummary && props.userSettings && (
                            <>
                                {/* Mobile compact version */}
                                <div className="md:hidden">
                                    <BudgetDisplay
                                        budgetSummary={props.budgetSummary}
                                        userSettings={props.userSettings}
                                        compact={true}
                                    />
                                </div>
                                {/* Desktop full version */}
                                <div className="hidden md:block">
                                    <BudgetDisplay
                                        budgetSummary={props.budgetSummary}
                                        userSettings={props.userSettings}
                                    />
                                </div>
                            </>
                        )}

                        {/* User Dropdown - Always visible */}
                        <UserDropdown
                            user={props.auth.user}
                            budgetSummary={props.budgetSummary}
                            userSettings={props.userSettings}
                        />

                        {/* Mobile Menu Button */}
                        <Sheet
                            open={isMobileMenuOpen}
                            onOpenChange={setIsMobileMenuOpen}
                        >
                            <SheetTrigger asChild>
                                <Button
                                    variant="ghost"
                                    size="icon"
                                    className="md:hidden"
                                >
                                    {isMobileMenuOpen ? (
                                        <X className="size-6" />
                                    ) : (
                                        <Menu className="size-6" />
                                    )}
                                </Button>
                            </SheetTrigger>
                            <SheetContent
                                side="right"
                                className="w-[300px] p-0"
                            >
                                <div className="flex h-full flex-col">
                                    {/* User Info */}
                                    <div className="space-y-4 border-b p-6">
                                        <div className="flex items-center gap-3">
                                            <div className="flex size-10 items-center justify-center rounded-full bg-primary text-primary-foreground">
                                                <User className="size-5" />
                                            </div>
                                            <div className="flex flex-col">
                                                <p className="text-sm font-medium">
                                                    {props.auth.user.name}
                                                </p>
                                                <p className="text-xs text-muted-foreground">
                                                    {props.auth.user.email}
                                                </p>
                                            </div>
                                        </div>

                                        {/* Date - Mobile */}
                                        <div className="flex items-center gap-2 rounded-lg bg-muted/50 px-3 py-2">
                                            <Calendar className="size-4 text-muted-foreground" />
                                            <span className="text-sm font-medium">
                                                {formattedDate}
                                            </span>
                                        </div>

                                        {/* Budget Summary - Mobile */}
                                        {props.budgetSummary &&
                                            props.userSettings && (
                                                <div className="space-y-2">
                                                    <div className="flex items-center justify-between rounded-lg border border-primary/20 bg-primary/10 p-3">
                                                        <div className="flex items-center gap-2">
                                                            <Coins className="size-4 text-primary" />
                                                            <span className="text-xs font-medium text-muted-foreground">
                                                                Budget du jour
                                                            </span>
                                                        </div>
                                                        <span className="text-sm font-bold text-primary">
                                                            {formatCurrency(
                                                                props
                                                                    .budgetSummary
                                                                    .today_budget,
                                                                props.userSettings,
                                                            )}
                                                        </span>
                                                    </div>
                                                    <div className="flex items-center justify-between rounded-lg bg-muted/50 p-3">
                                                        <div className="flex items-center gap-2">
                                                            <TrendingUp className="size-4 text-muted-foreground" />
                                                            <span className="text-xs font-medium text-muted-foreground">
                                                                Restant ce mois
                                                            </span>
                                                        </div>
                                                        <span className="text-sm font-semibold">
                                                            {formatCurrency(
                                                                props
                                                                    .budgetSummary
                                                                    .remaining_this_month,
                                                                props.userSettings,
                                                            )}
                                                        </span>
                                                    </div>
                                                    <p className="pt-1 text-center text-xs text-muted-foreground">
                                                        {
                                                            props.budgetSummary
                                                                .days_remaining
                                                        }{' '}
                                                        jour
                                                        {props.budgetSummary
                                                            .days_remaining > 1
                                                            ? 's'
                                                            : ''}{' '}
                                                        restant
                                                        {props.budgetSummary
                                                            .days_remaining > 1
                                                            ? 's'
                                                            : ''}
                                                    </p>
                                                </div>
                                            )}
                                    </div>

                                    {/* Mobile Navigation */}
                                    <nav className="flex-1 p-4">
                                        <div className="space-y-1">
                                            {navigation.map((item) => {
                                                const Icon = item.icon;
                                                return (
                                                    <Link
                                                        key={item.name}
                                                        href={item.href}
                                                        onClick={() =>
                                                            setIsMobileMenuOpen(
                                                                false,
                                                            )
                                                        }
                                                        className={`flex items-center gap-3 rounded-lg px-4 py-3 text-sm font-medium transition-colors ${
                                                            isActive(item.href)
                                                                ? 'bg-primary text-primary-foreground'
                                                                : 'text-muted-foreground hover:bg-muted hover:text-foreground'
                                                        }`}
                                                    >
                                                        <Icon className="size-5" />
                                                        {item.name}
                                                    </Link>
                                                );
                                            })}
                                        </div>
                                    </nav>

                                    {/* Bottom Actions */}
                                    <div className="space-y-1 border-t p-4">
                                        <Link
                                            href="/settings/profile"
                                            onClick={() =>
                                                setIsMobileMenuOpen(false)
                                            }
                                            className="flex items-center gap-3 rounded-lg px-4 py-3 text-sm font-medium text-muted-foreground transition-colors hover:bg-muted hover:text-foreground"
                                        >
                                            <Settings className="size-5" />
                                            Paramètres
                                        </Link>
                                        <Link
                                            href="/logout"
                                            method="post"
                                            as="button"
                                            onClick={() =>
                                                setIsMobileMenuOpen(false)
                                            }
                                            className="flex w-full items-center gap-3 rounded-lg px-4 py-3 text-sm font-medium text-destructive transition-colors hover:bg-destructive/10"
                                        >
                                            <LogOut className="size-5" />
                                            Déconnexion
                                        </Link>
                                    </div>
                                </div>
                            </SheetContent>
                        </Sheet>
                    </div>
                </div>
            </header>

            {/* Main Content */}
            <main className="container mx-auto px-4 py-6 md:py-8">
                {children}
            </main>
        </div>
    );
}
