# Copilot Instructions for social-platform

## Project Overview
Laravel 12 social platform application using PHP 8.2+, Vite 7, and Tailwind CSS v4.

## Architecture
- **Backend**: Laravel 12 with standard MVC structure
- **Frontend**: Vite + Tailwind CSS v4 (via `@tailwindcss/vite` plugin)
- **Database**: SQLite (default), MySQL/MariaDB/PostgreSQL supported
- **Testing**: Pest (not PHPUnit)

## Developer Workflows

### Setup & Development
```bash
composer setup          # Full project setup (install deps, generate key, migrate, build assets)
composer dev            # Start dev server, queue worker, and Vite concurrently
npm run dev             # Vite dev server only
npm run build           # Production build
```

### Testing
```bash
composer test           # Clear config + run Pest tests
./vendor/bin/pest       # Run Pest directly
```
- Feature tests extend `Tests\TestCase` (configured in [tests/Pest.php](tests/Pest.php))
- Use `RefreshDatabase` trait when tests need database isolation (commented by default)

### Code Quality
```bash
./vendor/bin/pint       # Laravel Pint for code style fixes
```

## Key Conventions

### Models & Database
- Models in `app/Models/` with factories in `database/factories/`
- Use `HasFactory` trait on all models
- Password fields auto-hash via `'password' => 'hashed'` cast (see [User.php](app/Models/User.php))
- Migrations use anonymous class syntax (Laravel 9+ style)

### Routes
- Web routes in [routes/web.php](routes/web.php)
- Console commands in [routes/console.php](routes/console.php)
- Health check endpoint at `/up` (configured in [bootstrap/app.php](bootstrap/app.php))

### Frontend Assets
- Entry points: [resources/css/app.css](resources/css/app.css), [resources/js/app.js](resources/js/app.js)
- Tailwind configured via Vite plugin, not separate config file
- Use `@vite()` directive in Blade templates

### Testing Patterns
```php
// Pest syntax (preferred)
test('example', function () {
    $response = $this->get('/');
    $response->assertStatus(200);
});

// Factory usage
User::factory()->create(['email' => 'test@example.com']);
```

## Dev Tools
- **Laravel Sail**: Docker environment (`./vendor/bin/sail`)
- **Laravel Pail**: Real-time log viewer (`php artisan pail`)
- **Tinker**: REPL (`php artisan tinker`)

## File Structure Reference
```
app/Models/          # Eloquent models
app/Http/Controllers # Request handlers
database/migrations/ # Schema migrations
database/factories/  # Model factories
database/seeders/    # Database seeders
resources/views/     # Blade templates
tests/Feature/       # Feature/integration tests
tests/Unit/          # Unit tests
```
