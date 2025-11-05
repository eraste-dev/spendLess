# Design Mobile-First - Budget App

## Vue d'ensemble

L'application a été complètement repensée avec une approche **mobile-first**, privilégiant l'expérience utilisateur sur mobile tout en restant fonctionnelle et élégante sur desktop.

## Changements Majeurs

### 1. Remplacement du Sidebar par un Header Mobile

#### Avant
- Sidebar fixe sur desktop
- Navigation verticale
- Prend beaucoup d'espace sur petit écran

#### Maintenant
- **Header sticky** en haut de page
- **Sheet/Drawer** pour la navigation mobile (slide depuis la droite)
- **Navigation horizontale** sur desktop
- Logo adaptatif (icône seule sur mobile, texte complet sur desktop)

### 2. Layout Responsive

```
Mobile (<768px)              Desktop (≥768px)
┌─────────────────┐         ┌──────────────────────────┐
│ [Logo] [Menu≡] │         │ [Logo] [Nav] [User] [≡] │
├─────────────────┤         ├──────────────────────────┤
│                 │         │                          │
│   Page Content  │         │     Page Content         │
│                 │         │                          │
│                 │         │                          │
│     [FAB+]      │         │      [Button+]           │
└─────────────────┘         └──────────────────────────┘
```

### 3. Composants Touch-Friendly

#### Cards de Catégories
- **Avant**: Cartes avec boutons "Modifier" et "Supprimer"
- **Maintenant**:
  - Menu dropdown avec icône `⋮` (MoreVertical)
  - Touch target minimum 44x44px
  - Feedback tactile avec `active:bg-muted/50`
  - Texte tronqué avec `truncate` et `line-clamp-2`

#### Formulaires
- **Inputs**: Hauteur `h-12` (48px) pour faciliter la saisie
- **Boutons**: Taille `h-12` avec texte `text-base` (16px)
- **Modal**: Header et footer collants (sticky) pour navigation facile

## Structure des Fichiers

### Nouveaux Composants

```
resources/js/
├── layouts/
│   └── mobile-header-layout.tsx          ✨ Nouveau layout mobile-first
├── components/
│   └── features/
│       └── budget/
│           ├── income-category-card-mobile.tsx      ✨ Card optimisée mobile
│           ├── income-category-list-mobile.tsx      ✨ Liste mobile
│           └── income-category-form-modal-mobile.tsx ✨ Modal mobile
└── pages/
    └── budget/
        └── income-categories/
            └── index.tsx                   🔄 Refactorisé en mobile-first
```

### Composants Supprimés/Remplacés
- ❌ `app-layout.tsx` (sidebar) → ✅ `mobile-header-layout.tsx` (header)
- ❌ Anciennes variantes desktop uniquement

## Features UX Mobile

### 1. Floating Action Button (FAB)

Bouton flottant en bas à droite pour l'action principale:

```tsx
{categories.length > 0 && (
    <Button
        onClick={onCreate}
        className="fixed bottom-6 right-6 size-14 rounded-full shadow-lg"
    >
        <Plus className="size-6" />
    </Button>
)}
```

**Comportement**:
- Visible uniquement si la liste contient des éléments
- Masqué sur l'état vide (CTA principal dans l'empty state)
- Z-index `z-40` pour être au-dessus du contenu mais sous les modals

### 2. Empty States Améliorés

État vide avec illustration et appel à l'action clair:

```tsx
<div className="flex flex-col items-center py-16 px-4 text-center">
    <div className="flex size-16 items-center justify-center rounded-full bg-muted mb-4">
        <Inbox className="size-8 text-muted-foreground" />
    </div>
    <h3 className="text-lg font-semibold mb-2">Aucune catégorie</h3>
    <p className="text-sm text-muted-foreground mb-6 max-w-sm">
        Commencez par créer votre première catégorie
    </p>
    <Button onClick={onCreate} size="lg" className="rounded-full">
        <Plus className="mr-2 size-5" />
        Créer une catégorie
    </Button>
</div>
```

### 3. Modals Adaptés Mobile

**Header sticky** avec bouton de fermeture:
- Header reste visible lors du scroll
- Bouton X tactile (size-icon)
- Titre et description clairs

**Footer sticky** avec actions:
- Actions en bas, toujours accessibles
- Boutons pleine largeur avec `flex-1`
- Hauteur tactile `h-12`

### 4. Navigation Mobile (Sheet)

Drawer qui slide depuis la droite avec:
- **User info** en haut avec avatar
- **Navigation principale** au milieu
- **Actions secondaires** en bas (Paramètres, Déconnexion)
- Width: `280px` pour ne pas couvrir tout l'écran
- Fermeture automatique après navigation

