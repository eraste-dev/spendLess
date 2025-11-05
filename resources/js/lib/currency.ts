import { UserSettings } from '@/types/budget';

/**
 * Format a number as currency based on user settings
 */
export function formatCurrency(
    amount: number | null | undefined,
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

    // Handle null/undefined amounts
    if (amount === null || amount === undefined) {
        amount = 0;
    }

    // Format the number
    const parts = amount.toFixed(settings.decimal_places).split('.');
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
    value: number | null | undefined,
    settings: UserSettings
): string {
    return formatCurrency(value, settings, { showSymbol: false });
}
