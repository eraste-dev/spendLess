# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a Laravel 12 + React (Inertia.js) full-stack budget management application with support for multiple domains (Budget, Courses, Assessments, Blog, Certificates). The project uses a mobile-first design philosophy with Tailwind CSS 4.x and TypeScript.

## Tech Stack

**Backend:**
- Laravel 12 (PHP 8.2+)
- Laravel Fortify (authentication)
- Laravel Wayfinder (routing)
- Inertia.js 2.x (server-side rendering bridge)
- SQLite (default database)
- Pest (testing framework)

**Frontend:**
- React 19 (with React Compiler)
- TypeScript
- Inertia.js (React adapter)
- Tailwind CSS 4.x
- Radix UI components
- shadcn/ui component patterns
- Vite 7.x
- Class Variance Authority (component variants)

## Development Commands

### Initial Setup
```bash
composer run setup
# Runs: composer install, copies .env, generates key, migrates DB, npm install, npm run build
```

### Development Server
```bash
composer run dev
# Runs concurrently: Laravel server, queue worker, logs (pail), and Vite dev server
```

### With SSR (Server-Side Rendering)
```bash
composer run dev:ssr
# Build SSR bundle first, then run Laravel server, queue, logs, and SSR server
```

### Testing
```bash
composer run test
# Runs Pest test suite

# Run a single test file
php artisan test --filter=TestName
# or
./vendor/bin/pest tests/Feature/SomeTest.php
```

### Code Quality
```bash
# Format frontend code
npm run format

# Check formatting
npm run format:check

# Lint and auto-fix
npm run lint

# Type checking
npm run types
```

### Laravel Pint (PHP formatting)
```bash
./vendor/bin/pint
```

### Build for Production
```bash
npm run build              # Client-side build
npm run build:ssr          # Build with SSR support
```

## Architecture

### Backend Architecture

#### Repository Pattern
The application uses a repository pattern for data access:
- `BaseRepository` and `BaseRepositoryInterface` provide common CRUD operations
- Domain-specific repositories extend `BaseRepository` (e.g., `IncomeCategoryRepository`, `ExpenseCategoryRepository`)
- Repositories are injected into controllers via constructor dependency injection
- Repositories are organized by domain in `app/Repositories/{Domain}/`

#### Directory Structure
```
app/
├── Http/
│   ├── Controllers/{Domain}/     # Controllers organized by domain
│   ├── Requests/{Domain}/        # Form requests for validation
│   └── Middleware/
├── Models/{Domain}/              # Eloquent models organized by domain
├── Repositories/{Domain}/        # Repository pattern implementation
└── Providers/
```

#### Key Patterns
- **Domain Organization**: Code is organized by business domain (Budget, User, Course, Assessment, etc.)
- **Form Requests**: Validation logic is encapsulated in dedicated request classes
- **User Scoping**: All budget-related operations are scoped to the authenticated user
- **Soft Ownership Checks**: Controllers verify user ownership before operations (e.g., `$category->user_id !== $userId`)

### Frontend Architecture

#### Inertia.js Integration
- Pages are resolved from `resources/js/pages/{name}.tsx`
- Automatic page component resolution using Vite glob imports
- Server-side props are passed directly to React components
- Uses Laravel Wayfinder for type-safe routing

#### React Structure
```
resources/js/
├── pages/{domain}/               # Inertia page components
├── layouts/                      # Layout components (app, auth, settings)
├── components/
│   ├── ui/                       # shadcn/ui base components
│   ├── ui-element-custom/        # Custom UI components
│   └── features/{domain}/        # Feature-specific components
├── types/                        # TypeScript type definitions
├── hooks/                        # React hooks
├── lib/                          # Utilities (e.g., cn from tailwind-merge)
├── routes/                       # Generated route helpers from Wayfinder
└── actions/                      # Generated API action types
```

#### Mobile-First Design Philosophy (from .cursor/rules)

**ALWAYS design for mobile first, then adapt for desktop:**
- Use Tailwind breakpoints: `sm:`, `md:`, `lg:`, `xl:`
- Prioritize touch interactions (larger buttons, generous spacing)
- Use mobile-adapted navigation: mobile header + Sheet/Drawer
- Default components should work perfectly on mobile without modifiers
- Add desktop enhancements using responsive breakpoints

