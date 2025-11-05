# Structure de Base de Données - Application de Gestion de Finance

## Vue d'ensemble

Cette base de données est conçue pour supporter une application multi-utilisateurs de gestion de finances personnelles avec système de récurrence automatique et planification.

---

## Principes de conception

1. **Multi-utilisateurs** : Chaque utilisateur a ses propres catégories, revenus, dépenses et épargnes
2. **Récurrence automatique** : Les revenus et dépenses mensuels sont automatiquement clonés
3. **Planification** : Support pour la création de données futures avec statut "planifié"
4. **Historique** : Soft deletes pour conserver l'historique
5. **Indépendance mensuelle** : Chaque mois est indépendant et modifiable sans affecter les autres

---

## Tables principales

### 1. `users`

Table des utilisateurs (existante Laravel Fortify).

```sql
id                  BIGINT UNSIGNED PRIMARY KEY
name                VARCHAR(255)
email               VARCHAR(255) UNIQUE
email_verified_at   TIMESTAMP NULL
password            VARCHAR(255)
two_factor_secret   TEXT NULL
two_factor_recovery_codes TEXT NULL
two_factor_confirmed_at TIMESTAMP NULL
remember_token      VARCHAR(100) NULL
created_at          TIMESTAMP
updated_at          TIMESTAMP
```

---

### 2. `user_settings`

Paramètres et préférences de chaque utilisateur.

```sql
id                      BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT
user_id                 BIGINT UNSIGNED NOT NULL
currency                VARCHAR(10) DEFAULT 'XOF'
currency_format         VARCHAR(50) DEFAULT '{amount} {currency}'
date_format             VARCHAR(20) DEFAULT 'd/m/Y'
month_start_day         TINYINT DEFAULT 1
week_start_day          TINYINT DEFAULT 1 (1=Lundi, 0=Dimanche)
alert_threshold         DECIMAL(5,2) DEFAULT 90.00 (% dépassement avant alerte)
savings_rate_target     DECIMAL(5,2) DEFAULT 20.00 (% épargne cible)
planning_months         TINYINT DEFAULT 3 (nombre de mois à planifier)
auto_clone_enabled      BOOLEAN DEFAULT TRUE (activer le clonage auto)
theme                   VARCHAR(20) DEFAULT 'system' (light/dark/system)
created_at              TIMESTAMP
updated_at              TIMESTAMP

FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
INDEX idx_user_id (user_id)
UNIQUE KEY unique_user_settings (user_id)
```

---

### 3. `income_categories`

Catégories de revenus personnalisables par utilisateur.

```sql
id                  BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT
user_id             BIGINT UNSIGNED NOT NULL
name                VARCHAR(100) NOT NULL
description         TEXT NULL
color               VARCHAR(7) DEFAULT '#3B82F6' (format hex: #RRGGBB)
icon                VARCHAR(50) NULL
is_active           BOOLEAN DEFAULT TRUE
order               INTEGER DEFAULT 0
created_at          TIMESTAMP
updated_at          TIMESTAMP
deleted_at          TIMESTAMP NULL (soft delete)

FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
INDEX idx_user_active (user_id, is_active)
INDEX idx_user_order (user_id, order)
```

**Exemples de catégories** : Salaire, Freelance, Projets, Investissements, Dividendes, Primes, Autres

---

### 4. `incomes`

Revenus de l'utilisateur avec support de récurrence et planification.

```sql
id                      BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT
user_id                 BIGINT UNSIGNED NOT NULL
income_category_id      BIGINT UNSIGNED NULL
name                    VARCHAR(255) NOT NULL
amount                  DECIMAL(15,2) NOT NULL
date                    DATE NOT NULL
type                    ENUM('fixed', 'variable') DEFAULT 'fixed'
recurrence_type         ENUM('one-time', 'monthly') DEFAULT 'one-time'
status                  ENUM('active', 'planned') DEFAULT 'active'
description             TEXT NULL
parent_income_id        BIGINT UNSIGNED NULL (référence au revenu source si cloné)
is_cloned               BOOLEAN DEFAULT FALSE
created_at              TIMESTAMP
updated_at              TIMESTAMP
deleted_at              TIMESTAMP NULL (soft delete)

FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
FOREIGN KEY (income_category_id) REFERENCES income_categories(id) ON DELETE SET NULL
FOREIGN KEY (parent_income_id) REFERENCES incomes(id) ON DELETE SET NULL
INDEX idx_user_date (user_id, date)
INDEX idx_user_status (user_id, status)
INDEX idx_recurrence (recurrence_type, status)
INDEX idx_month_year (user_id, YEAR(date), MONTH(date))
```

