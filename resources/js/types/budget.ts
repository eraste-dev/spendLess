// Types partagés pour le module Budget

export interface IncomeCategory {
    id: number;
    name: string;
    description: string | null;
    amount: number | null;
    is_monthly: boolean;
    income_date: string | null;
    color: string;
    order: number;
    is_active: boolean;
    created_at: string;
    updated_at: string;
}

export interface ExpenseSubcategory {
    id: number;
    expense_category_id: number;
    name: string;
    description: string | null;
    target_amount: number | null;
    order: number;
    is_active: boolean;
    created_at: string;
    updated_at: string;
}

export interface ExpenseCategory {
    id: number;
    name: string;
    description: string | null;
    color: string;
    target_percentage: number | null;
    target_amount: number | null;
    order: number;
    is_active: boolean;
    is_recurring_monthly: boolean;
    recurrence_day: number;
    subcategories?: ExpenseSubcategory[];
    created_at: string;
    updated_at: string;
}

export interface Income {
    id: number;
    user_id: number;
    income_category_id: number;
    category?: IncomeCategory;
    description: string | null;
    amount: number;
    income_date: string;
    is_recurring: boolean;
    recurrence_day: number | null;
    next_occurrence: string | null;
    created_at: string;
    updated_at: string;
}

export interface UserSettings {
    id: number;
    user_id: number;
    currency: string;
    currency_symbol: string;
    date_format: string;
    locale: string;
}

export interface Currency {
    symbol: string;
    name: string;
}
