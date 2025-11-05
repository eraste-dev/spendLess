import { Link, usePage } from '@inertiajs/react';
import { Menu, X, User, LogOut, Settings, Wallet, TrendingUp, TrendingDown } from 'lucide-react';
import { useState } from 'react';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Sheet, SheetContent, SheetTrigger } from '@/components/ui/sheet';

interface User {
    name: string;
    email: string;
}

interface MobileHeaderLayoutProps {
    children: React.ReactNode;
}

export default function MobileHeaderLayout({
    children,
}: MobileHeaderLayoutProps) {
    const { props } = usePage<{ auth: { user: User } }>();
    const [isMobileMenuOpen, setIsMobileMenuOpen] = useState(false);
    const page = usePage();

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
                        className="flex items-center gap-2 font-bold text-xl"
                    >
                        <Wallet className="size-6" />
                        <span className="hidden sm:inline">Budget App</span>
                    </Link>

                    {/* Desktop Navigation */}
                    <nav className="hidden md:flex items-center gap-1">
                        {navigation.map((item) => (
                            <Link
                                key={item.name}
                                href={item.href}
                                className={`px-4 py-2 rounded-md text-sm font-medium transition-colors ${
                                    isActive(item.href)
                                        ? 'bg-primary text-primary-foreground'
                                        : 'text-muted-foreground hover:bg-muted hover:text-foreground'
                                }`}
                            >
                                {item.name}
                            </Link>
                        ))}
                    </nav>

                    {/* Right side */}
                    <div className="flex items-center gap-2">
                        {/* User Menu - Desktop */}
                        <DropdownMenu>
                            <DropdownMenuTrigger asChild>
                                <Button
                                    variant="ghost"
                                    size="icon"
                                    className="hidden md:flex rounded-full"
                                >
                                    <User className="size-5" />
                                </Button>
                            </DropdownMenuTrigger>
                            <DropdownMenuContent align="end" className="w-56">
                                <DropdownMenuLabel>
                                    <div className="flex flex-col space-y-1">
                                        <p className="text-sm font-medium">
                                            {props.auth.user.name}
                                        </p>
                                        <p className="text-xs text-muted-foreground">
                                            {props.auth.user.email}
                                        </p>
                                    </div>
                                </DropdownMenuLabel>
                                <DropdownMenuSeparator />
                                <DropdownMenuItem asChild>
                                    <Link
                                        href="/settings/profile"
                                        className="cursor-pointer"
                                    >
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
                            <SheetContent side="right" className="w-[280px] p-0">
                                <div className="flex flex-col h-full">
                                    {/* User Info */}
                                    <div className="p-6 border-b">
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
                                                        className={`flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium transition-colors ${
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
                                    <div className="border-t p-4 space-y-1">
                                        <Link
                                            href="/settings/profile"
                                            onClick={() =>
                                                setIsMobileMenuOpen(false)
                                            }
                                            className="flex items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium text-muted-foreground hover:bg-muted hover:text-foreground transition-colors"
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
                                            className="flex w-full items-center gap-3 px-4 py-3 rounded-lg text-sm font-medium text-destructive hover:bg-destructive/10 transition-colors"
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