**Notes** :

- `recurrence_type = 'monthly'` → sera cloné automatiquement chaque mois
- `status = 'planned'` → créé en avance, ne compte pas dans les calculs avant que la date arrive
- `status = 'active'` → compte dans les calculs du mois
- `parent_income_id` → permet de tracer la lignée des clones

---

### 5. `expense_categories`

Catégories de dépenses avec configuration de récurrence.

```sql
id                      BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT
user_id                 BIGINT UNSIGNED NOT NULL
name                    VARCHAR(100) NOT NULL
description             TEXT NULL
target_percentage       DECIMAL(5,2) NULL (% cible du budget total, ex: 10.00 pour 10%)
target_amount           DECIMAL(15,2) NULL (montant fixe cible par mois)
color                   VARCHAR(7) DEFAULT '#EF4444'
icon                    VARCHAR(50) NULL
is_active               BOOLEAN DEFAULT TRUE
is_recurring            BOOLEAN DEFAULT FALSE (active le clonage mensuel)
clone_day               TINYINT DEFAULT 1 (jour du mois où créer le clone)
keep_amounts            BOOLEAN DEFAULT TRUE (conserver montants du mois précédent)
order                   INTEGER DEFAULT 0
created_at              TIMESTAMP
updated_at              TIMESTAMP
deleted_at              TIMESTAMP NULL (soft delete)

FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
INDEX idx_user_active (user_id, is_active)
INDEX idx_user_recurring (user_id, is_recurring)
INDEX idx_user_order (user_id, order)
```

**Notes** :

- `target_percentage` et `target_amount` sont mutuellement exclusifs (l'un ou l'autre, pas les deux)
- `is_recurring = TRUE` → toutes les dépenses de cette catégorie seront clonées automatiquement
- `clone_day` → jour du mois où créer les clones (1-28, pour éviter problèmes de mois courts)

---

### 6. `expense_subcategories`

Sous-catégories de dépenses.

```sql
id                      BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT
expense_category_id     BIGINT UNSIGNED NOT NULL
name                    VARCHAR(100) NOT NULL
description             TEXT NULL
icon                    VARCHAR(50) NULL
is_active               BOOLEAN DEFAULT TRUE
order                   INTEGER DEFAULT 0
created_at              TIMESTAMP
updated_at              TIMESTAMP
deleted_at              TIMESTAMP NULL (soft delete)

FOREIGN KEY (expense_category_id) REFERENCES expense_categories(id) ON DELETE CASCADE
INDEX idx_category_active (expense_category_id, is_active)
INDEX idx_category_order (expense_category_id, order)
```

**Exemples** :

- Catégorie "Dépenses Fixes" → Sous-catégories : Loyer, Internet, Électricité, Gaz, Eau, Transport
- Catégorie "Épargne" → Sous-catégories : Mariage, NSIA, 10 ans, OMCG
- Catégorie "Loisirs" → Sous-catégories : Restaurants, Shopping, Sorties

---

### 7. `expenses`

Dépenses de l'utilisateur avec support de récurrence et planification.

