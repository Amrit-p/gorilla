# Mowing CRM & Scheduling

Production-oriented **Laravel + Blade** CRM for lawn / mowing service businesses: leads, clients, jobs, maps, employee mobile view, notifications, activity logs, and **Spatie Laravel Permission** RBAC.

**Stack:** Laravel (latest), Blade, **Tailwind CSS + jQuery via CDN only** (no npm build), MySQL in production, SQLite for automated tests.

---

## Quick start

1. **Install & configure**

   ```bash
   composer install
   cp .env.example .env
   php artisan key:generate
   ```

2. **Database** — set `DB_*` in `.env` for MySQL, then:

   ```bash
   php artisan migrate
   php artisan db:seed
   ```

3. **Run locally**

   ```bash
   php artisan serve
   ```

4. **Default admin (after `db:seed`)**

   - Email: `admin@mowingcrm.test`  
   - Password: `Password@123`  

   Change this immediately in production.

---

## Architecture (high level)

| Layer | Role |
|--------|------|
| `app/Http/Controllers` | Thin HTTP layer; delegates to services |
| `app/Http/Requests` | Validation + authorization entry points |
| `app/Services` | Business logic |
| `app/Repositories` | Query / pagination helpers |
| `app/Policies` | Model-level authorization (defense in depth) |
| `app/Events` / `Listeners` / `Jobs` | Lead conversion, geocoding queue, etc. |

**Routes:** `routes/web.php` (home + guest auth), `routes/crm.php` (authenticated CRM, required from `web.php`).

---

## Implemented modules

1. Laravel setup · 2. Authentication · 3. Roles & permissions · 4. Dashboard layout · 5. User management · 6. Leads · 7. Clients · 8. Jobs · 9. Map & routing · 10. Employee mobile dashboard · 11. Notifications · 12. Activity logs · 13. Settings · 14. Optimization (caching, indexes, queries) · 15. Security (policies, headers, active users, throttles) · 16. This README & route tidy-up.

---

## Tests

```bash
composer test
# or
php artisan test
```

PHPUnit uses **in-memory SQLite** (`phpunit.xml`). Feature tests seed `RoleAndPermissionSeeder` where a real admin user is required.

---

## Configuration notes

- **Mapbox:** `MAPBOX_ACCESS_TOKEN` in `.env` (see `config/services.php`).
- **Mail / SMTP:** Super Admin can configure an SMTP mailer in **Settings** (saved in the `settings` table). When **“Use database mail settings”** is enabled, those values override `.env` at runtime via `MailSettingsRegistrar`. Leave the SMTP password field blank to keep the previously saved password.
- **Permissions:** `database/seeders/RoleAndPermissionSeeder.php` defines abilities and demo roles.
- **Queues:** Geocoding uses `GeocodeLeadAddressJob` (`ShouldQueue`); run a worker in production (`php artisan queue:work`).

---

## License

Application code follows your project license; Laravel is MIT (see [laravel.com](https://laravel.com)).
