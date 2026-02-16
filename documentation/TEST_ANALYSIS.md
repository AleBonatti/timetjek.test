# Timetjek Test - Analysis & Implementation Considerations

## Project Overview
This is a full-stack development test for Timetjek to build a time tracking application with Swedish personnummer authentication, clock in/out functionality, and GPS coordinate tracking.

## Technical Stack Analysis

### Backend
- **PHP 8.3 + Laravel 11** ✓ (Already scaffolded)
- **Database**: MariaDB/MySQL for relational data storage
- **Authentication**: Laravel Sanctum (already installed per composer.json)

### Frontend
- **Vue 3 + TypeScript** ✓ (Already set up with Vite)
- **Mobile-first responsive design** (Tailwind CSS v4 already configured)
- **REST API communication** (Axios already configured)

## Core Features to Implement

### 1. Authentication System
**Requirements:**
- Login with Swedish Personnummer (10 or 12 digits format: YYYYMMDD-XXXX)
- Password-based authentication
- Session/token management

**Considerations:**
- Personnummer validation (Luhn algorithm for Swedish personal numbers)
- Secure password hashing (Laravel's bcrypt/argon2)
- API token authentication using Sanctum
- Manual user seeding (no registration form needed)

**Database:**
- `users` table already exists, needs `personnummer` field (already added per git status)
- Consider indexing personnummer for faster lookups

### 2. User Profile Management
**Requirements:**
- Edit user information
- Change password functionality

**Implementation:**
- Profile update endpoint
- Password change with current password verification
- Form validation on both client and server

### 3. Time Registration (Clock In/Out)
**Requirements:**
- Clock in/out based on current time
- GPS coordinate capture
- Simple overlap validation

**Considerations:**
- Database table: `time_registrations`
  - `user_id` (foreign key)
  - `clock_in` (datetime)
  - `clock_out` (nullable datetime)
  - `latitude` (decimal, nullable)
  - `longitude` (decimal, nullable)
  - `created_at`, `updated_at`

- Business Logic:
  - User can only have one active (unclosed) registration at a time
  - Clock out must be after clock in
  - No overlapping time periods for the same user

- GPS Coordinates:
  - Capture using browser Geolocation API
  - No map display required (simplifies implementation)
  - Optional: store accuracy/timestamp of GPS reading

### 4. Time Registration Overview
**Requirements:**
- Display all saved clockings
- Edit existing clockings
- Time overlap validation

**Considerations:**
- Paginated list or grouped by date
- Display format: Date, Clock In, Clock Out, Duration
- Edit modal/form for modifications
- Delete functionality (optional but useful)
- Filter/search by date range (nice-to-have)

### 5. Mobile-Friendly Design
**Requirements:**
- Responsive layout
- Touch-friendly UI elements

**Implementation:**
- Tailwind CSS utility classes for responsive breakpoints
- Mobile-first approach
- Touch targets minimum 44×44px
- Consider PWA capabilities (optional)

## Database Schema

### Users Table (existing, modified)
```sql
- id
- name
- email
- personnummer (unique, indexed) ✓ Already added
- password
- created_at
- updated_at
```

### Time Registrations Table (to create)
```sql
- id
- user_id (foreign key -> users.id, on delete cascade)
- clock_in (datetime, not null)
- clock_out (datetime, nullable)
- latitude (decimal(10,8), nullable)
- longitude (decimal(11,8), nullable)
- notes (text, nullable) - optional for comments
- created_at
- updated_at
```

### Indexes
- `user_id` + `clock_in` (composite for queries)
- `user_id` + `clock_out` (for finding active registrations)

## API Endpoints Design

### Authentication
- `POST /api/login` - Login with personnummer + password
- `POST /api/logout` - Logout and invalidate token
- `GET /api/user` - Get authenticated user info

### User Management
- `PUT /api/user/profile` - Update user profile
- `PUT /api/user/password` - Change password

### Time Registrations
- `GET /api/time-registrations` - List all registrations (paginated)
- `POST /api/time-registrations/clock-in` - Clock in with GPS
- `PUT /api/time-registrations/{id}/clock-out` - Clock out
- `GET /api/time-registrations/{id}` - Get single registration
- `PUT /api/time-registrations/{id}` - Edit registration
- `DELETE /api/time-registrations/{id}` - Delete registration (optional)

### Validation Endpoint (optional)
- `POST /api/time-registrations/validate` - Check for overlaps before save

## Frontend Structure

### Pages/Views
1. **Login** (`/login`)
   - Personnummer input (formatted: YYYYMMDD-XXXX)
   - Password input
   - Remember me (optional)

2. **Dashboard** (`/`)
   - Quick clock in/out button
   - Today's summary
   - Recent registrations

3. **Time Registrations** (`/registrations`)
   - Full list with filters
   - Edit/delete actions

4. **Profile** (`/profile`)
   - Edit user information
   - Change password form

### Components
- `LoginForm.vue`
- `ClockInOutButton.vue`
- `TimeRegistrationList.vue`
- `TimeRegistrationForm.vue` (for editing)
- `UserProfileForm.vue`
- `PasswordChangeForm.vue`
- `NavigationBar.vue`
- `GPSIndicator.vue` (shows GPS status)

### State Management
- Pinia for global state (user, active registration)
- Or composables with Vue 3 Composition API

## Validation Rules

### Personnummer Validation
- Format: YYYYMMDD-XXXX or YYYYMMDDXXXX (10-12 digits)
- Luhn algorithm check for Swedish personal numbers
- Unique in database

### Time Registration Validation
1. **Clock In**
   - User must not have an active (unclosed) registration

2. **Clock Out**
   - Must belong to the same user
   - Must be after clock_in
   - Result must not overlap with other registrations

3. **Edit Registration**
   - clock_out must be after clock_in (if both present)
   - No overlap with other user's registrations
   - Cannot edit to create unclosed registration if one exists

### Overlap Detection Algorithm
```
For a registration (A) with times [start_A, end_A]:
Overlaps if any existing registration (B) satisfies:
  start_B < end_A AND end_B > start_A
```

## Security Considerations

1. **Authentication**
   - CSRF protection (Laravel default)
   - Rate limiting on login endpoint
   - Secure password hashing

2. **Authorization**
   - Users can only access their own registrations
   - API middleware to verify token
   - Check ownership before edit/delete

3. **Input Validation**
   - Server-side validation for all inputs
   - Sanitize personnummer format
   - Validate GPS coordinates range

4. **GPS Privacy**
   - Optional GPS capture with user permission
   - Store only coordinates, not full location data

## Testing Strategy

### Backend Tests (PHPUnit)
- Personnummer validation logic
- Overlap detection logic
- Authentication flow
- CRUD operations for time registrations
- Authorization checks

### Frontend Tests (Optional - Vitest)
- Component rendering
- Form validation
- API integration

### Manual Testing Checklist
- [ ] Login with valid/invalid personnummer
- [ ] Clock in captures GPS
- [ ] Cannot clock in twice
- [ ] Clock out updates existing registration
- [ ] Edit registration validates overlaps
- [ ] Mobile responsive on different devices
- [ ] Password change works
- [ ] Profile update persists

## Implementation Phases

### Phase 1: Database & Models (Priority: High)
1. Create time_registrations migration
2. Create TimeRegistration model with relationships
3. Seed test users with personnummer

### Phase 2: Backend API (Priority: High)
1. Authentication endpoints (login/logout)
2. Time registration CRUD endpoints
3. Validation logic for overlaps
4. User profile endpoints

### Phase 3: Frontend Core (Priority: High)
1. Login page with personnummer input
2. Clock in/out interface
3. GPS coordinate capture
4. Time registration list view

### Phase 4: Frontend Enhancement (Priority: Medium)
1. Edit registration functionality
2. Profile management page
3. Better date/time formatting
4. Mobile optimization

### Phase 5: Polish (Priority: Low)
1. Loading states
2. Error handling
3. Toast notifications
4. Empty states

## Development Time Estimate

**Note**: As per guidelines, avoid giving time estimates. However, for personal planning:
- Phase 1: Database setup
- Phase 2: API implementation
- Phase 3: Core frontend
- Phase 4: Enhancements
- Phase 5: Testing & polish

Total: Plan for iterative development with working features at each phase.

## AI Tool Usage Plan

Document usage of AI tools for:
- Boilerplate code generation
- Complex algorithm implementation (e.g., Luhn algorithm)
- TypeScript type definitions
- Debugging assistance
- Code review and optimization suggestions

## Nice-to-Have Features (Out of Scope)

- Email notifications
- Export to PDF/Excel
- Multi-language support
- Admin panel
- Docker containerization
- CI/CD pipeline
- Advanced reporting/analytics
- Team/project assignment
- Break time tracking

## Questions to Consider

1. Should GPS be mandatory or optional for clock in/out?
2. Can users delete their own registrations or just edit?
3. Time format: 24-hour or 12-hour with AM/PM?
4. Date format: Swedish (YYYY-MM-DD) or other?
5. Timezone handling: Server timezone or user timezone?
6. Should there be a maximum edit window (e.g., can't edit registrations older than X days)?

## Current Project Status

Based on git status, the project has:
- ✅ Laravel 11 scaffolding
- ✅ Vue 3 + TypeScript setup
- ✅ Tailwind CSS v4 configuration
- ✅ User model with personnummer field
- ✅ User seeder created
- ✅ Sanctum for API authentication

**Next Steps:**
1. Create TimeRegistration migration and model
2. Implement API endpoints
3. Build frontend components
4. Test and refine

## Conclusion

This project is well-scoped for demonstrating full-stack skills. The existing scaffolding provides a solid foundation. Focus should be on:
- Clean, maintainable code
- Proper validation logic
- Mobile-friendly UI
- RESTful API design
- Secure authentication

The key differentiator will be attention to detail in personnummer validation, overlap detection, and user experience on mobile devices.
