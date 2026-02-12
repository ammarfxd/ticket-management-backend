# Ticket Management API (Laravel 12)

Backend REST API untuk sistem **ticket management/helpdesk**: customer buat ticket, agent handle ticket assigned, admin manage categories & assign ticket.

Project ini dibuat untuk portfolio (junior-friendly tapi “real-world”): **Sanctum token auth**, **role-based authorization (Policies)**, **filters + pagination**, **seed demo data**, dan **feature tests**.

---

## Features

- Auth (Register / Login / Logout) — **Laravel Sanctum**
- Roles: `admin`, `agent`, `customer`
- Tickets:
    - Create ticket (customer)
    - List tickets (role-based scope)
    - View ticket (policy)
    - Update status/priority (agent assigned / admin)
    - Assign ticket to agent (admin)
- Ticket comments:
    - Reply thread (customer/agent/admin ikut policy)
    - Internal note (agent/admin sahaja)
- Categories:
    - View categories (any authenticated)
    - Admin-only CRUD (Policy)
- API Resources (consistent JSON)
- Seed demo data + Feature tests

---

## Tech Stack

- PHP 8.2+ (CI uses 8.3)
- Laravel 12
- MySQL
- Laravel Sanctum
- PHPUnit

---

## Quick Start (Local)

### 1) Install

```bash
composer install
cp .env.example .env
php artisan key:generate
```