```sql
id                      BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT
user_id                 BIGINT UNSIGNED NOT NULL
expense_category_id     BIGINT UNSIGNED NOT NULL
expense_subcategory_id  BIGINT UNSIGNED NULL
amount                  DECIMAL(15,2) NOT NULL
date                    DATE NOT NULL
description             TEXT NULL
notes                   TEXT NULL
payment_method          ENUM('cash', 'card', 'transfer', 'mobile_money', 'other') DEFAULT 'cash'
recurrence_type         ENUM('one-time', 'monthly') DEFAULT 'one-time'
status                  ENUM('active', 'planned') DEFAULT 'active'
parent_expense_id       BIGINT UNSIGNED NULL (référence à la dépense source si clonée)
is_cloned               BOOLEAN DEFAULT FALSE
created_at              TIMESTAMP
updated_at              TIMESTAMP
deleted_at              TIMESTAMP NULL (soft delete)

FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
FOREIGN KEY (expense_category_id) REFERENCES expense_categories(id) ON DELETE RESTRICT
FOREIGN KEY (expense_subcategory_id) REFERENCES expense_subcategories(id) ON DELETE SET NULL
FOREIGN KEY (parent_expense_id) REFERENCES expenses(id) ON DELETE SET NULL
INDEX idx_user_date (user_id, date)
INDEX idx_user_category (user_id, expense_category_id)
INDEX idx_user_status (user_id, status)
INDEX idx_recurrence (recurrence_type, status)
INDEX idx_month_year (user_id, YEAR(date), MONTH(date))
INDEX idx_payment_method (payment_method)
```

**Notes** :

- `recurrence_type = 'monthly'` → sera clonée automatiquement chaque mois
- `status = 'planned'` → créée en avance, ne compte pas dans les calculs avant la date
- `parent_expense_id` → permet de tracer la lignée des clones
- `ON DELETE RESTRICT` pour `expense_category_id` → empêche suppression d'une catégorie avec des dépenses

---

### 8. `savings_accounts`

Comptes d'épargne avec objectifs.

```sql
id                      BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT
user_id                 BIGINT UNSIGNED NOT NULL
name                    VARCHAR(100) NOT NULL
description             TEXT NULL
current_balance         DECIMAL(15,2) DEFAULT 0.00
target_amount           DECIMAL(15,2) NULL (objectif d'épargne)
target_date             DATE NULL (date cible pour atteindre l'objectif)
color                   VARCHAR(7) DEFAULT '#10B981'
icon                    VARCHAR(50) NULL
is_active               BOOLEAN DEFAULT TRUE
order                   INTEGER DEFAULT 0
created_at              TIMESTAMP
updated_at              TIMESTAMP
deleted_at              TIMESTAMP NULL (soft delete)

FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
INDEX idx_user_active (user_id, is_active)
INDEX idx_user_order (user_id, order)
```

**Exemples** : Épargne mariage, NSIA, 10 ans, Santé & imprévus, OMCG

---

### 9. `savings_transactions`

Mouvements (versements/retraits) des comptes d'épargne.

```sql
id                      BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT
savings_account_id      BIGINT UNSIGNED NOT NULL
type                    ENUM('deposit', 'withdrawal') NOT NULL
amount                  DECIMAL(15,2) NOT NULL
date                    DATE NOT NULL
description             TEXT NULL
balance_after           DECIMAL(15,2) NOT NULL (solde après transaction)
created_at              TIMESTAMP
updated_at              TIMESTAMP
deleted_at              TIMESTAMP NULL (soft delete)

FOREIGN KEY (savings_account_id) REFERENCES savings_accounts(id) ON DELETE CASCADE
INDEX idx_account_date (savings_account_id, date)
INDEX idx_account_type (savings_account_id, type)
```

**Notes** :

- `balance_after` → permet de reconstituer l'historique des soldes
- Lors d'un versement/retrait, mettre à jour `savings_accounts.current_balance`

---

### 10. `budgets`

Résumé mensuel du budget (calculé automatiquement ou manuellement).

```sql
id                      BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT
user_id                 BIGINT UNSIGNED NOT NULL
month                   TINYINT NOT NULL (1-12)
year                    SMALLINT NOT NULL
total_income            DECIMAL(15,2) DEFAULT 0.00
total_expenses          DECIMAL(15,2) DEFAULT 0.00
total_savings           DECIMAL(15,2) DEFAULT 0.00
balance                 DECIMAL(15,2) DEFAULT 0.00 (total_income - total_expenses - total_savings)
notes                   TEXT NULL
created_at              TIMESTAMP
updated_at              TIMESTAMP

FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
UNIQUE KEY unique_user_month_year (user_id, month, year)
INDEX idx_user_year (user_id, year)
INDEX idx_user_month_year (user_id, year, month)
```

**Notes** :

- Table de cache/résumé pour accélérer les requêtes
- Recalculée automatiquement lorsqu'un revenu/dépense est ajouté/modifié/supprimé
- Optionnel : peut être calculé à la volée au lieu d'être stocké

