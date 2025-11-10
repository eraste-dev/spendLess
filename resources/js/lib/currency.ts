import { UserSettings } from '@/types/budget';

/**
 * Format a number as currency based on user settings
 */
export function formatCurrency(
    amount: number | string | null | undefined,
    settings: UserSettings,
    options?: {
        showSymbol?: boolean;
        symbolPosition?: 'before' | 'after';
    }
): string {
    const {
        showSymbol = true,
        symbolPosition = 'after',
    } = options || {};

    // Handle null/undefined amounts and ensure it's a number
    if (amount === null || amount === undefined) {
        amount = 0;
    }

    // Convert to number if it's not already
    const numericAmount = typeof amount === 'number' ? amount : parseFloat(amount as any) || 0;

    // Format the number
    const parts = numericAmount.toFixed(settings.decimal_places).split('.');
    const integerPart = parts[0].replace(
        /\B(?=(\d{3})+(?!\d))/g,
        settings.thousand_separator
    );
    const decimalPart = parts[1];

    let formatted = `${integerPart}${settings.decimal_separator}${decimalPart}`;

    // Add currency symbol if requested
    if (showSymbol) {
        formatted = symbolPosition === 'before'
            ? `${settings.currency_symbol} ${formatted}`
            : `${formatted} ${settings.currency_symbol}`;
    }

    return formatted;
}

/**
 * Format a number without currency symbol
 */
export function formatNumber(
    value: number | string | null | undefined,
    settings: UserSettings
): string {
    return formatCurrency(value, settings, { showSymbol: false });
}
