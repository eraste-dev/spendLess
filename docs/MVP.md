# MVP - Application de Gestion de Finance Personnelle

## Vue d'ensemble
Application web de gestion de budget personnel permettant de suivre les revenus, dépenses, épargnes avec analyses mensuelles et annuelles.

## Objectifs du MVP
- Remplacer la gestion Excel actuelle
- Offrir une solution paramétrable et flexible
- Fournir des analyses et exports détaillés
- Éliminer les "trous" et erreurs de saisie

---

## 1. Fonctionnalités Core

### 1.1 Gestion des Revenus (Entrées)

#### 1.1.1 Création de revenus
- **Informations de base**
  - Nom/libellé (ex: Salaire, Freelance, Autres)
  - Montant en FCFA
  - Date de réception
  - Type : Fixe ou Variable
  - Notes/description
  - Récurrence : **Ponctuel** ou **Mensuel**

#### 1.1.2 Système de récurrence automatique des revenus
- **Revenus mensuels (ex: Salaire)**
  - Marqués comme "Mensuel" lors de la création
  - **Clone automatique chaque mois** :
    - Quand le mois se termine (dernier jour à 23h59), le système crée automatiquement une nouvelle entrée de revenu pour le mois suivant avec le même montant
    - Le revenu cloné porte la date du 1er jour du nouveau mois
    - Chaque entrée mensuelle est indépendante et modifiable sans affecter les autres mois

- **Revenus ponctuels (ex: Freelance, Prime)**
  - Ne sont PAS clonés automatiquement
  - Doivent être créés manuellement chaque fois

#### 1.1.3 Planification future (optionnel)
- **Mode planification activé par l'utilisateur**
  - L'utilisateur peut décider de "planifier" ses revenus pour les 3-6 prochains mois
  - Lorsque activé, le système crée les revenus mensuels pour les mois futurs
  - **Règle importante** : Les revenus créés en mode planification ont un statut "planifié" jusqu'à ce que la date arrive
  - Une fois la date du mois atteinte, le statut passe à "actif" et compte dans les calculs

- **Gestion de la planification**
  - Voir les revenus planifiés (badge "Planifié")
  - Modifier un revenu planifié avant qu'il devienne actif
  - Annuler une planification

#### 1.1.4 Catégories de revenus personnalisables
- **Par utilisateur** : Chaque utilisateur a ses propres catégories
- Créer/Modifier/Supprimer des catégories
- Exemples : Salaire, Freelance, Projets, Investissements, Dividendes, etc.

### 1.2 Gestion des Catégories de Dépenses

#### 1.2.1 Structure hiérarchique
- **Structure : Catégorie → Sous-catégorie**
- **Par utilisateur** : Chaque utilisateur définit ses propres catégories et sous-catégories

**Catégories principales par défaut (paramétrables) :**

1. **Dépenses Fixes**
   - Sous-catégories : Dîme, Loyer, Électricité, Gaz, Eau, Internet, Transport, Alimentation, Pressing, Besoins maison

2. **Épargne**
   - Sous-catégories : Épargne mariage, Épargne NSIA, Épargne 10 ans, Santé & imprévus, OMCG

3. **Loisirs & Extras**
   - Sous-catégories : Sorties/Restaurants, Shopping (Vêtements, Tech, Maison)

#### 1.2.2 Configuration par catégorie
- Nom et description
- Pourcentage cible du budget (ex: Dîme 10%, Loyer 23%)
- Budget mensuel fixe (optionnel)
- Couleur pour visualisation
- Actif/Inactif
- **Récurrence mensuelle** : Active ou non