## Breakpoints et Responsive

### Breakpoints Tailwind Utilisés

```css
/* Mobile first */
base: 0-639px      (défaut)
sm:   640px+       (petits tablettes)
md:   768px+       (tablettes et plus)
lg:   1024px+      (desktop)
xl:   1280px+      (grands écrans)
```

### Patterns Appliqués

```tsx
// Typographie
className="text-2xl sm:text-3xl"  // Plus grand sur desktop

// Padding
className="px-4 py-6 md:py-8"     // Plus d'espace vertical sur desktop

// Visibilité
className="hidden md:flex"         // Masqué sur mobile
className="md:hidden"              // Visible seulement sur mobile

// Tailles
className="h-12 text-base"         // Cohérent mobile
className="sm:max-w-[500px]"       // Largeur maximale desktop
```

## Accessibilité (a11y)

### Touch Targets (WCAG 2.1)
- ✅ Tous les boutons font minimum 44x44px
- ✅ Zone de tap généreuse avec padding
- ✅ Espacement entre éléments interactifs

### Contraste et Lisibilité
- ✅ Taille de texte minimum: 14px (`text-sm`)
- ✅ Contraste respecté (theme colors)
- ✅ Focus visible sur navigation clavier

### Navigation
- ✅ Header sticky pour accès permanent au menu
- ✅ Fermeture drawer avec ESC
- ✅ Labels sur tous les inputs

## Performance

### Optimisations Appliquées

1. **Sticky positioning** au lieu de fixed pour les headers
   - Meilleure performance de scroll
   - Moins de repaints

2. **Backdrop blur** conditionnel
   ```tsx
   className="bg-background/95 backdrop-blur
              supports-[backdrop-filter]:bg-background/60"
   ```

3. **Transitions légères**
   ```tsx
   className="transition-colors"     // Seulement couleurs
   className="transition-shadow"     // Seulement ombres
   ```

4. **Truncate et line-clamp**
   - Évite le débordement de texte
   - Meilleure performance DOM

## Migration Guide

### Pour adapter une page existante au mobile-first:

1. **Remplacer le layout**
   ```tsx
   // Avant
   import AppLayout from '@/layouts/app-layout';
   <AppLayout>...</AppLayout>

   // Après
   import MobileHeaderLayout from '@/layouts/mobile-header-layout';
   <MobileHeaderLayout>...</MobileHeaderLayout>
   ```

2. **Créer les variantes mobile des composants**
   - Suffix `-mobile` pour les composants tactiles
   - Card simple avec dropdown au lieu de plusieurs boutons
   - Liste avec empty state optimisé

3. **Ajouter le FAB si applicable**
   ```tsx
   {items.length > 0 && (
       <Button className="fixed bottom-6 right-6 size-14 rounded-full">
           <Plus className="size-6" />
       </Button>
   )}
   ```

4. **Adapter les modals**
   - Utiliser les variantes `-mobile` avec sticky header/footer
   - Inputs `h-12` et `text-base`
   - Footer avec boutons `flex-1`

## Exemples Concrets

### Page Catégories de Revenus

**Avant**: 430 lignes avec sidebar
**Après**: 160 lignes mobile-first

**Améliorations**:
- ✅ Header responsive avec navigation adaptative
- ✅ Cards touch-friendly avec dropdown
- ✅ FAB pour création rapide
- ✅ Modal optimisé mobile avec sticky header/footer
- ✅ Empty state engageant
- ✅ Feedback tactile (active states)

### Checklist Implémentation

Pour chaque nouvelle feature:

- [ ] Header mobile sticky fonctionnel
- [ ] Navigation Sheet/Drawer sur mobile
- [ ] Cards avec touch targets 44px+
- [ ] FAB si action principale
- [ ] Modal avec sticky header/footer
- [ ] Empty state avec icône et CTA
- [ ] Feedback tactile (active:)
- [ ] Typographie responsive
- [ ] Test sur mobile réel
- [ ] Test sur tablette
- [ ] Test sur desktop

## Prochaines Étapes

1. Appliquer le pattern mobile-first aux catégories de dépenses
2. Adapter le dashboard en mobile-first
3. Créer les pages de transactions (revenus/dépenses)
4. Implémenter les rapports et visualisations
5. Ajouter les animations de transition

## Ressources

- [.cursorrules](./.cursorrules) - Guidelines complètes
- [Mobile Header Layout](../resources/js/layouts/mobile-header-layout.tsx)
- [Income Categories](../resources/js/pages/budget/income-categories/index.tsx)
- [Tailwind CSS Docs](https://tailwindcss.com/docs)
- [Radix UI](https://www.radix-ui.com/)
