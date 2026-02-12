# Ticket Management API (Laravel 12)

Backend REST API for a **ticket management/helpdesk system**: customers create tickets, agents handle assigned tickets, and admins manage categories & assign tickets.

Built for portfolio: **Sanctum token auth**, **role-based authorization (Policies)**, **filters + pagination**, **seed demo data**, and **feature tests**.

---

## Features

- Authentication (Register / Login / Logout) — **Laravel Sanctum**
- Roles: `admin`, `agent`, `customer`
- Tickets
    - Create ticket (customer)
    - List tickets (role-based scope)
    - View ticket (policy)
    - Update status/priority (assigned agent / admin)
    - Assign ticket to agent (admin)
- Ticket comments
    - Reply thread (policy enforced)
    - Internal notes (agent/admin only)
- Categories
    - View categories (any authenticated user)
    - Admin-only CRUD (Policy)
- API Resources (consistent JSON responses)
- Seed demo data + Feature tests

---

## Tech Stack

- PHP 8.2+ (CI uses 8.3)
- Laravel 12
- MySQL
- Laravel Sanctum
- PHPUnit

---

## Project Setup (Local)

### 1) Install dependencies

```bash
composer install
cp .env.example .env
php artisan key:generate
```

### 2) Configure database

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ticket_management_db
DB_USERNAME=root
DB_PASSWORD=
```