---

### 11. `category_budgets`

Budgets par catégorie et par mois (optionnel, pour suivi détaillé).

```sql
id                      BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT
user_id                 BIGINT UNSIGNED NOT NULL
expense_category_id     BIGINT UNSIGNED NOT NULL
month                   TINYINT NOT NULL (1-12)
year                    SMALLINT NOT NULL
budgeted_amount         DECIMAL(15,2) NOT NULL (montant prévu)
actual_amount           DECIMAL(15,2) DEFAULT 0.00 (montant réel dépensé)
variance                DECIMAL(15,2) DEFAULT 0.00 (écart : actual - budgeted)
variance_percentage     DECIMAL(5,2) DEFAULT 0.00 (% d'écart)
created_at              TIMESTAMP
updated_at              TIMESTAMP

FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
FOREIGN KEY (expense_category_id) REFERENCES expense_categories(id) ON DELETE CASCADE
UNIQUE KEY unique_category_month_year (user_id, expense_category_id, month, year)
INDEX idx_user_month_year (user_id, year, month)
```

**Notes** :

- Permet de comparer budget prévu vs réel par catégorie
- `variance` positif = dépassement, négatif = économie
- Recalculé automatiquement

---

## Tables secondaires (optionnelles pour MVP)

### 12. `recurring_schedules`

Planning des récurrences (alternative à la logique dans `incomes`/`expenses`).

```sql
id                      BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT
user_id                 BIGINT UNSIGNED NOT NULL
entity_type             ENUM('income', 'expense') NOT NULL
entity_id               BIGINT UNSIGNED NOT NULL (income_id ou expense_id)
recurrence_type         ENUM('monthly', 'weekly', 'yearly') NOT NULL
day_of_month            TINYINT NULL (1-28)
day_of_week             TINYINT NULL (0-6)
month_of_year           TINYINT NULL (1-12)
next_clone_date         DATE NOT NULL
is_active               BOOLEAN DEFAULT TRUE
created_at              TIMESTAMP
updated_at              TIMESTAMP

FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
INDEX idx_next_clone (next_clone_date, is_active)
INDEX idx_user_active (user_id, is_active)
```

**Note** : Cette table centralise la logique de récurrence, mais peut être remplacée par un Job Laravel qui lit directement `incomes` et `expenses`.

---

### 13. `notifications`

Notifications pour l'utilisateur (alertes de dépassement, rappels, etc.).

```sql
id                      BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT
user_id                 BIGINT UNSIGNED NOT NULL
type                    VARCHAR(50) NOT NULL (budget_exceeded, saving_goal_reached, etc.)
title                   VARCHAR(255) NOT NULL
message                 TEXT NOT NULL
data                    JSON NULL (données supplémentaires)
is_read                 BOOLEAN DEFAULT FALSE
read_at                 TIMESTAMP NULL
created_at              TIMESTAMP
updated_at              TIMESTAMP

FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
INDEX idx_user_unread (user_id, is_read)
INDEX idx_user_created (user_id, created_at)
```

---

## Relations entre tables

### Schéma simplifié

```
users
  ├── user_settings (1:1)
  ├── income_categories (1:N)
  ├── incomes (1:N)
  │     └── income_category (N:1)
  ├── expense_categories (1:N)
  │     └── expense_subcategories (1:N)
  ├── expenses (1:N)
  │     ├── expense_category (N:1)
  │     └── expense_subcategory (N:1)
  ├── savings_accounts (1:N)
  │     └── savings_transactions (1:N)
  ├── budgets (1:N)
  └── category_budgets (1:N)
```

---

## Indexes et Performances

### Indexes critiques

1. **user_id** sur toutes les tables principales (filtrage par utilisateur)
2. **date** sur `incomes` et `expenses` (requêtes par période)
3. **YEAR(date), MONTH(date)** sur `incomes` et `expenses` (agrégations mensuelles)
4. **status** sur `incomes` et `expenses` (filtrer actif vs planifié)
5. **recurrence_type** sur `incomes` et `expenses` (identifier ce qui doit être cloné)

### Indexes composites

