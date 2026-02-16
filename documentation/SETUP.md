# Timetjek Test - Setup Guide

## Project Overview

This is a full-stack time tracking application built for Timetjek's recruitment assessment. The project uses Laravel 11 as the backend with Vue 3 + TypeScript for the frontend in a monorepo setup.

## Tech Stack

### Backend
- **Laravel 11** - PHP framework
- **Laravel Sanctum** - SPA authentication
- **MariaDB** - Relational database (port 3307)
- **PHP 8.3+** - Required PHP version

### Frontend
- **Vue 3** - Progressive JavaScript framework (Composition API)
- **TypeScript** - Type-safe JavaScript
- **Vite** - Build tool and dev server
- **Tailwind CSS 4** - Utility-first CSS framework
- **Pinia** - State management
- **Vue Router** - Client-side routing
- **Axios** - HTTP client
- **Heroicons** - Icon library

### Development Tools
- **ESLint** - Code linting
- **Prettier** - Code formatting (4-space tabs)
- **Laravel Pint** - PHP code style fixer

## Project Structure

```
timetjek.test/
├── app/
│   ├── Http/Controllers/
│   ├── Models/
│   │   └── User.php (with HasApiTokens, personnummer field)
│   └── Providers/
├── database/
│   ├── factories/
│   │   └── UserFactory.php (generates personnummer)
│   ├── migrations/
│   │   ├── *_create_users_table.php
│   │   ├── *_create_personal_access_tokens_table.php
│   │   └── *_add_personnummer_to_users_table.php
│   └── seeders/
│       └── DatabaseSeeder.php (3 test users)
├── resources/
│   ├── css/
│   │   └── app.css (Tailwind 4 configuration)
│   ├── js/
│   │   ├── components/
│   │   ├── composables/
│   │   ├── layouts/
│   │   ├── router/
│   │   │   └── index.ts
│   │   ├── stores/
│   │   │   └── auth.ts
│   │   ├── types/
│   │   │   ├── index.ts
│   │   │   └── shims-vue.d.ts
│   │   ├── utils/
│   │   │   └── axios.ts (configured for Sanctum)
│   │   ├── views/
│   │   │   └── Home.vue
│   │   ├── App.vue
│   │   └── app.ts
│   └── views/
│       └── app.blade.php (SPA entry point)
├── routes/
│   ├── api.php (Sanctum protected routes)
│   └── web.php (catch-all for Vue Router)
├── documentation/
│   ├── SETUP.md (this file)
│   └── TEST_ANALYSIS.md
├── .env (configured for MariaDB)
├── .prettierrc.json (4-space tabs, LF line endings)
├── eslint.config.js (Vue 3 + TypeScript)
├── tsconfig.json
├── tsconfig.node.json
└── vite.config.ts (Vue 3 + Tailwind 4)
```

## Database Configuration

The project is configured to use MariaDB with the following settings:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3307
DB_DATABASE=timetjek
DB_USERNAME=root
DB_PASSWORD=
```

### Test Users

Three test users are seeded automatically:

1. **Alessandro Bonatti**
   - Personnummer: `19760117-6998`
   - Email: `alessandro.bonatti@elva11.se`
   - Password: `password`

2. **Random User #1**
   - Generated personnummer
   - Random email
   - Password: `password`

3. **Random User #2**
   - Generated personnummer
   - Random email
   - Password: `password`

## Setup Instructions

### 1. Prerequisites

- PHP 8.3+
- Composer
- Node.js 18+
- npm or yarn
- MariaDB running on port 3307
- Laravel Herd (recommended)

### 2. Environment Setup

The `.env` file is already configured. Key settings:

```env
APP_NAME="Timetjek Test"
APP_URL=https://timetjek.test
SESSION_DRIVER=cookie
SESSION_DOMAIN=.timetjek.test
SANCTUM_STATEFUL_DOMAINS=timetjek.test,localhost,localhost:3000,127.0.0.1,127.0.0.1:8000,::1
```

### 3. Install Dependencies

```bash
# Install PHP dependencies (already done)
composer install

# Install Node dependencies (already done)
npm install
```

### 4. Database Setup

```bash
# Run migrations
php artisan migrate

# Seed the database with test users
php artisan db:seed
```

### 5. Development Server

#### Option A: Using Laravel Herd

1. Add the project to Herd
2. The site will be available at `https://timetjek.test`
3. Run Vite dev server:

