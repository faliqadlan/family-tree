# DEPLOYMENT.md — Silsilah Keluarga Production Guide

This guide covers deploying **Silsilah Keluarga** to a production server. It is intended for human operators and automated AI deployment agents.

---

## Table of Contents

1. [Server Requirements](#1-server-requirements)
2. [Docker Compose Configuration](#2-docker-compose-configuration)
3. [Neo4j Configuration](#3-neo4j-configuration)
4. [Environment Configuration](#4-environment-configuration)
5. [Deployment Sequence](#5-deployment-sequence)
6. [Database Migrations & Seeding](#6-database-migrations--seeding)
7. [Queue & Scheduler](#7-queue--scheduler)
8. [Nginx Configuration](#8-nginx-configuration)
9. [SSL / HTTPS](#9-ssl--https)
10. [Promotion of First Admin User](#10-promotion-of-first-admin-user)
11. [Health Checks](#11-health-checks)
12. [CI/CD Pipeline (GitHub Actions)](#12-cicd-pipeline-github-actions)
13. [Rollback Procedure](#13-rollback-procedure)

---

## 1. Server Requirements

### Minimum Production Server

| Resource | Minimum | Recommended |
|---|---|---|
| CPU | 2 vCPU | 4 vCPU |
| RAM | 4 GB | 8 GB |
| Disk | 40 GB SSD | 100 GB SSD |
| OS | Ubuntu 22.04 LTS | Ubuntu 24.04 LTS |

### Required Software (bare-metal / VM)

| Software | Version |
|---|---|
| PHP | 8.2+ (with extensions: pdo_pgsql, redis, opcache, mbstring, bcmath, xml, curl) |
| Composer | 2+ |
| Node.js | 20+ (build step only) |
| PostgreSQL | 15+ |
| Neo4j | 5+ |
| Nginx | 1.24+ |
| Supervisor | latest |
| Redis | 7+ (for queues and cache) |
| Docker + Compose | 24+ / v2 (if using containerised deployment) |

---

## 2. Docker Compose Configuration

Save as `docker-compose.prod.yml` in the project root.

```yaml
version: "3.9"

services:

  app:
    build:
      context: .
      dockerfile: Dockerfile
    image: silsilah-keluarga:latest
    container_name: silsilah_app
    restart: unless-stopped
    depends_on:
      - pgsql
      - neo4j
      - redis
    environment:
      APP_ENV: production
      APP_KEY: "${APP_KEY}"
      APP_URL: "${APP_URL}"
      DB_CONNECTION: pgsql
      DB_HOST: pgsql
      DB_PORT: 5432
      DB_DATABASE: "${DB_DATABASE}"
      DB_USERNAME: "${DB_USERNAME}"
      DB_PASSWORD: "${DB_PASSWORD}"
      NEO4J_HOST: neo4j
      NEO4J_PORT: 7687
      NEO4J_USERNAME: "${NEO4J_USERNAME}"
      NEO4J_PASSWORD: "${NEO4J_PASSWORD}"
      NEO4J_DATABASE: neo4j
      REDIS_HOST: redis
      CACHE_STORE: redis
      SESSION_DRIVER: redis
      QUEUE_CONNECTION: redis
    volumes:
      - ./storage:/var/www/html/storage
    networks:
      - silsilah_net

  worker:
    image: silsilah-keluarga:latest
    container_name: silsilah_worker
    restart: unless-stopped
    command: php artisan queue:work --sleep=3 --tries=3 --max-time=3600
    depends_on:
      - app
      - redis
    environment:
      APP_ENV: production
      APP_KEY: "${APP_KEY}"
      DB_CONNECTION: pgsql
      DB_HOST: pgsql
      DB_PORT: 5432
      DB_DATABASE: "${DB_DATABASE}"
      DB_USERNAME: "${DB_USERNAME}"
      DB_PASSWORD: "${DB_PASSWORD}"
      NEO4J_HOST: neo4j
      NEO4J_PORT: 7687
      NEO4J_USERNAME: "${NEO4J_USERNAME}"
      NEO4J_PASSWORD: "${NEO4J_PASSWORD}"
      NEO4J_DATABASE: neo4j
      REDIS_HOST: redis
      QUEUE_CONNECTION: redis
    volumes:
      - ./storage:/var/www/html/storage
    networks:
      - silsilah_net

  scheduler:
    image: silsilah-keluarga:latest
    container_name: silsilah_scheduler
    restart: unless-stopped
    command: >
      sh -c "while true; do php artisan schedule:run --no-interaction; sleep 60; done"
    depends_on:
      - app
    environment:
      APP_ENV: production
      APP_KEY: "${APP_KEY}"
      DB_CONNECTION: pgsql
      DB_HOST: pgsql
      DB_DATABASE: "${DB_DATABASE}"
      DB_USERNAME: "${DB_USERNAME}"
      DB_PASSWORD: "${DB_PASSWORD}"
      NEO4J_HOST: neo4j
      NEO4J_PORT: 7687
      NEO4J_USERNAME: "${NEO4J_USERNAME}"
      NEO4J_PASSWORD: "${NEO4J_PASSWORD}"
      REDIS_HOST: redis
    volumes:
      - ./storage:/var/www/html/storage
    networks:
      - silsilah_net

  pgsql:
    image: postgres:15-alpine
    container_name: silsilah_pgsql
    restart: unless-stopped
    environment:
      POSTGRES_DB: "${DB_DATABASE}"
      POSTGRES_USER: "${DB_USERNAME}"
      POSTGRES_PASSWORD: "${DB_PASSWORD}"
    volumes:
      - pgsql_data:/var/lib/postgresql/data
    networks:
      - silsilah_net

  neo4j:
    image: neo4j:5-community
    container_name: silsilah_neo4j
    restart: unless-stopped
    environment:
      NEO4J_AUTH: "${NEO4J_USERNAME}/${NEO4J_PASSWORD}"
      NEO4J_PLUGINS: '["apoc"]'
      NEO4J_dbms_security_procedures_unrestricted: "apoc.*"
      NEO4J_dbms_memory_heap_initial__size: 512m
      NEO4J_dbms_memory_heap_max__size: 1G
      NEO4J_dbms_memory_pagecache_size: 512m
    ports:
      - "7474:7474"   # Neo4j Browser (restrict in production)
      - "7687:7687"   # Bolt protocol
    volumes:
      - neo4j_data:/data
      - neo4j_logs:/logs
      - neo4j_plugins:/plugins
    networks:
      - silsilah_net

  redis:
    image: redis:7-alpine
    container_name: silsilah_redis
    restart: unless-stopped
    command: redis-server --appendonly yes --requirepass "${REDIS_PASSWORD}"
    volumes:
      - redis_data:/data
    networks:
      - silsilah_net

  nginx:
    image: nginx:1.25-alpine
    container_name: silsilah_nginx
    restart: unless-stopped
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - ./docker/nginx/default.conf:/etc/nginx/conf.d/default.conf:ro
      - ./public:/var/www/html/public:ro
      - ./docker/ssl:/etc/ssl/silsilah:ro
    depends_on:
      - app
    networks:
      - silsilah_net

volumes:
  pgsql_data:
  neo4j_data:
  neo4j_logs:
  neo4j_plugins:
  redis_data:

networks:
  silsilah_net:
    driver: bridge
```

### Application Dockerfile

Save as `Dockerfile` in the project root.

```dockerfile
FROM php:8.3-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    bash \
    git \
    curl \
    libpng-dev \
    libjpeg-turbo-dev \
    libwebp-dev \
    libzip-dev \
    oniguruma-dev \
    postgresql-dev \
    supervisor \
    nodejs \
    npm

# Install PHP extensions
RUN docker-php-ext-install \
    pdo_pgsql \
    mbstring \
    bcmath \
    zip \
    opcache \
    pcntl

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy composer files first for caching
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --optimize-autoloader

# Copy application
COPY . .

# Install and build frontend assets
COPY package.json package-lock.json ./
RUN npm ci && npm run build && rm -rf node_modules

# Set permissions
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Optimize Laravel
RUN php artisan config:cache \
 && php artisan route:cache \
 && php artisan view:cache

EXPOSE 9000
CMD ["php-fpm"]
```

---

## 3. Neo4j Configuration

### APOC Plugin (required for graph traversal)

The APOC plugin is loaded automatically via the `NEO4J_PLUGINS` environment variable in the Docker Compose configuration above.

### Initial Constraints & Indexes

Run these Cypher statements in the Neo4j Browser (`http://your-server:7474`) or via the Bolt client after the container is running:

```cypher
-- Ensure uniqueness on Person UUID
CREATE CONSTRAINT person_uuid_unique IF NOT EXISTS
  FOR (p:Person) REQUIRE p.uuid IS UNIQUE;

-- Index for faster lookups
CREATE INDEX person_name IF NOT EXISTS
  FOR (p:Person) ON (p.full_name);
```

### Backups

```bash
# Stop Neo4j, then copy the data volume
docker exec silsilah_neo4j neo4j-admin database dump neo4j --to-path=/backups/

# Or using neo4j-admin backup (Enterprise only)
neo4j-admin backup --database=neo4j --backup-dir=/backups/
```

---

## 4. Environment Configuration

Create a `.env` file on the production server. **Never commit `.env` to source control.**

```dotenv
APP_NAME="Silsilah Keluarga"
APP_ENV=production
APP_KEY=base64:YOUR_GENERATED_APP_KEY_HERE
APP_DEBUG=false
APP_URL=https://silsilahkeluarga.example.com

LOG_CHANNEL=stack
LOG_LEVEL=warning

# PostgreSQL
DB_CONNECTION=pgsql
DB_HOST=pgsql
DB_PORT=5432
DB_DATABASE=silsilah_prod
DB_USERNAME=silsilah_user
DB_PASSWORD=STRONG_POSTGRES_PASSWORD

# Neo4j
NEO4J_HOST=neo4j
NEO4J_PORT=7687
NEO4J_USERNAME=neo4j
NEO4J_PASSWORD=STRONG_NEO4J_PASSWORD
NEO4J_DATABASE=neo4j

# Redis
REDIS_HOST=redis
REDIS_PASSWORD=STRONG_REDIS_PASSWORD
REDIS_PORT=6379

# Cache, Session & Queue (all via Redis)
CACHE_STORE=redis
SESSION_DRIVER=redis
SESSION_LIFETIME=120
QUEUE_CONNECTION=redis

# Mail (configure as appropriate)
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailgun.org
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_FROM_ADDRESS="noreply@silsilahkeluarga.example.com"
MAIL_FROM_NAME="${APP_NAME}"
```

Generate a fresh app key locally and paste the output into `APP_KEY`:

```bash
php artisan key:generate --show
```

---

## 5. Deployment Sequence

### First Deployment

```bash
# 1. Clone the repository on the server
git clone https://github.com/faliqadlan/family-tree.git /var/www/silsilah
cd /var/www/silsilah

# 2. Create the .env file (see Section 4)
nano .env

# 3. Build and start containers
docker compose -f docker-compose.prod.yml up -d --build

# 4. Run migrations
docker exec silsilah_app php artisan migrate --force

# 5. Publish Filament assets
docker exec silsilah_app php artisan filament:assets

# 6. Create first admin user (see Section 10)
docker exec -it silsilah_app php artisan tinker
```

### Subsequent Deployments (Zero-Downtime)

```bash
cd /var/www/silsilah

# 1. Pull latest code
git pull origin main

# 2. Rebuild app image
docker compose -f docker-compose.prod.yml build app

# 3. Enable maintenance mode
docker exec silsilah_app php artisan down --secret="YOUR_BYPASS_SECRET"

# 4. Run migrations
docker exec silsilah_app php artisan migrate --force

# 5. Clear and rebuild caches
docker exec silsilah_app php artisan optimize:clear
docker exec silsilah_app php artisan optimize

# 6. Restart app and worker containers
docker compose -f docker-compose.prod.yml up -d --no-deps app worker scheduler

# 7. Bring back online
docker exec silsilah_app php artisan up
```

---

## 6. Database Migrations & Seeding

```bash
# Run all pending migrations
docker exec silsilah_app php artisan migrate --force

# Seed only in staging (never run DatabaseSeeder in production unless explicitly safe)
docker exec silsilah_app php artisan db:seed --class=ProductionSeeder --force
```

---

## 7. Queue & Scheduler

Queue workers and the scheduler run as dedicated containers (`silsilah_worker`, `silsilah_scheduler`) defined in `docker-compose.prod.yml`. They restart automatically on failure.

To monitor the queue:

```bash
docker exec silsilah_app php artisan queue:monitor redis:default
```

---

## 8. Nginx Configuration

Save as `docker/nginx/default.conf`:

```nginx
server {
    listen 80;
    server_name silsilahkeluarga.example.com;
    return 301 https://$host$request_uri;
}

server {
    listen 443 ssl http2;
    server_name silsilahkeluarga.example.com;

    root /var/www/html/public;
    index index.php;

    ssl_certificate     /etc/ssl/silsilah/fullchain.pem;
    ssl_certificate_key /etc/ssl/silsilah/privkey.pem;
    ssl_protocols       TLSv1.2 TLSv1.3;
    ssl_ciphers         HIGH:!aNULL:!MD5;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";
    add_header Referrer-Policy "strict-origin-when-cross-origin";

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass   silsilah_app:9000;
        fastcgi_index  index.php;
        fastcgi_param  SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include        fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

---

## 9. SSL / HTTPS

Using Let's Encrypt with Certbot (recommended):

```bash
# On the host (not inside Docker)
apt install certbot
certbot certonly --standalone -d silsilahkeluarga.example.com

# Certificates are saved to:
# /etc/letsencrypt/live/silsilahkeluarga.example.com/fullchain.pem
# /etc/letsencrypt/live/silsilahkeluarga.example.com/privkey.pem

# Map them into the nginx container via the volume:
# ./docker/ssl:/etc/ssl/silsilah
cp /etc/letsencrypt/live/silsilahkeluarga.example.com/fullchain.pem docker/ssl/
cp /etc/letsencrypt/live/silsilahkeluarga.example.com/privkey.pem docker/ssl/
```

Add a cron job for renewal:

```
0 3 * * * certbot renew --quiet && docker compose -f /var/www/silsilah/docker-compose.prod.yml restart nginx
```

---

## 10. Promotion of First Admin User

After running migrations, promote the first admin via Tinker:

```bash
docker exec -it silsilah_app php artisan tinker
```

```php
// Inside tinker:
\App\Models\User::create([
    'name'     => 'Super Admin',
    'email'    => 'admin@example.com',
    'password' => bcrypt('ChangeMe!123'),
    'role'     => 'admin',
]);
```

Or promote an existing user:

```php
\App\Models\User::where('email', 'admin@example.com')->update(['role' => 'admin']);
```

The admin panel is then accessible at `https://silsilahkeluarga.example.com/admin`.

---

## 11. Health Checks

Add health-check endpoints to your monitoring system (e.g., UptimeRobot, Datadog):

| Check | URL | Expected |
|---|---|---|
| Application | `GET /` | HTTP 200 |
| Admin Panel | `GET /admin/login` | HTTP 200 |
| Queue (via Horizon or log) | — | no failed jobs |

Check Neo4j connectivity:

```bash
docker exec silsilah_app php artisan tinker --execute="app(\App\Repositories\Contracts\GraphRepositoryInterface::class)->getDescendantUuids('test-uuid', 1);"
```

---

## 12. CI/CD Pipeline (GitHub Actions)

Example workflow (`.github/workflows/deploy.yml`):

```yaml
name: Deploy to Production

on:
  push:
    branches: [main]

jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: shivammathur/setup-php@v2
        with:
          php-version: "8.3"
          extensions: pdo_pgsql, mbstring, bcmath, zip
      - run: composer install --no-interaction --prefer-dist
      - run: cp .env.example .env && php artisan key:generate
      - run: php artisan test --testsuite=Unit

  deploy:
    needs: test
    runs-on: ubuntu-latest
    steps:
      - name: Deploy via SSH
        uses: appleboy/ssh-action@v1
        with:
          host: ${{ secrets.PRODUCTION_HOST }}
          username: ${{ secrets.PRODUCTION_USER }}
          key: ${{ secrets.PRODUCTION_SSH_KEY }}
          script: |
            cd /var/www/silsilah
            git pull origin main
            docker compose -f docker-compose.prod.yml build app
            docker exec silsilah_app php artisan down --secret="${{ secrets.MAINTENANCE_SECRET }}"
            docker exec silsilah_app php artisan migrate --force
            docker exec silsilah_app php artisan optimize:clear
            docker exec silsilah_app php artisan optimize
            docker compose -f docker-compose.prod.yml up -d --no-deps app worker scheduler
            docker exec silsilah_app php artisan up
```

---

## 13. Rollback Procedure

```bash
cd /var/www/silsilah

# 1. Revert to previous git tag or commit
git checkout tags/v1.2.3
# or
git reset --hard HEAD~1

# 2. Enable maintenance mode
docker exec silsilah_app php artisan down

# 3. Roll back the last migration batch
docker exec silsilah_app php artisan migrate:rollback --force

# 4. Rebuild and restart
docker compose -f docker-compose.prod.yml up -d --no-deps --build app worker scheduler

# 5. Bring back online
docker exec silsilah_app php artisan up
```

---

*Last updated: 2026-03-26. For issues, open a GitHub issue at [faliqadlan/family-tree](https://github.com/faliqadlan/family-tree).*
