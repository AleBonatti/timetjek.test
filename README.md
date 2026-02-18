# Timetjek - Test Application

Responsive time tracking application built with Laravel 11 and Vue 3. This application allows employees to clock in and out, track their working hours, and manage their time entries with an intuitive interface that works seamlessly on both desktop and mobile devices.

## Features

### Time Tracking

- **Quick Clock In/Out**: Simple one-click buttons to start and stop tracking time
- **Real-time Duration**: Live display of current session duration
- **Geolocation Support**: Optional location tracking for clock in/out events
- **Today's Overview**: Dashboard showing all time entries for the current day

### Time Entry Management

- **View Time Entries**: Browse entries by week, month, or custom date range
- **Edit Entries**: Modify clock in/out times and add notes to entries
- **Delete Entries**: Remove incorrect or duplicate entries
- **Grouped Display**: Entries automatically grouped by day with daily totals
- **Status Indicators**: Clear visual indicators for open/closed entries

### User Features

- **Authentication**: Secure login using personnummer (Swedish personal identity number)
- **Profile Management**: Update name and email address
- **Password Management**: Change password with current password verification
- **Dark Mode**: Toggle between light and dark themes

### Responsive Design

- **Mobile-First**: Optimized for mobile devices with touch-friendly interfaces
- **Adaptive Layout**: Automatically adjusts to screen size
- **Slide-out Navigation**: Smooth left-to-right sidebar animation on mobile
- **Full-width Buttons**: Mobile-optimized button layouts

## Tech Stack

### Backend

- **Laravel 11**: Modern PHP framework
- **MariaDb / MySql**: Robust relational database
- **Sanctum**: SPA authentication

### Frontend

- **Vue 3**: Progressive JavaScript framework with Composition API
- **TypeScript**: Type-safe JavaScript
- **Vite**: Fast build tool and dev server
- **Tailwind CSS v4**: Utility-first CSS framework with custom theming
- **Pinia**: State management
- **Vue Router**: Client-side routing

## Prerequisites

- PHP 8.2 or higher
- Composer
- Node.js 18+ and npm
- PostgreSQL 14+

## Installation

### 1. Clone the Repository

```bash
git clone <repository-url>
cd timetjek.test
```

### 2. Install PHP Dependencies

```bash
composer install
```

### 3. Install JavaScript Dependencies

```bash
npm install
```

### 4. Environment Configuration

Copy the example environment file and configure it:

```bash
cp .env.example .env
```

Edit `.env` and configure your database connection:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=timetjek
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 5. Generate Application Key

```bash
php artisan key:generate
```

### 6. Run Database Migrations

```bash
php artisan migrate
```

### 7. Seed the Database

The seeder will create test users with the following credentials:

```bash
php artisan db:seed
```

**Test Users:**

- Personnummer: `19900101-1239`, Password: `password`
- Personnummer: `19850523-5678`, Password: `password`

### 8. Build Frontend Assets

For development:

```bash
npm run dev
```

For production:

```bash
npm run build
```

### 9. Start the Development Server

