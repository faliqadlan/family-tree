# Silsilah Keluarga — Privacy-First Family Tree

**Silsilah Keluarga** is a privacy-first family tree web application built on a **Hybrid Database Architecture** combining PostgreSQL (SQL) and Neo4j (Graph). It enables families to manage member profiles with granular privacy controls, organise events with RSVP and contribution tracking, and visualise kinship relationships as an interactive graph.

---

## Table of Contents

- [Architecture Overview](#architecture-overview)
- [Technology Stack](#technology-stack)
- [Project Structure](#project-structure)
- [Key Features](#key-features)
- [Getting Started (Local Development)](#getting-started-local-development)
- [Admin Panel (Filament v3)](#admin-panel-filament-v3)
- [API Endpoints](#api-endpoints)
- [Testing](#testing)
- [Deployment](#deployment)

---

## Architecture Overview

```
┌──────────────────────────────────────────────────────────┐
│                    Client / Browser                       │
└───────────────┬──────────────────────────┬───────────────┘
                │  REST API (Sanctum)       │  Admin Panel (/admin)
                ▼                          ▼
┌──────────────────────┐       ┌──────────────────────────┐
│  API Controllers     │       │  Filament v3 Admin Panel  │
│  (app/Http/Api/)     │       │  (app/Filament/Resources) │
└──────────┬───────────┘       └────────────┬─────────────┘
           │                                │
           ▼                                ▼
┌──────────────────────────────────────────────────────────┐
│              Service & Repository Layer                   │
│  PrivacyEngineService │ SmartInvitationService            │
│  EloquentUserRepository │ Neo4jGraphRepository            │
└──────────┬───────────────────────────────┬───────────────┘
           │                               │
           ▼                               ▼
┌────────────────────┐         ┌──────────────────────────┐
│   PostgreSQL       │         │   Neo4j Graph Database    │
│  (Operational DB)  │         │  (Relationship Graph)     │
│  users, profiles,  │         │  Person nodes, CHILD_OF,  │
│  events, rsvps,    │         │  MARRIED_TO relationships │
│  contributions     │         │                           │
└────────────────────┘         └──────────────────────────┘
```

**PostgreSQL** stores all structured, relational data (users, profiles, events, RSVPs, financial contributions).
**Neo4j** stores the family graph — person nodes and the relationships between them (`CHILD_OF`, `MARRIED_TO`). This enables efficient traversal queries such as "find all descendants within N generations."

---

## Technology Stack

| Layer | Technology | Version |
|---|---|---|
| Framework | Laravel | ^12.0 |
| Language | PHP | ^8.2 |
| SQL Database | PostgreSQL | 15+ |
| Graph Database | Neo4j | 5+ |
| Neo4j Client | laudis/neo4j-php-client | ^2.7 |
| Admin Panel | Filament | ^3.3 |
| Realtime UI | Livewire | ^3.0 |
| API Auth | Laravel Sanctum | built-in |
| Frontend Build | Vite + TailwindCSS | ^7 / ^4 |
| Testing | PHPUnit | ^11 |
| Dev Environment | Laravel Sail (Docker) | ^1.41 |

---

## Project Structure

```
app/
├── Filament/
│   └── Resources/          # Filament v3 admin resources
│       ├── UserResource/
│       ├── ProfileResource/
│       └── EventResource/
│           ├── Pages/
│           └── RelationManagers/
│               ├── RsvpsRelationManager.php
│               └── FinancialContributionsRelationManager.php
├── Http/Controllers/Api/   # REST API controllers
├── Models/                 # Eloquent models
├── Observers/              # Model event observers (Neo4j sync)
├── Policies/               # Authorization policies
├── Providers/
│   ├── AppServiceProvider.php
│   └── Filament/
│       └── AdminPanelProvider.php
├── Repositories/           # Data-access abstractions
│   ├── Contracts/
│   ├── EloquentUserRepository.php
│   └── Neo4jGraphRepository.php
└── Services/               # Business logic services
    ├── Contracts/
    ├── PrivacyEngineService.php
    ├── SmartInvitationService.php
    └── Neo4j/
        └── Neo4jService.php
```

---

## Key Features

### Privacy Engine
Field-level privacy controls per profile. Each sensitive field (`phone`, `email`, `dob`, `address`) has an independent privacy setting:
- **public** — visible to everyone
- **masked** — shown as `***` unless an approved `AccessRequest` exists
- **private** — never visible to others

### Smart Invitation System
Graph-aware invitation dispatch: given an event's `ancestor_node_id` and `invitation_depth`, the system traverses the Neo4j graph to find all eligible family members within N generations and sends them invitations.

### Filament Admin Panel
A secure administrative backend at `/admin`:
- **Super-Admins** can manage users, roles, and moderate all profiles.
- **Event Committees (Panitia)** can manage their assigned events, track RSVPs, and verify financial contributions (Patungan).

### Financial Contributions (Patungan)
Committee members (coordinator/treasurer) can record, review, and confirm/reject monetary contributions per event, supporting multiple payment methods (bank transfer, GoPay, OVO, DANA, etc.).

---

## Getting Started (Local Development)

### Prerequisites
- PHP 8.2+
- Composer 2+
- Node.js 20+ & npm
- Docker Desktop (for Sail)

### Setup

```bash
# 1. Clone the repository
git clone https://github.com/faliqadlan/family-tree.git
cd family-tree

# 2. Install PHP dependencies
composer install

# 3. Install JS dependencies
npm install

# 4. Copy and configure environment
cp .env.example .env
php artisan key:generate

# 5. Start services via Docker Sail
./vendor/bin/sail up -d

# 6. Run migrations
./vendor/bin/sail artisan migrate

# 7. Build frontend assets
npm run build
```

### Environment Variables

Key variables to configure in `.env`:

```dotenv
APP_NAME="Silsilah Keluarga"
APP_URL=http://localhost

DB_CONNECTION=pgsql
DB_HOST=pgsql
DB_PORT=5432
DB_DATABASE=family_tree
DB_USERNAME=sail
DB_PASSWORD=password

NEO4J_HOST=neo4j
NEO4J_PORT=7687
NEO4J_USERNAME=neo4j
NEO4J_PASSWORD=your-neo4j-password
NEO4J_DATABASE=neo4j
```

---

## Admin Panel (Filament v3)

The admin panel is available at `/admin`.

### Access Control

| Role | Access |
|---|---|
| `admin` (Super-Admin) | Full access: Users, Profiles, Events, RSVPs, Contributions |
| Event Committee Member | Restricted to events they are assigned to; RSVPs & Contributions managers |
| Regular `user` | No admin access |

### Promoting a User to Super-Admin

```bash
php artisan tinker
>>> \App\Models\User::where('email', 'admin@example.com')->update(['role' => 'admin']);
```

### Resources

| Resource | Path | Who can access |
|---|---|---|
| Users | `/admin/users` | Super-Admin |
| Profiles | `/admin/profiles` | Super-Admin |
| Events | `/admin/events` | Super-Admin, Event Committee |
| RSVPs | `/admin/events/{id}` (tab) | Super-Admin, Event Creator/Committee |
| Contributions | `/admin/events/{id}` (tab) | Super-Admin, Coordinator/Treasurer |

---

## API Endpoints

All endpoints require `Authorization: Bearer {sanctum-token}`.

### Profiles
| Method | Path | Description |
|---|---|---|
| `GET` | `/api/profiles/{profile}` | View profile (filtered by PrivacyEngine) |
| `PATCH` | `/api/profiles/{profile}` | Update own profile |

### Access Requests
| Method | Path | Description |
|---|---|---|
| `GET` | `/api/access-requests` | List my access requests |
| `POST` | `/api/access-requests` | Request access to private fields |
| `PATCH` | `/api/access-requests/{id}/respond` | Approve or deny a request |

### Events
| Method | Path | Description |
|---|---|---|
| `GET` | `/api/events` | List events |
| `POST` | `/api/events` | Create event |
| `GET` | `/api/events/{event}` | View event |
| `PATCH` | `/api/events/{event}` | Update event |
| `DELETE` | `/api/events/{event}` | Delete event |
| `POST` | `/api/events/{event}/dispatch-invitations` | Send graph-based invitations |

### Financial Contributions
| Method | Path | Description |
|---|---|---|
| `GET` | `/api/events/{event}/contributions` | List contributions |
| `POST` | `/api/events/{event}/contributions` | Submit contribution |
| `PATCH` | `/api/events/{event}/contributions/{id}/confirm` | Confirm contribution |

### Family Tree
| Method | Path | Description |
|---|---|---|
| `GET` | `/api/family-tree/descendants` | Get descendant UUIDs from Neo4j |

---

## Testing

```bash
# Run all tests
php artisan test

# Run a specific test suite
php artisan test --testsuite=Unit
php artisan test --testsuite=Feature
```

The unit tests cover `PrivacyEngineService` logic. Integration tests mock the `GraphRepositoryInterface` to avoid requiring a live Neo4j connection.

---

## Deployment

See [DEPLOYMENT.md](DEPLOYMENT.md) for a full production deployment guide including Docker Compose configuration, Neo4j setup, environment hardening, and CI/CD pipeline steps.

---

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
