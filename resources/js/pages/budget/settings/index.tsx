import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import AppLayout from '@/layouts/app-layout';
import { update } from '@/routes/budget/settings';
import { Currency, UserSettings } from '@/types/budget';
import { Head, useForm } from '@inertiajs/react';
import { FormEventHandler } from 'react';

interface SettingsIndexProps {
    settings: UserSettings;
    currencies: Record<string, Currency>;
}

export default function SettingsIndex({
    settings,
    currencies,
}: SettingsIndexProps) {
    const { data, setData, put, processing, errors } = useForm({
        currency: settings.currency,
        currency_symbol: settings.currency_symbol,
        thousand_separator: settings.thousand_separator,
        decimal_separator: settings.decimal_separator,
        decimal_places: settings.decimal_places,
    });

    const handleCurrencyChange = (currencyCode: string) => {
        const currency = currencies[currencyCode];
        if (currency) {
            setData({
                ...data,
                currency: currencyCode,
                currency_symbol: currency.symbol,
            });
        }
    };

    // Format preview number based on current settings
    const formatPreview = () => {
        const number = 1234567.89;
        const parts = number.toFixed(data.decimal_places).split('.');
        const integerPart = parts[0].replace(
            /\B(?=(\d{3})+(?!\d))/g,
            data.thousand_separator,
        );
        const decimalPart = parts[1];
        return `${integerPart}${data.decimal_separator}${decimalPart}`;
    };

    const handleSubmit: FormEventHandler = (e) => {
        e.preventDefault();
        put(update.url());
    };

    return (
        <AppLayout>
            <Head title="Configuration" />

            <div className="px-4 py-6 md:py-8">
                {/* Page Title */}
                <div className="mb-6">
                    <h1 className="text-2xl font-bold sm:text-3xl">
                        Configuration
                    </h1>
                    <p className="mt-2 text-sm text-muted-foreground">
                        Personnalisez vos préférences pour le budget
                    </p>
                </div>

                {/* Settings Form */}
                <form onSubmit={handleSubmit} className="max-w-2xl space-y-6">
                    {/* Currency Selection */}
                    <div className="space-y-4 rounded-xl border bg-card p-6">
                        <div>
                            <h2 className="mb-1 text-lg font-semibold">
                                Devise
                            </h2>
                            <p className="text-sm text-muted-foreground">
                                Sélectionnez la devise utilisée pour vos revenus
                                et dépenses
                            </p>
                        </div>

                        <div className="space-y-3">
                            <Label htmlFor="currency">Devise utilisée</Label>
                            <Select
                                value={data.currency}
                                onValueChange={handleCurrencyChange}
                            >
                                <SelectTrigger className="h-12 text-base">
                                    <SelectValue placeholder="Sélectionner une devise" />
                                </SelectTrigger>
                                <SelectContent>
                                    {Object.entries(currencies)
                                        .filter(([code]) => code.trim() !== '')
                                        .map(([code, currency]) => (
                                            <SelectItem key={code} value={code}>
                                                <div className="flex items-center gap-3">
                                                    <span className="font-semibold">
                                                        {currency.symbol}
                                                    </span>
                                                    <span>{currency.name}</span>
                                                </div>
                                            </SelectItem>
                                        ))}
                                </SelectContent>
                            </Select>
                            {errors.currency && (
                                <p className="text-sm text-destructive">
                                    {errors.currency}
                                </p>
                            )}
                        </div>

                        {/* Preview */}
                        <div className="rounded-lg bg-muted p-4">
                            <p className="mb-2 text-xs text-muted-foreground">
                                Aperçu
                            </p>
                            <div className="flex items-baseline gap-2">
                                <span className="text-3xl font-bold">
                                    {formatPreview()}
                                </span>
                                <span className="text-xl font-medium text-muted-foreground">
                                    {data.currency_symbol}
                                </span>
                            </div>
                        </div>
                    </div>

                    {/* Number Formatting */}
                    <div className="space-y-4 rounded-xl border bg-card p-6">
                        <div>
                            <h2 className="mb-1 text-lg font-semibold">
                                Format des nombres
                            </h2>
                            <p className="text-sm text-muted-foreground">
                                Personnalisez l'affichage des montants
                            </p>
                        </div>

                        <div className="grid gap-4 sm:grid-cols-2">
                            {/* Thousand Separator */}
                            <div className="space-y-2">
                                <Label htmlFor="thousand_separator">
                                    Séparateur de milliers
                                </Label>
                                <Select
                                    value={data.thousand_separator}
                                    onValueChange={(value) =>
                                        setData('thousand_separator', value)
                                    }
                                >
                                    <SelectTrigger className="h-12 text-base">
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value=" ">
                                            Espace ( )
                                        </SelectItem>
                                        <SelectItem value=",">
                                            Virgule (,)
                                        </SelectItem>
                                        <SelectItem value=".">
                                            Point (.)
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                {errors.thousand_separator && (
                                    <p className="text-sm text-destructive">
                                        {errors.thousand_separator}
                                    </p>
                                )}
                            </div>

                            {/* Decimal Separator */}
                            <div className="space-y-2">
                                <Label htmlFor="decimal_separator">
                                    Séparateur décimal
                                </Label>
                                <Select
                                    value={data.decimal_separator}
                                    onValueChange={(value) =>
                                        setData('decimal_separator', value)
                                    }
                                >
                                    <SelectTrigger className="h-12 text-base">
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value=",">
                                            Virgule (,)
                                        </SelectItem>
                                        <SelectItem value=".">
                                            Point (.)
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                {errors.decimal_separator && (
                                    <p className="text-sm text-destructive">
                                        {errors.decimal_separator}
                                    </p>
                                )}
                            </div>
                        </div>

                        {/* Decimal Places */}
                        <div className="space-y-2">
                            <Label htmlFor="decimal_places">
                                Nombre de décimales
                            </Label>
                            <Input
                                id="decimal_places"
                                type="number"
                                min="0"
                                max="4"
                                value={data.decimal_places}
                                onChange={(e) =>
                                    setData(
                                        'decimal_places',
                                        parseInt(e.target.value) || 0,
                                    )
                                }
                                className="h-12 text-base"
                            />
                            {errors.decimal_places && (
                                <p className="text-sm text-destructive">
                                    {errors.decimal_places}
                                </p>
                            )}
                            <p className="text-xs text-muted-foreground">
                                Entre 0 et 4 décimales
                            </p>
                        </div>
                    </div>

                    {/* Save Button */}
                    <div className="flex gap-3">
                        <Button
                            type="submit"
                            className="h-12 flex-1 sm:flex-none sm:px-8"
                            disabled={processing}
                        >
                            {processing ? 'Enregistrement...' : 'Enregistrer'}
                        </Button>
                    </div>
                </form>
            </div>
        </AppLayout>
    );
}