This project has been developed using [Laravel Herd](https://herd.laravel.com/).

**Using Laravel Herd (Recommended):**

The site is mapped as `https://timetjek.test` in Herd. Once you have Herd installed and the site linked, the application will be automatically available at:

```
https://timetjek.test
```

Using https allows for Expose external test on mobile platforms.

**Alternative - Using Artisan Serve:**

If you're not using Herd, you can use Laravel's built-in development server:

```bash
php artisan serve
```

The application will be available at `http://localhost:8000`. Update .env accordingly.

## Usage

### Logging In

1. Navigate to `http://localhost:8000`
2. Enter a personnummer (e.g., `19900101-1239`)
3. Enter the password (`password` for test users)
4. Click "Sign in"

### Clocking In/Out

1. From the Dashboard, click the "Clock In" button to start tracking time
2. Click "Clock Out" to stop tracking
3. View your entries in the "Today's Time Entries" section

### Managing Time Entries

1. Navigate to "Time Entries" from the sidebar
2. Use the tabs to filter by Week, Month, or Custom Range
3. Click on any entry to edit it
4. Modify times, add notes, or delete the entry

### Updating Profile

1. Navigate to "Settings" from the sidebar
2. Update your name and email in the Profile section
3. Change your password in the Password section

## Testing

The test suite uses PHPUnit and covers both unit and feature (HTTP) levels. Tests run against a **dedicated test database** to keep your development data intact.

### Test Database Setup

Create a separate database for tests (the suite uses `timetjek_test` on MariaDB port `3307` by default — adjust `phpunit.xml` to match your setup):

```bash
mysql -u root -p -e "CREATE DATABASE timetjek_test;"
```

The test environment is configured in `phpunit.xml`:

```xml
<env name="DB_CONNECTION" value="mysql"/>
<env name="DB_HOST" value="127.0.0.1"/>
<env name="DB_PORT" value="3307"/>
<env name="DB_DATABASE" value="timetjek_test"/>
```

> The test suite uses `RefreshDatabase`, which runs migrations and rolls back after each test. **Only the test database is affected** — your development database is never touched.

### Running Tests

Run the full suite:

```bash
php artisan test
```

Run a specific test file:

```bash
php artisan test tests/Feature/AuthTest.php
php artisan test tests/Feature/TimeEntryTest.php
php artisan test tests/Unit/TimeEntryModelTest.php
```

Stop on first failure:

```bash
php artisan test --stop-on-failure
```

### Test Coverage

| Suite | File | Tests | What's covered |
|-------|------|-------|----------------|
| Unit | `TimeEntryModelTest` | 12 | `isActive()`, `getDurationInMinutes()`, `getFormattedDuration()`, `overlaps()` |
| Feature | `AuthTest` | 20 | Login (valid, wrong password, unknown user), Luhn format validation, logout, rate limiting (429), profile update, password update |
| Feature | `TimeEntryTest` | 31 | Clock in/out, GPS coordinates, working hours boundaries (06:00–23:00), overlap detection (4 scenarios), open-entry rules, CRUD authorization, data isolation |

### Personnummer Validation

The login endpoint validates personnummer using the **Luhn algorithm** (the same check used client-side). Both the full 12-digit format (`19900101-1239`) and the short 10-digit format (`900101-1239`) are accepted by the validator.

## Development

### Code Linting

```bash
npm run lint
```

### Code Formatting

```bash
npm run format
```

### Development Workflow (no Herd)

1. Create site on Herd
1. If you're not using Herd: start the Laravel dev server: `php artisan serve`
1. Start Vite dev server: `npm run dev`
1. Make changes to your code
1. Vite will automatically hot-reload the frontend
1. Refresh the browser for backend changes

## Project Structure

```
├── app/
│   ├── Actions/
│   │   └── TimeEntry/
│   │       ├── ClockInAction.php           # Clock-in business logic
│   │       ├── ClockOutAction.php          # Clock-out business logic
│   │       ├── UpdateTimeEntryAction.php   # Update + validation logic
│   │       └── DeleteTimeEntryAction.php   # Delete logic
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── Api/
│   │   │       ├── AuthController.php      # Authentication endpoints
│   │   │       └── TimeEntryController.php # Time entry endpoints
│   │   └── Requests/
│   │       ├── Auth/                       # Login, password, profile form requests
│   │       └── TimeEntry/                  # Clock in/out, update, date range form requests
│   ├── Models/
│   │   ├── User.php
│   │   └── TimeEntry.php
│   └── Policies/
│       └── TimeEntryPolicy.php             # Ownership authorization
├── database/
│   ├── factories/
│   │   ├── UserFactory.php
│   │   └── TimeEntryFactory.php
│   ├── migrations/
│   └── seeders/
│       └── UserSeeder.php
├── resources/
│   ├── js/
│   │   ├── components/                     # Reusable Vue components
│   │   ├── layouts/                        # Layout components
│   │   ├── views/                          # Page components
│   │   ├── stores/                         # Pinia stores
│   │   └── router/                         # Vue Router configuration
│   └── css/
│       └── app.css                         # Tailwind CSS + theme configuration
├── routes/
│   ├── api.php                             # API routes (prefixed /api/v1)
│   └── web.php
└── tests/
    ├── Feature/
    │   ├── AuthTest.php                    # Login, logout, profile, password, rate limiting
    │   └── TimeEntryTest.php               # Clock in/out, CRUD, overlap, authorization
    └── Unit/
        └── TimeEntryModelTest.php          # Model methods: overlaps(), duration, etc.
```

## API Endpoints

All endpoints are prefixed with `/api/v1/`. Protected routes require an active session cookie (Sanctum SPA authentication).

### Authentication

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| `POST` | `/api/v1/login` | Public | Login with personnummer and password |
| `POST` | `/api/v1/logout` | Required | Logout current user |
| `GET` | `/api/v1/user` | Required | Get authenticated user |
| `PUT` | `/api/v1/user/password` | Required | Update password |
| `PUT` | `/api/v1/user/profile` | Required | Update profile |

### Time Entries

| Method | Endpoint | Description |
|--------|----------|-------------|
| `GET` | `/api/v1/time-entries/today` | Get today's entries |
| `GET` | `/api/v1/time-entries/current-week` | Get current week's entries |
| `GET` | `/api/v1/time-entries/current-month` | Get current month's entries |
| `GET` | `/api/v1/time-entries/date-range?from=YYYY-MM-DD&to=YYYY-MM-DD` | Get entries for a custom date range |
| `POST` | `/api/v1/time-entries/clock-in` | Clock in (optional GPS coordinates) |
| `POST` | `/api/v1/time-entries/clock-out` | Clock out (optional GPS coordinates) |
| `PUT` | `/api/v1/time-entries/{id}` | Update a time entry (owner only) |
| `DELETE` | `/api/v1/time-entries/{id}` | Delete a time entry (owner only) |

> The login endpoint is rate-limited to **5 requests per minute** per IP.

## Configuration

### Theme Colors

The primary brand color is configured in `resources/css/app.css`:

```css
@theme {
    --color-primary-*: #1d7cc0;
}
```

### Session Configuration

Session-based authentication is used for SPA. Configure session settings in `config/session.php`.

**Migration from Database to Cookie Sessions:**

The project was migrated from database sessions (`SESSION_DRIVER=database`) to cookie sessions (`SESSION_DRIVER=cookie`) for improved performance and simplicity:

- **Better Performance**: Cookie-based sessions eliminate database queries for session retrieval on every request, reducing database load and improving response times
- **Simpler Infrastructure**: No need to maintain a sessions table or run session garbage collection
- **Stateless Architecture**: Cookie sessions align better with modern SPA architecture and make horizontal scaling easier
- **Laravel Sanctum Compatibility**: Cookie sessions work seamlessly with Sanctum's SPA authentication flow

For this application's use case (single-page application with standard authentication), cookie sessions provide better performance without sacrificing security.

### CORS Configuration

CORS is configured in `bootstrap/app.php` to allow credentials and proper headers for SPA authentication.

## Deployment

### Production Build

1. Set `APP_ENV=production` in `.env`
2. Run `npm run build` to compile frontend assets
3. Configure your web server (Apache/Nginx) to serve `public/index.php`
4. Ensure `storage` and `bootstrap/cache` directories are writable
5. Run `php artisan config:cache` and `php artisan route:cache`

### Environment Variables

Important production environment variables:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com
SESSION_SECURE_COOKIE=true
```

## Troubleshooting

### Geolocation Warnings

If you see geolocation warnings on localhost:

- The app will still work without location data
- For HTTPS environments, geolocation works more reliably
- Location permissions are optional and won't prevent time tracking

### Session Issues

If you experience authentication issues:

1. Clear browser cookies
2. Run `php artisan config:clear`
3. Ensure `SESSION_DOMAIN` is correctly set in `.env`

### Asset Build Issues

If frontend assets aren't loading:

1. Run `npm run build` again
2. Clear browser cache
3. Check `public/build` directory exists

## Support

For issues or questions, please contact alessandro.bonatti@elva11.se.
