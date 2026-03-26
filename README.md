# Silsilah Keluarga Monolith (Web + API)

Privacy-first family tree backend built with Laravel, using a hybrid data architecture:

- MySQL (Eloquent) for transactional data
- Neo4j for relationship graph traversal

This repository is a single Laravel monolith with:

- Inertia + React web interface (Breeze scaffolding)
- REST API endpoints under `/api/*`
- No Filament admin panel

## Tech Stack

- PHP 8.2+
- Laravel 12
- Laravel Sanctum (API auth)
- Laravel Breeze + Inertia.js + React (web auth UI)
- Neo4j client: `laudis/neo4j-php-client`

## Core Features

- Profiles with field-level privacy controls
- Access request workflow for protected fields
- Event management with RSVPs and financial contributions
- Smart graph-driven invitation dispatch
- Family tree descendants query through Neo4j

## Authentication

Two auth flows coexist:

- Session auth (`web` guard) for Inertia/Breeze web pages
- Sanctum auth (`sanctum` guard) for API routes

Sanctum token flow:

- `POST /api/auth/login`
- `POST /api/auth/logout` (auth required)
- `GET /api/auth/me` (auth required)

Example login payload:

```json
{
    "email": "user@example.com",
    "password": "password",
    "device_name": "mobile-app"
}
```

Use returned token for external/stateless API clients:

```http
Authorization: Bearer {access_token}
```

## API Endpoints

All endpoints below require `auth:sanctum` unless noted.

When called from the first-party web app, session-backed Sanctum requests are supported.

### Users

- `GET /api/users`
- `POST /api/users`
- `GET /api/users/{user}`
- `PUT/PATCH /api/users/{user}`
- `DELETE /api/users/{user}`

### Profiles

- `GET /api/profiles`
- `POST /api/profiles`
- `GET /api/profiles/{profile}`
- `PUT/PATCH /api/profiles/{profile}`
- `DELETE /api/profiles/{profile}`

### Access Requests

- `GET /api/access-requests`
- `POST /api/access-requests`
- `GET /api/access-requests/{accessRequest}`
- `PUT/PATCH /api/access-requests/{accessRequest}`
- `DELETE /api/access-requests/{accessRequest}`
- `PATCH /api/access-requests/{accessRequest}/respond`

### Events

- `GET /api/events`
- `POST /api/events`
- `GET /api/events/{event}`
- `PUT/PATCH /api/events/{event}`
- `DELETE /api/events/{event}`
- `POST /api/events/{event}/dispatch-invitations`

### RSVPs

- `GET /api/rsvps`
- `POST /api/rsvps`
- `GET /api/rsvps/{rsvp}`
- `PUT/PATCH /api/rsvps/{rsvp}`
- `DELETE /api/rsvps/{rsvp}`

### Financial Contributions

- `GET /api/financial-contributions`
- `POST /api/financial-contributions`
- `GET /api/financial-contributions/{financialContribution}`
- `PUT/PATCH /api/financial-contributions/{financialContribution}`
- `DELETE /api/financial-contributions/{financialContribution}`
- `PATCH /api/financial-contributions/{financialContribution}/confirm`

### Family Tree

- `GET /api/family-tree/descendants?ancestor_uuid={uuid}&depth={n}`

## Local Setup

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve
```

## Testing

```bash
php artisan test
```

## Notes

- API routes return JSON responses.
- Web routes render Inertia pages.
- Existing migrations, models, and Neo4j integration are preserved.