**Component Pattern:**
```tsx
// Mobile-first approach
<Button className="w-full sm:w-auto">
  Submit
</Button>
```

#### TypeScript Patterns
- Import path alias: `@/*` maps to `resources/js/*`
- Strict type checking enabled
- Domain-specific types in `resources/js/types/{domain}.ts`
- Props are typed with Laravel-generated types where applicable

#### UI Components
- Base components from Radix UI with shadcn/ui styling patterns
- Components use `class-variance-authority` for variant management
- Utility function `cn()` for merging Tailwind classes
- Components follow the "composition over configuration" pattern

### State Management
- No global state library (Redux/Zustand) used
- State is managed through Inertia.js props and React component state
- Form state typically managed with controlled components
- Theme management via `useAppearance` hook

## Common Workflows

### Adding a New Budget Feature

1. **Create Migration**: `php artisan make:migration create_{table}_table`
2. **Create Model**: `app/Models/Budget/{ModelName}.php`
   - Add user relationship
   - Add scopes (e.g., `scopeActive`, `scopeOrdered`)
3. **Create Repository**: `app/Repositories/Budget/{ModelName}Repository.php`
   - Extend `BaseRepository`
   - Add domain-specific methods (e.g., `getAllForUser`, `toggleActive`)
4. **Create Form Requests**:
   - `app/Http/Requests/Budget/Store{ModelName}Request.php`
   - `app/Http/Requests/Budget/Update{ModelName}Request.php`
5. **Create Controller**: `app/Http/Controllers/Budget/{ModelName}Controller.php`
   - Inject repository via constructor
   - Verify user ownership in all methods
   - Support both JSON and Inertia responses (`$request->wantsJson()`)
6. **Add Routes**: In `routes/web.php` under the `budget` group
7. **Create TypeScript Types**: `resources/js/types/budget.ts`
8. **Create React Components**:
   - Page: `resources/js/pages/budget/{feature}/index.tsx`
   - Feature components: `resources/js/components/features/budget/{component}.tsx`
9. **Mobile-First UI**: Design for mobile first, then add desktop breakpoints

### Adding a New UI Component

1. If using shadcn/ui pattern, create in `resources/js/components/ui/`
2. Use `class-variance-authority` for variants
3. Import `cn` from `@/lib/utils` for class merging
4. Follow Radix UI + Tailwind pattern
5. Design mobile-first with responsive modifiers

## Important Conventions

### Backend
- User ownership is ALWAYS verified before any operation
- Controllers return JSON when `$request->wantsJson()` is true
- Repository methods are preferred over direct Eloquent queries in controllers
- Success messages in French (e.g., "Catégorie de revenu créée avec succès")
- Use route names, not hardcoded paths
- Resource routes follow Laravel conventions

### Frontend
- All path imports use `@/` alias
- Component files use PascalCase with `.tsx` extension
- Mobile-first: base styles for mobile, then `sm:`, `md:`, `lg:`, `xl:` for larger screens
- Prefer composition with Radix UI primitives over building from scratch
- TypeScript strict mode is enforced

### Testing
- Pest is the testing framework (not PHPUnit syntax)
- Tests are organized in `tests/Feature` and `tests/Unit`
- Test database uses SQLite in-memory

## Routing

### Backend Routes
- Authentication routes provided by Laravel Fortify
- Budget routes: `/budget/*` (all require auth + verification)
- Settings routes: `/settings/*` (separate file `routes/settings.php`)
- Resource routes follow RESTful conventions
- Additional routes for domain actions (e.g., `toggle-active`, `update-order`)

### Frontend Routing
- Laravel Wayfinder generates type-safe route helpers
- Access routes via generated helpers in `resources/js/routes/`
- Inertia links for navigation: `<Link href={route('budget.income-categories.index')}>` pattern

## Database

- Default: SQLite (see `.env.example`)
- Queue driver: database
- Session driver: database
- Cache: database

## Additional Notes

- React Compiler is enabled (babel-plugin-react-compiler)
- SSR support available via `composer run dev:ssr`
- Concurrently runs multiple dev processes with color-coded output
- Laravel Pail provides real-time log streaming
- Wayfinder provides form variants for type-safe form handling
