<?php

namespace Database\Seeders;

use App\Models\Budget\ExpenseCategory;
use App\Models\Budget\ExpenseSubcategory;
use App\Models\Budget\IncomeCategory;
use App\Models\User\User;
use Illuminate\Database\Seeder;

class BudgetCategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Récupérer l'utilisateur de test
        $user = User::where('email', 'test@example.com')->first();

        if (!$user) {
            $this->command->error('Utilisateur test non trouvé. Créez d\'abord un utilisateur avec l\'email test@example.com');
            return;
        }

        $this->command->info('Création des catégories de budget pour l\'utilisateur: ' . $user->name);

        // Nettoyer les catégories existantes pour cet utilisateur
        ExpenseCategory::where('user_id', $user->id)->delete();
        IncomeCategory::where('user_id', $user->id)->delete();

        // ============================================
        // CATÉGORIES DE REVENUS
        // ============================================
        $this->command->info('Création des catégories de revenus...');

        $incomeCategories = [
            [
                'name' => 'Salaire',
                'description' => 'Revenu mensuel fixe',
                'amount' => 500000.00,
                'is_monthly' => true,
                'income_date' => null,
                'color' => '#10b981',
                'order' => 1,
            ],
            [
                'name' => 'Freelance',
                'description' => 'Revenus de projets freelance',
                'amount' => 150000.00,
                'is_monthly' => false,
                'income_date' => now()->addDays(15)->format('Y-m-d'),
                'color' => '#3b82f6',
                'order' => 2,
            ],
            [
                'name' => 'Investissements',
                'description' => 'Dividendes et revenus d\'investissements',
                'amount' => 50000.00,
                'is_monthly' => true,
                'income_date' => null,
                'color' => '#8b5cf6',
                'order' => 3,
            ],
            [
                'name' => 'Projets',
                'description' => 'Revenus de projets secondaires',
                'amount' => 100000.00,
                'is_monthly' => false,
                'income_date' => now()->addDays(30)->format('Y-m-d'),
                'color' => '#f59e0b',
                'order' => 4,
            ],
            [
                'name' => 'Autres revenus',
                'description' => 'Autres sources de revenus',
                'amount' => null,
                'is_monthly' => false,
                'income_date' => now()->format('Y-m-d'),
                'color' => '#6b7280',
                'order' => 5,
            ],
        ];

        foreach ($incomeCategories as $category) {
            IncomeCategory::create(array_merge($category, ['user_id' => $user->id]));
        }

        $this->command->info('✓ ' . count($incomeCategories) . ' catégories de revenus créées');

        // ============================================
        // CATÉGORIES DE DÉPENSES
        // ============================================
        $this->command->info('Création des catégories de dépenses...');

        // 1. DÉPENSES FIXES
        $depensesFixes = ExpenseCategory::create([
            'user_id' => $user->id,
            'name' => 'Dépenses Fixes',
            'description' => 'Dépenses mensuelles récurrentes obligatoires',
            'target_percentage' => 50.00,
            'color' => '#ef4444',
            'order' => 1,
            'is_active' => true,
            'is_recurring_monthly' => true,
            'recurrence_day' => 1,
        ]);

        $subcategoriesDepensesFixes = [
            ['name' => 'Dîme', 'description' => '10% du revenu', 'target_amount' => null, 'order' => 1],
            ['name' => 'Loyer', 'description' => 'Loyer mensuel', 'target_amount' => null, 'order' => 2],
            ['name' => 'Électricité', 'description' => 'Facture d\'électricité', 'target_amount' => null, 'order' => 3],
            ['name' => 'Gaz', 'description' => 'Facture de gaz', 'target_amount' => null, 'order' => 4],
            ['name' => 'Eau', 'description' => 'Facture d\'eau', 'target_amount' => null, 'order' => 5],
            ['name' => 'Internet', 'description' => 'Abonnement internet', 'target_amount' => null, 'order' => 6],
            ['name' => 'Transport', 'description' => 'Frais de transport mensuels', 'target_amount' => null, 'order' => 7],
            ['name' => 'Alimentation', 'description' => 'Courses et alimentation', 'target_amount' => null, 'order' => 8],
            ['name' => 'Pressing', 'description' => 'Nettoyage et pressing', 'target_amount' => null, 'order' => 9],
            ['name' => 'Besoins maison', 'description' => 'Produits ménagers et entretien', 'target_amount' => null, 'order' => 10],
        ];

        foreach ($subcategoriesDepensesFixes as $subcategory) {
            ExpenseSubcategory::create(array_merge($subcategory, [
                'expense_category_id' => $depensesFixes->id,
            ]));
        }

        $this->command->info('✓ Catégorie "Dépenses Fixes" créée avec ' . count($subcategoriesDepensesFixes) . ' sous-catégories');

        // 2. ÉPARGNE
        $epargne = ExpenseCategory::create([
            'user_id' => $user->id,
            'name' => 'Épargne',
            'description' => 'Épargne mensuelle et provisions',
            'target_percentage' => 30.00,
            'color' => '#10b981',
            'order' => 2,
            'is_active' => true,
            'is_recurring_monthly' => true,
            'recurrence_day' => 1,
        ]);

        $subcategoriesEpargne = [
            ['name' => 'Épargne mariage', 'description' => 'Épargne pour le mariage', 'target_amount' => null, 'order' => 1],
            ['name' => 'Épargne NSIA', 'description' => 'Compte épargne NSIA', 'target_amount' => null, 'order' => 2],
            ['name' => 'Épargne 10 ans', 'description' => 'Plan épargne long terme', 'target_amount' => null, 'order' => 3],
            ['name' => 'Santé & imprévus', 'description' => 'Fonds d\'urgence santé', 'target_amount' => null, 'order' => 4],
            ['name' => 'OMCG', 'description' => 'Organisation Ministère Chrétien', 'target_amount' => null, 'order' => 5],
        ];

        foreach ($subcategoriesEpargne as $subcategory) {
            ExpenseSubcategory::create(array_merge($subcategory, [
                'expense_category_id' => $epargne->id,
            ]));
        }

        $this->command->info('✓ Catégorie "Épargne" créée avec ' . count($subcategoriesEpargne) . ' sous-catégories');

        // 3. LOISIRS & EXTRAS
        $loisirs = ExpenseCategory::create([
            'user_id' => $user->id,
            'name' => 'Loisirs & Extras',
            'description' => 'Dépenses de loisirs et shopping',
            'target_percentage' => 20.00,
            'color' => '#f59e0b',
            'order' => 3,
            'is_active' => true,
            'is_recurring_monthly' => false,
        ]);

        $subcategoriesLoisirs = [
            ['name' => 'Sorties/Restaurants', 'description' => 'Restaurants et sorties', 'target_amount' => null, 'order' => 1],
            ['name' => 'Shopping - Vêtements', 'description' => 'Achats de vêtements', 'target_amount' => null, 'order' => 2],
            ['name' => 'Shopping - Tech', 'description' => 'Gadgets et technologie', 'target_amount' => null, 'order' => 3],
            ['name' => 'Shopping - Maison', 'description' => 'Décoration et équipements maison', 'target_amount' => null, 'order' => 4],
        ];

        foreach ($subcategoriesLoisirs as $subcategory) {
            ExpenseSubcategory::create(array_merge($subcategory, [
                'expense_category_id' => $loisirs->id,
            ]));
        }

        $this->command->info('✓ Catégorie "Loisirs & Extras" créée avec ' . count($subcategoriesLoisirs) . ' sous-catégories');

        // ============================================
        // RÉSUMÉ
        // ============================================
        $totalIncomeCategories = IncomeCategory::where('user_id', $user->id)->count();
        $totalExpenseCategories = ExpenseCategory::where('user_id', $user->id)->count();
        $totalSubcategories = ExpenseSubcategory::whereHas('category', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })->count();

        $this->command->info('');
        $this->command->info('========================================');
        $this->command->info('✅ Seeding terminé avec succès !');
        $this->command->info('========================================');
        $this->command->info('📊 Résumé:');
        $this->command->info("   • Catégories de revenus: {$totalIncomeCategories}");
        $this->command->info("   • Catégories de dépenses: {$totalExpenseCategories}");
        $this->command->info("   • Sous-catégories de dépenses: {$totalSubcategories}");
        $this->command->info('========================================');
    }
}