#### 1.2.3 CRUD complet
- Créer, modifier, supprimer catégories et sous-catégories
- Réorganiser l'ordre d'affichage
- Archiver plutôt que supprimer (conservation de l'historique)

### 1.3 Gestion des Dépenses

#### 1.3.1 Enregistrement de dépense
- Montant en FCFA
- Date de la dépense
- Catégorie et sous-catégorie (sélection)
- Description/notes
- Type : **Ponctuelle** ou **Récurrente Mensuelle**
- Moyen de paiement : Liquide, Carte, Virement, Mobile Money

#### 1.3.2 Système de récurrence automatique des dépenses

**Dépenses mensuelles récurrentes (ex: Loyer, Internet, Transport)**

- Marquées comme "Récurrente Mensuelle" lors de la création
- **Clone automatique chaque mois** :
  - Quand le mois se termine (dernier jour à 23h59), le système crée automatiquement une nouvelle dépense pour le mois suivant
  - La dépense clonée porte la date du 1er jour du nouveau mois (ou la date configurée)
  - Chaque dépense mensuelle est indépendante et modifiable sans affecter les autres mois
  - Le montant peut être ajusté manuellement après création

**Dépenses ponctuelles (ex: Shopping, Restaurant)**

- Ne sont PAS clonées automatiquement
- Doivent être créées manuellement à chaque fois

#### 1.3.3 Planification future (optionnel)

**Mode planification activé par l'utilisateur**

- L'utilisateur peut activer la planification pour voir les 3-6 prochains mois
- Lorsque activé, le système crée les dépenses récurrentes pour les mois futurs
- **Règle importante** : Les dépenses planifiées ont un statut "planifié" jusqu'à la date du mois
- Une fois le mois atteint, le statut passe à "actif" et compte dans les calculs réels
- Les dépenses planifiées permettent de simuler le budget futur

**Gestion de la planification**

- Voir les dépenses planifiées (badge "Planifié")
- Modifier une dépense planifiée avant qu'elle devienne active
- Annuler une planification sans affecter l'historique

#### 1.3.4 Clone mensuel par catégorie

**Système intelligent de clonage**

- Au lieu de cloner dépense par dépense, le système peut cloner **toute une catégorie**
- Exemple : Catégorie "Dépenses Fixes" avec 8 sous-catégories → Clone automatique des 8 dépenses
- **Condition de clonage** : Seules les catégories marquées "Récurrence mensuelle = Active" sont clonées
- **Date de création** : Les clones ne sont créés QUE lorsque le mois arrive (pas avant, sauf mode planification)

**Configuration du clonage par catégorie**

- Dans les paramètres de chaque catégorie :
  - Activer/désactiver le clonage mensuel
  - Définir la date de création dans le mois (1er, 5, 15, etc.)
  - Conserver ou non les montants du mois précédent comme base

#### 1.3.5 Saisie rapide et en masse

- Formulaire de saisie unique
- Import CSV/Excel
- Saisie multiple (ajout de plusieurs dépenses d'un coup)

### 1.4 Système d'Épargne
- **Comptes d'épargne multiples**
  - Nom du compte (ex: Épargne mariage, NSIA, 10 ans)
  - Solde actuel
  - Objectif d'épargne (montant cible)
  - Date cible (optionnel)
  - Progression visuelle (%)

- **Mouvements d'épargne**
  - Versements
  - Retraits
  - Historique complet

- **Épargne automatique**
  - Définir montant/pourcentage à épargner automatiquement
  - Déduction automatique du budget mensuel

### 1.5 Gestion des Périodes
- **Vue par mois**
  - Sélection mois/année
  - Navigation rapide (mois précédent/suivant)
  - Vue du mois en cours par défaut

- **Calculs mensuels automatiques**
  - Total des entrées
  - Total des dépenses par catégorie
  - Total des épargnes
  - Solde restant
  - Pourcentages réels vs pourcentages cibles

---

## 2. Tableau de Bord (Dashboard)

### 2.1 Vue d'ensemble du mois en cours
- **Résumé financier**
  - Total des revenus du mois
  - Total des dépenses du mois
  - Total épargné
  - Solde restant
  - Pourcentage dépensé vs budget total

- **Graphiques**
  - Répartition des dépenses par catégorie (Camembert)
  - Évolution hebdomadaire des dépenses (Barres)
  - Progression vers objectifs d'épargne (Barres de progression)

- **Alertes visuelles**
  - Catégories dépassant le budget prévu (rouge)
  - Catégories proches de la limite (orange)
  - Objectifs d'épargne atteints (vert)

### 2.2 Indicateurs clés (KPI)
- Ratio Dépenses fixes / Revenus (cible : ≤ 50%)
- Taux d'épargne mensuel
- Dépense moyenne journalière
- Comparaison vs mois précédent (+/- %)

---

## 3. Analyses et Rapports

### 3.1 Analyse Mensuelle
- **Vue détaillée par mois**
  - Tableau complet : Catégorie | Sous-catégorie | Montant | % | Notes
  - Comparaison Budget prévu vs Réel
  - Écart en montant et en pourcentage

- **Répartition des dépenses**
  - Par catégorie principale
  - Par sous-catégorie
  - Évolution semaine par semaine

### 3.2 Analyse Annuelle
- **Vue annuelle (12 mois)**
  - Total des revenus annuels
  - Total des dépenses annuelles
  - Total épargné sur l'année
  - Solde net annuel

- **Graphiques annuels**
  - Courbe d'évolution des revenus et dépenses par mois
  - Comparaison catégories principales sur 12 mois
  - Progression de l'épargne totale

- **Statistiques**
  - Mois le plus dépensier
  - Mois le plus économe
  - Catégorie la plus dépensière
  - Moyenne mensuelle par catégorie

### 3.3 Rapports comparatifs
- Comparaison mois à mois (sélection de 2+ mois)
- Comparaison année à année
- Tendances et prévisions

---

## 4. Exports

### 4.1 Formats d'export
- **Excel (.xlsx)**
  - Format similaire à l'Excel actuel
  - Feuilles multiples : Mois par mois, Année, Catégories
  - Formules et mise en forme préservées

- **PDF**
  - Rapport formaté et imprimable
  - Graphiques inclus
  - Personnalisation du contenu

- **CSV**
  - Export brut pour traitement externe
  - Compatible avec d'autres outils

### 4.2 Options d'export
- Export d'un mois spécifique
- Export d'une période (plusieurs mois)
- Export annuel
- Export par catégorie
- Export des épargnes uniquement

---

## 5. Paramétrage de l'Application

### 5.1 Paramètres Généraux
- **Devise**
  - Devise principale : FCFA (XOF)
  - Format d'affichage (ex: 100 000,00 XOF)

- **Période de référence**
  - Début du mois financier (1er du mois ou autre date)
  - Découpage hebdomadaire (semaine 1-4)

- **Préférences d'affichage**
  - Thème : Clair, Sombre, Système
  - Langue : Français
  - Format de date : JJ/MM/AAAA

### 5.2 Budgets et Objectifs
- **Budget mensuel global**
  - Définir un revenu mensuel moyen/cible
  - Définir un budget de dépenses max

- **Objectifs d'épargne**
  - Taux d'épargne cible (ex: 20% des revenus)
  - Montants minimums à épargner

- **Seuils d'alerte**
  - % de dépassement acceptable avant alerte
  - Notifications (email, dans l'app)

### 5.3 Gestion des Catégories (Admin)
- Interface complète CRUD
- Ordre d'affichage personnalisable
- Import/Export de configurations de catégories

---

## 6. Import de Données

### 6.1 Import Excel
- **Import de l'historique**
  - Parser le format Excel existant
  - Mapper colonnes → champs de l'app
  - Validation des données
  - Prévisualisation avant import

- **Import récurrent**
  - Télécharger fichier Excel/CSV
  - Ajout de nouvelles transactions

### 6.2 Migration initiale
- Assistant de première configuration
- Import des 3+ derniers mois (historique)
- Configuration des catégories basées sur l'Excel

---

## 7. Interface Utilisateur

### 7.1 Navigation principale
- **Menu**
  - Tableau de bord
  - Revenus
  - Dépenses
  - Épargnes
  - Analyses
  - Paramètres

### 7.2 Pages principales

#### Page : Tableau de bord
- Vue d'ensemble mois en cours
- Graphiques et KPI
- Actions rapides (Ajouter dépense, Ajouter revenu)

#### Page : Revenus
- Liste des revenus du mois
- Filtres : Période, Type, Catégorie
- Bouton "Ajouter un revenu"
- Total du mois

#### Page : Dépenses
- Liste des dépenses du mois
- Filtres : Période, Catégorie, Sous-catégorie, Moyen de paiement
- Recherche par description
- Bouton "Ajouter une dépense"
- Vue tableau avec colonnes triables

#### Page : Épargnes
- Liste des comptes d'épargne
- Vue détaillée par compte (solde, objectif, progression)
- Historique des mouvements
- Bouton "Nouveau versement"

#### Page : Analyses
- Onglets : Mensuel, Annuel, Comparatif
- Sélecteur de période
- Graphiques interactifs
- Bouton "Exporter"

#### Page : Paramètres
- Sous-sections :
  - Profil utilisateur
  - Paramètres généraux
  - Gestion des catégories
  - Budgets et objectifs
  - Import de données
  - Apparence

### 7.3 Composants réutilisables
- Formulaire de dépense/revenu
- Sélecteur de catégorie/sous-catégorie
- Sélecteur de date/période
- Graphiques (Chart.js ou Recharts)
- Tableaux triables/filtrables

---

## 8. Stack Technique

### 8.1 Backend (Existant Laravel 12)
- **API REST**
  - Endpoints pour CRUD : Revenus, Dépenses, Catégories, Épargnes
  - Repository Pattern (déjà en place)
  - Validation via Form Requests

- **Base de données**
  - Migrations pour nouvelles tables :
    - `incomes` (revenus)
    - `expense_categories` (catégories)
    - `expense_subcategories` (sous-catégories)
    - `expenses` (dépenses)
    - `savings_accounts` (comptes d'épargne)
    - `savings_transactions` (mouvements d'épargne)
    - `budgets` (budgets mensuels)
    - `user_settings` (paramètres utilisateur)

- **Logique métier**
  - Calculs automatiques (totaux, pourcentages, soldes)
  - Génération de dépenses récurrentes (Job Laravel)
  - Export Excel/PDF (Laravel Excel, DomPDF)

### 8.2 Frontend (Existant React 19 + TypeScript + Inertia)
- **Pages Inertia**
  - Dashboard
  - Incomes/Index, Incomes/Create, Incomes/Edit
  - Expenses/Index, Expenses/Create, Expenses/Edit
  - Savings/Index, Savings/Show
  - Analytics/Monthly, Analytics/Yearly
  - Settings/Index

- **Composants UI**
  - Réutiliser Radix UI + Tailwind CSS v4
  - Graphiques : Recharts ou Chart.js
  - Tableaux : TanStack Table
  - Formulaires : React Hook Form + Zod validation

- **State Management**
  - Props Inertia pour données serveur
  - React Context pour état global (thème, préférences)
  - React Query pour cache (si nécessaire)

### 8.3 Authentification
- Utiliser Laravel Fortify existant
- Une seule personne par compte (multi-utilisateurs = Phase 2)

---

## 9. Modèles de Données (Principales Tables)

### 9.1 `incomes`
```
id, user_id, category, amount, date, type (fixed/variable),
recurrence (one-time/monthly/weekly/yearly), description, created_at, updated_at
```

### 9.2 `expense_categories`
```
id, user_id, name, target_percentage, target_amount, color,
order, is_active, created_at, updated_at
```

### 9.3 `expense_subcategories`
```
id, category_id, name, description, order, created_at, updated_at
```

### 9.4 `expenses`
```
id, user_id, category_id, subcategory_id, amount, date, description,
payment_method, is_recurring, recurrence_frequency, notes,
created_at, updated_at
```

### 9.5 `savings_accounts`
```
id, user_id, name, current_balance, target_amount, target_date,
color, created_at, updated_at
```

### 9.6 `savings_transactions`
```
id, savings_account_id, type (deposit/withdrawal), amount, date,
description, created_at, updated_at
```

### 9.7 `budgets`
```
id, user_id, month, year, total_income, total_expenses,
total_savings, notes, created_at, updated_at
```

### 9.8 `user_settings`
```
id, user_id, currency, date_format, week_start_day,
alert_threshold, savings_rate_target, created_at, updated_at
```

---

## 10. User Stories (MVP)

### En tant qu'utilisateur, je veux :

1. **Revenus**
   - Enregistrer mes revenus mensuels (salaire, freelance)
   - Voir le total de mes revenus par mois
   - Modifier/supprimer un revenu

2. **Catégories**
   - Créer mes propres catégories de dépenses
   - Créer des sous-catégories
   - Définir un budget/pourcentage cible par catégorie
   - Réorganiser mes catégories

3. **Dépenses**
   - Enregistrer une dépense rapidement (catégorie, montant, date)
   - Voir toutes mes dépenses du mois
   - Filtrer par catégorie/date
   - Modifier/supprimer une dépense
   - Créer des dépenses récurrentes (loyer, internet)

4. **Épargne**
   - Créer plusieurs comptes d'épargne
   - Définir des objectifs d'épargne
   - Enregistrer des versements/retraits
   - Voir ma progression vers mes objectifs

5. **Tableau de bord**
   - Voir d'un coup d'œil ma situation financière du mois
   - Voir quelles catégories dépassent le budget
   - Voir combien il me reste à dépenser

6. **Analyses**
   - Voir mes dépenses par catégorie sur un mois
   - Comparer mes dépenses sur plusieurs mois
   - Voir mon évolution annuelle (12 mois)
   - Identifier mes postes de dépenses les plus importants

7. **Exports**
   - Exporter un mois en Excel (format similaire à mon fichier actuel)
   - Exporter un rapport annuel en PDF
   - Exporter mes données brutes en CSV

8. **Import**
   - Importer mon historique Excel existant
   - Importer des dépenses en masse via CSV

9. **Paramétrage**
   - Personnaliser l'apparence (thème clair/sombre)
   - Configurer mes préférences (devise, format de date)
   - Définir mes objectifs d'épargne globaux

---

## 11. Hors Scope du MVP (Phase 2+)

- Multi-utilisateurs / Partage de budget (couple, famille)
- Application mobile native
- Multi-devises avec conversion automatique
- Synchronisation bancaire automatique
- Notifications push/email avancées
- Planification budgétaire prédictive (IA)
- Gestion de dettes/crédits
- Objectifs financiers à long terme (retraite, immobilier)
- Scan de reçus/factures (OCR)
- Intégration avec services tiers (Paypal, Wave, etc.)

---

## 12. Livrables du MVP

### 12.1 Fonctionnalités
- ✅ CRUD Revenus
- ✅ CRUD Catégories/Sous-catégories
- ✅ CRUD Dépenses
- ✅ CRUD Comptes d'épargne
- ✅ Tableau de bord avec graphiques
- ✅ Analyse mensuelle et annuelle
- ✅ Export Excel, PDF, CSV
- ✅ Import Excel historique
- ✅ Paramètres utilisateur

### 12.2 Pages
- ✅ Dashboard
- ✅ Gestion des revenus
- ✅ Gestion des dépenses
- ✅ Gestion des épargnes
- ✅ Analyses (mensuelle, annuelle)
- ✅ Paramètres

### 12.3 Tests
- Tests unitaires (Pest PHP)
- Tests fonctionnels des endpoints API
- Tests des calculs (totaux, pourcentages)

---

## 13. Roadmap de Développement

### Phase 1 : Structure de base (Semaine 1-2)
1. Migrations et modèles
2. Repositories et services
3. Controllers API de base
4. Authentication et routing

### Phase 2 : Backend Core (Semaine 2-3)
1. Logique métier (calculs, validations)
2. Form Requests
3. API Resources
4. Tests unitaires

### Phase 3 : Frontend Core (Semaine 3-4)
1. Pages Inertia (CRUD)
2. Composants UI (formulaires, tableaux)
3. Layouts et navigation

### Phase 4 : Dashboard et Graphiques (Semaine 4-5)
1. Tableau de bord
2. Intégration graphiques
3. KPI et indicateurs

### Phase 5 : Analyses et Exports (Semaine 5-6)
1. Pages d'analyse
2. Exports Excel/PDF/CSV
3. Graphiques avancés

### Phase 6 : Paramétrage et Import (Semaine 6-7)
1. Page paramètres
2. Gestion catégories
3. Import Excel historique

### Phase 7 : Tests et Refinement (Semaine 7-8)
1. Tests end-to-end
2. Corrections bugs
3. Optimisations
4. Documentation

---

## 14. Critères de Succès du MVP

- ✅ L'utilisateur peut abandonner complètement son Excel
- ✅ Toutes les données de l'Excel peuvent être importées
- ✅ Le format d'export Excel est familier
- ✅ Les calculs sont corrects (totaux, pourcentages)
- ✅ L'interface est intuitive et rapide à utiliser
- ✅ Les analyses fournissent des insights utiles
- ✅ Aucune perte de données (historique préservé)
- ✅ Responsive (desktop + tablette)

---

## 15. Questions à Valider

Avant de commencer le développement, confirmer :

1. **Structure de catégories** : Les 3 catégories principales (Dépenses fixes, Épargne, Loisirs) + sous-catégories listées sont-elles complètes ?

2. **Système de retraits** : Comment gérer les "Retraits 1-4" de l'Excel ? S'agit-il d'une simple vue hebdomadaire du solde restant ?

3. **Récurrence automatique** : Quelles dépenses doivent être créées automatiquement chaque mois ? (Loyer, Internet, Transport, Alimentation, etc.)

4. **Objectifs d'épargne** : Priorité haute pour les barres de progression vers objectifs ?

5. **Import historique** : Combien de mois d'historique à importer ? Format Excel identique pour tous les mois ?

6. **Multi-devises** : Uniquement FCFA ou prévoir EUR, USD également ?

7. **Notifications** : Nécessaires dans le MVP ou Phase 2 ?

---

**Ce MVP est-il conforme à vos attentes ? Y a-t-il des éléments à ajouter, retirer ou modifier ?**