```bash
npm run dev
```

#### Option B: Using Artisan Serve

```bash
# Terminal 1: Start Laravel development server
php artisan serve

# Terminal 2: Start Vite development server
npm run dev
```

Visit: `http://localhost:8000`

### 6. Build for Production

```bash
# Build frontend assets
npm run build
```

## Key Features & Configurations

### Tailwind CSS 4

- Uses the new `@import 'tailwindcss'` syntax in `app.css`
- Vite plugin: `@tailwindcss/vite` (not the old PostCSS plugin)
- Custom theme variables defined in `@theme` block
- Dark mode support via `dark:` variant (uses `prefers-color-scheme`)

### Laravel Sanctum SPA Authentication

- Configured in `bootstrap/app.php` with `statefulApi()` middleware
- CSRF protection enabled
- Cookie-based authentication for SPA
- API routes protected with `auth:sanctum` middleware

### TypeScript Configuration

- Strict mode enabled
- Path aliases: `@/*` maps to `resources/js/*`
- Vue 3 type definitions included
- No `any` types warning enabled in ESLint

### Code Quality

**ESLint:**
- Vue 3 recommended rules
- TypeScript support
- Warns on `any` types

**Prettier:**
- Tab width: 4 spaces
- Line endings: LF (Unix-style)
- Single quotes
- Trailing commas (ES5)

## API Endpoints

### Authentication

```
POST   /api/login           # Login (to be implemented)
POST   /api/logout          # Logout (to be implemented)
GET    /api/user            # Get authenticated user (protected)
```

### User Management (to be implemented)

```
PUT    /api/user/profile    # Update user profile
PUT    /api/user/password   # Change password
```

### Time Registrations (to be implemented)

```
GET    /api/time-registrations              # List registrations
POST   /api/time-registrations/clock-in     # Clock in
PUT    /api/time-registrations/:id/clock-out # Clock out
GET    /api/time-registrations/:id          # Get registration
PUT    /api/time-registrations/:id          # Update registration
DELETE /api/time-registrations/:id          # Delete registration
```

## Frontend State Management

### Pinia Stores

- **auth.ts**: User authentication state
  - `user`: Current user object
  - `isAuthenticated`: Boolean flag
  - `setUser()`: Set user data
  - `clearUser()`: Clear user data

### Vue Router

Routes are defined in `resources/js/router/index.ts`. Currently includes:
- `/`: Home page

## Development Workflow

### Code Style

```bash
# Format PHP code
./vendor/bin/pint

# Format JS/TS/Vue code
npm run format

# Lint JS/TS/Vue code
npm run lint
```

### Git Workflow

The repository is initialized and ready. Commit messages should be descriptive.

## Next Steps

The scaffolding is complete. The following features need to be implemented:

1. **Authentication**
   - Login page with personnummer validation
   - Logout functionality
   - Protected routes

2. **User Profile**
   - Profile edit form
   - Password change

3. **Time Registration**
   - Clock in/out interface
   - GPS capture
   - Registration list
   - Edit/delete registrations
   - Overlap validation

4. **UI Components**
   - Layout component with navigation
   - Form components
   - Modal components
   - Loading states
   - Error handling

## Notes

- The project uses a monorepo structure (no separate frontend/backend)
- All frontend code is in `resources/js/`
- API routes are defined in `routes/api.php`
- Web routes (for Vue Router) are in `routes/web.php`
- Tailwind CSS 4 uses a different configuration approach than v3
- Dark mode is handled by system preference via `prefers-color-scheme`

## Troubleshooting

### Vite Not Hot Reloading

Make sure both the Vite dev server (`npm run dev`) and Laravel are running.

### CORS Issues

Check that `SANCTUM_STATEFUL_DOMAINS` in `.env` includes your domain.

### Database Connection Error

Verify MariaDB is running on port 3307 and the database `timetjek` exists.

### TypeScript Errors

Run `npm install` to ensure all type definitions are installed.

## Resources

- [Laravel 11 Documentation](https://laravel.com/docs/11.x)
- [Vue 3 Documentation](https://vuejs.org/)
- [Tailwind CSS 4 Alpha Documentation](https://tailwindcss.com/docs/v4-beta)
- [Pinia Documentation](https://pinia.vuejs.org/)
- [Laravel Sanctum SPA Authentication](https://laravel.com/docs/11.x/sanctum#spa-authentication)
