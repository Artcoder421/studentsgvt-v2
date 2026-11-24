# SONIT — National Institute of Transport Student Organization Portal (Laravel)

A modern, security-hardened, role-based **Laravel** student governance and digital campus platform built for the **Students Organization of the National Institute of Transport (SONIT — NIT, Mabibo, Dar es Salaam)**.

## Core Modules & Capabilities

- **Role-Based Access Control (RBAC)** — `Super Admin`, `SONIT Admin`, `Class Representative (CR)`, and `Student` with Bcrypt password hashing, rate-limited authentication (supporting both NIT Registration Numbers like `NIT/BLTM/2022/4805` and email), CSRF protection, and middleware-enforced role separation.
- **NIT Course Communities & Channels** — Default official communities pre-provisioned for **23 real NIT programmes** (Bachelor's, Ordinary Diplomas, and Master's). Every default course community maintains a reserved CR seat (Admin-governed even before a CR registers), plus course-scoped sub-communities (a BLTM CR can only create groups for BLTM, enforced server-side) and General Campus Channels.
- **Verified E-Elections System** — Boolean election lifecycle controls (`is_enabled`, `applications_open`, `voting_open`, `results_published`), candidate nomination vetting, database-enforced single ballot per position, course-scoped Parliament Representative voting, and live results tallying.
- **Top-of-Homepage Countdown Timer Announcements** — Admin-managed countdown banners (e.g., Charity Drives, Election deadlines) with live JavaScript clocks and fundraising progress indicators.
- **Live Notification Badges & Urgent Broadcasts** — Per-user announcement read tracking powering live unread badge counters and urgent alert ribbons.
- **Editable Institutional Identity** — Live Admin editing of Organization Motto, Vision, Mission, History, and official contacts.
- **Complete Student Services** — Campus Venues & Booking Desk, Verified Accommodation & Hostels, Trackable Student Help Desk (with confidential welfare option), Lost & Found Registry, Campus Events Calendar with RSVPs, Sports & Athletics Teams, Charity & Solidarity Campaigns, and Executive Leadership Directory.

## Local Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
php artisan serve
```

## Deploying to cPanel

See [CPANEL_DEPLOY.md](CPANEL_DEPLOY.md) for step-by-step MySQL + cPanel deployment instructions.