```sql
-- Récupérer les revenus actifs d'un utilisateur pour un mois
INDEX idx_user_month_status ON incomes(user_id, YEAR(date), MONTH(date), status);

-- Récupérer les dépenses par catégorie pour un mois
INDEX idx_user_category_month ON expenses(user_id, expense_category_id, YEAR(date), MONTH(date));

-- Identifier les éléments à cloner
INDEX idx_clone_ready ON incomes(user_id, recurrence_type, status);
INDEX idx_clone_ready ON expenses(user_id, recurrence_type, status);
```

---

## Règles métier et contraintes

### 1. Clonage automatique mensuel

**Logique de clonage (Job Laravel quotidien à 00h00)** :

```php
// Pseudo-code
$today = Carbon::today();
$lastDayOfMonth = $today->isLastOfMonth();

if ($lastDayOfMonth) {
    // Cloner tous les revenus mensuels actifs
    $incomes = Income::where('recurrence_type', 'monthly')
                     ->where('status', 'active')
                     ->get();

    foreach ($incomes as $income) {
        Income::create([
            'user_id' => $income->user_id,
            'income_category_id' => $income->income_category_id,
            'name' => $income->name,
            'amount' => $income->amount,
            'date' => $today->copy()->addMonth()->startOfMonth(),
            'type' => $income->type,
            'recurrence_type' => 'monthly',
            'status' => 'active',
            'parent_income_id' => $income->id,
            'is_cloned' => true,
        ]);
    }

    // Même logique pour les dépenses
    $expenses = Expense::where('recurrence_type', 'monthly')
                       ->where('status', 'active')
                       ->get();

    foreach ($expenses as $expense) {
        Expense::create([...]);
    }
}
```

### 2. Activation des revenus/dépenses planifiés

**Job Laravel quotidien** :

```php
// Pseudo-code
$today = Carbon::today();

// Activer les revenus planifiés dont la date est arrivée
Income::where('status', 'planned')
      ->where('date', '<=', $today)
      ->update(['status' => 'active']);

// Même chose pour les dépenses
Expense::where('status', 'planned')
       ->where('date', '<=', $today)
       ->update(['status' => 'active']);
```

### 3. Recalcul des budgets

**Trigger ou Observer Laravel** :

```php
// À chaque création/modification/suppression d'income ou expense
public function updated(Income $income)
{
    $budget = Budget::firstOrCreate([
        'user_id' => $income->user_id,
        'month' => $income->date->month,
        'year' => $income->date->year,
    ]);

    // Recalculer
    $budget->total_income = Income::where('user_id', $income->user_id)
                                  ->whereYear('date', $income->date->year)
                                  ->whereMonth('date', $income->date->month)
                                  ->where('status', 'active')
                                  ->sum('amount');

    // Idem pour expenses et savings
    $budget->save();
}
```

### 4. Soft deletes

- Toutes les tables principales utilisent `deleted_at` pour conserver l'historique
- Les suppressions ne sont jamais physiques (sauf demande explicite)

### 5. Contraintes d'intégrité

```sql
-- Empêcher la suppression d'une catégorie avec des dépenses actives
ALTER TABLE expenses
ADD CONSTRAINT fk_category_restrict
FOREIGN KEY (expense_category_id)
REFERENCES expense_categories(id)
ON DELETE RESTRICT;

-- Montants toujours positifs
ALTER TABLE incomes ADD CONSTRAINT chk_amount_positive CHECK (amount > 0);
ALTER TABLE expenses ADD CONSTRAINT chk_amount_positive CHECK (amount > 0);
ALTER TABLE savings_transactions ADD CONSTRAINT chk_amount_positive CHECK (amount > 0);
```

---

## Migrations Laravel

### Ordre de création des migrations

1. `create_user_settings_table`
2. `create_income_categories_table`
3. `create_incomes_table`
4. `create_expense_categories_table`
5. `create_expense_subcategories_table`
6. `create_expenses_table`
7. `create_savings_accounts_table`
8. `create_savings_transactions_table`
9. `create_budgets_table`
10. `create_category_budgets_table`
11. `create_notifications_table` (optionnel)

---

## Seeders

### Données par défaut à créer

**1. Catégories de revenus par défaut** (pour chaque nouvel utilisateur) :

- Salaire
- Freelance
- Projets
- Autres

**2. Catégories de dépenses par défaut** :

