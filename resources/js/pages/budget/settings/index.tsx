import { UserSettings, Currency } from '@/types/budget';
import MobileHeaderLayout from '@/layouts/mobile-header-layout';
import { Head, useForm } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
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
    });

    const handleCurrencyChange = (currencyCode: string) => {
        const currency = currencies[currencyCode];
        if (currency) {
            setData({
                currency: currencyCode,
                currency_symbol: currency.symbol,
            });
        }
    };

    const handleSubmit: FormEventHandler = (e) => {
        e.preventDefault();
        put(route('budget.settings.update'));
    };

    return (
        <MobileHeaderLayout>
            <Head title="Configuration" />

            <div className="px-4 py-6 md:py-8">
                {/* Page Title */}
                <div className="mb-6">
                    <h1 className="text-2xl sm:text-3xl font-bold">Configuration</h1>
                    <p className="text-sm text-muted-foreground mt-2">
                        Personnalisez vos préférences pour le budget
                    </p>
                </div>

                {/* Settings Form */}
                <form onSubmit={handleSubmit} className="space-y-6 max-w-2xl">
                    {/* Currency Selection */}
                    <div className="rounded-xl border bg-card p-6 space-y-4">
                        <div>
                            <h2 className="text-lg font-semibold mb-1">Devise</h2>
                            <p className="text-sm text-muted-foreground">
                                Sélectionnez la devise utilisée pour vos revenus et dépenses
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
                                    {Object.entries(currencies).map(([code, currency]) => (
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
                                <p className="text-sm text-destructive">{errors.currency}</p>
                            )}
                        </div>

                        {/* Preview */}
                        <div className="rounded-lg bg-muted p-4">
                            <p className="text-xs text-muted-foreground mb-2">
                                Aperçu
                            </p>
                            <div className="flex items-baseline gap-2">
                                <span className="text-3xl font-bold">1,000.00</span>
                                <span className="text-xl text-muted-foreground font-medium">
                                    {data.currency_symbol}
                                </span>
                            </div>
                        </div>
                    </div>

                    {/* Save Button */}
                    <div className="flex gap-3">
                        <Button
                            type="submit"
                            className="flex-1 sm:flex-none h-12 sm:px-8"
                            disabled={processing}
                        >
                            {processing ? 'Enregistrement...' : 'Enregistrer'}
                        </Button>
                    </div>
                </form>
            </div>
        </MobileHeaderLayout>
    );
}
