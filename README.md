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

Edit .env:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ticket_management_db
DB_USERNAME=root
DB_PASSWORD=
```

### 3) Run migrations + seed demo data

```bash
php artisan migrate:fresh --seed
```

### 4) Start the server

```bash
php artisan serve
```

---

## Demo Accounts (Seeder)

- Password for all demo accounts: password

- Admin: admin@example.com

- Agent: agent@example.com

- Customer: customer@example.com

---

## Authentication

### All protected endpoints require:

```text
Authorization: Bearer <TOKEN>
Accept: application/json
```

### Login

#### POST /api/auth/login

Example response:

```json
{
    "message": "Logged in successfully.",
    "user": {
        "id": 1,
        "name": "Admin Demo",
        "email": "admin@example.com",
        "role": "admin"
    },
    "token": "1|xxxxxxxxxxxxxxxxxxxx",
    "token_type": "Bearer"
}
```

## API Endpoints

> Base URL: `http://127.0.0.1:8000`  
> Auth: `Authorization: Bearer <TOKEN>`

### Auth

| Method | Endpoint             | Description                       |
| ------ | -------------------- | --------------------------------- |
| POST   | `/api/auth/register` | Register (default role: customer) |
| POST   | `/api/auth/login`    | Login + return token              |
| POST   | `/api/auth/logout`   | Logout (revoke current token)     |
| GET    | `/api/auth/me`       | Get current logged-in user        |

### Tickets

| Method | Endpoint                   | Role        | Description                                                     |
| ------ | -------------------------- | ----------- | --------------------------------------------------------------- |
| GET    | `/api/tickets`             | auth        | List tickets (customer: own, agent: assigned, admin: all)       |
| POST   | `/api/tickets`             | customer    | Create new ticket                                               |
| GET    | `/api/tickets/{id}`        | policy      | View ticket detail (customer: own, agent: assigned, admin: all) |
| PATCH  | `/api/tickets/{id}`        | agent/admin | Update ticket (`status`, `priority`)                            |
| PATCH  | `/api/tickets/{id}/assign` | admin       | Assign ticket to agent (`assigned_to`)                          |

### Ticket Comments

| Method | Endpoint                     | Role   | Description                                             |
| ------ | ---------------------------- | ------ | ------------------------------------------------------- |
| GET    | `/api/tickets/{id}/comments` | policy | List ticket comments                                    |
| POST   | `/api/tickets/{id}/comments` | policy | Add comment/reply (`is_internal=true` agent/admin only) |

### Categories

| Method | Endpoint               | Role  | Description     |
| ------ | ---------------------- | ----- | --------------- |
| GET    | `/api/categories`      | auth  | List categories |
| POST   | `/api/categories`      | admin | Create category |
| PATCH  | `/api/categories/{id}` | admin | Update category |
| DELETE | `/api/categories/{id}` | admin | Delete category |