- **Dépenses Fixes** (is_recurring = true)
  - Dîme
  - Loyer
  - Électricité
  - Gaz
  - Eau
  - Internet
  - Transport
  - Alimentation
  - Pressing
  - Besoins maison

- **Épargne** (is_recurring = true)
  - Épargne mariage
  - Épargne NSIA
  - Épargne 10 ans
  - Santé & imprévus
  - OMCG

- **Loisirs & Extras** (is_recurring = false)
  - Sorties/Restaurants
  - Shopping (Vêtements, Tech, Maison)

**3. User settings par défaut** :

- Devise : XOF
- Format date : d/m/Y
- Mois commence : 1er du mois
- Seuil d'alerte : 90%
- Taux d'épargne cible : 20%

---

## Requêtes courantes optimisées

### 1. Revenus du mois en cours

```sql
SELECT * FROM incomes
WHERE user_id = ?
  AND YEAR(date) = YEAR(CURDATE())
  AND MONTH(date) = MONTH(CURDATE())
  AND status = 'active'
ORDER BY date ASC;
```

### 2. Dépenses par catégorie pour un mois

```sql
SELECT
    ec.name AS category_name,
    SUM(e.amount) AS total_amount,
    COUNT(e.id) AS expense_count
FROM expenses e
JOIN expense_categories ec ON e.expense_category_id = ec.id
WHERE e.user_id = ?
  AND YEAR(e.date) = ?
  AND MONTH(e.date) = ?
  AND e.status = 'active'
GROUP BY ec.id, ec.name
ORDER BY total_amount DESC;
```

### 3. Progression vers objectifs d'épargne

```sql
SELECT
    sa.name,
    sa.current_balance,
    sa.target_amount,
    ROUND((sa.current_balance / sa.target_amount) * 100, 2) AS progress_percentage,
    DATEDIFF(sa.target_date, CURDATE()) AS days_remaining
FROM savings_accounts sa
WHERE sa.user_id = ?
  AND sa.is_active = TRUE
  AND sa.target_amount IS NOT NULL
ORDER BY progress_percentage DESC;
```

### 4. Comparaison mensuelle (mois actuel vs mois précédent)

```sql
SELECT
    'current' AS period,
    SUM(amount) AS total
FROM expenses
WHERE user_id = ?
  AND YEAR(date) = YEAR(CURDATE())
  AND MONTH(date) = MONTH(CURDATE())
  AND status = 'active'

UNION ALL

SELECT
    'previous' AS period,
    SUM(amount) AS total
FROM expenses
WHERE user_id = ?
  AND YEAR(date) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))
  AND MONTH(date) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))
  AND status = 'active';
```

---

## Sécurité et confidentialité

### 1. Protection des données

- Toutes les requêtes DOIVENT filtrer par `user_id`
- Utiliser les Policy Laravel pour autoriser l'accès
- Jamais exposer les données d'un utilisateur à un autre

### 2. Validation des données

- Montants : toujours positifs, max 15 chiffres
- Dates : format valide, pas de dates futures pour status = 'active'
- Types ENUM : valider côté backend

### 3. Soft deletes

- Conserver l'historique pour audit
- Ne jamais supprimer physiquement sauf demande RGPD

---

## Points d'extension future (Phase 2)

1. **Multi-devises** : Ajouter table `currencies` et `exchange_rates`
2. **Pièces jointes** : Table `attachments` pour reçus/factures
3. **Partage de budget** : Table `budget_shares` pour couples/familles
4. **Alertes avancées** : Table `alert_rules` avec conditions personnalisées
5. **Récurrence complexe** : Ajouter `recurrence_type = 'weekly', 'yearly', 'custom'`
6. **Prévisions IA** : Table `predictions` pour suggestions basées sur l'historique

---

## Résumé

Cette structure de base de données permet :

✅ Multi-utilisateurs avec isolation complète
✅ Récurrence automatique mensuelle (revenus + dépenses)
✅ Planification future avec statut "planifié"
✅ Clonage intelligent par catégorie
✅ Historique complet avec soft deletes
✅ Performance optimisée avec indexes
✅ Extensibilité pour fonctionnalités futures

**Nombre total de tables MVP** : 11 tables principales + 1 optionnelle (notifications)
