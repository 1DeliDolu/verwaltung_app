# Verwaltung App

Internal operations portal for department workspaces, task execution, personnel management, and hybrid document access.

## Overview

Verwaltung App is a PHP 8.2 server-rendered business application for internal company operations. It is organized around departments such as IT, HR, and Operations and is designed to keep workflow logic, authorization boundaries, and document handling explicit.

The current project supports:

- department workspaces with role-based access
- internal task and workflow handling
- internal mail features
- calendar and operational reminders
- HR personnel records linked to IT-provisioned user accounts
- department file storage with hybrid access:
  - browser-based access inside the app
  - Samba/SMB access for network-share workflows

## Key Workflows

### IT-first personnel provisioning

IT creates the technical user account first:

- name
- email
- target department
- role
- temporary password

On first login, the user must change the password. Password rules are enforced server-side.

### HR personnel processing

HR does not create arbitrary people directly. HR creates a personnel profile only for a user that was already provisioned by IT.

The HR profile includes:

- automatically generated personnel number
- employment status
- position
- hire date
- personnel rights and notes
- GDPR/BDSG-oriented processing basis
- retention date

An employee can have multiple personnel documents.

### Hybrid file access

Department files are available through two parallel access paths:

1. Web browser inside the application via `/services/fileserver`
2. Samba/SMB share access for Explorer/Finder and external editing workflows

Both paths point to the same underlying department share structure.

## Project Structure

```text
app/                  Controllers, services, models, core classes
bin/                  CLI entrypoints for operational tasks
bootstrap/            Bootstrap and environment loading
config/               App, auth, database, filesystem configuration
database/
  migrations/         SQL migrations
  seeds/              Seed data
infra/
  demo/               Demo compose stack
  file/               Samba config and share roots
  scripts/            Operational helper scripts
public/               Front controller and assets
resources/views/      Server-rendered PHP views
routes/               Route definitions
tests/                Lightweight automated test suite and test harness
_docs/                Change documentation and verification notes
.claude/              Workspace guidance for disciplined development
```

## Main Screens

- `/dashboard`
  Department-centric start page with shortcuts and summary statistics
- `/departments`
  Visible department list for the current user
- `/departments/{slug}`
  Department workspace with documents, uploads, and department-specific actions
- `/services`
  Infrastructure overview with health indicators for mail and file services
- `/services/fileserver`
  Web file browser for department shares
- `/mail`
  Internal mail interface
- `/calendar`
  Shared calendar and reminders

## Tech Stack

- PHP 8.2+
- MySQL or MariaDB
- server-rendered MVC-style architecture
- Bootstrap-based UI
- session authentication
- role-based and department-based authorization
- local filesystem + Samba for hybrid file access
- Docker Compose for infra/demo services

## Local Application Setup

### 1. Environment

Create or update `.env` with at least:

```env
APP_NAME="Verwaltung App"
APP_ENV=local
APP_DEBUG=true

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=verwaltung_app
DB_USERNAME=root
DB_PASSWORD=your_password
```

### 2. Database

Prepare the configured database with the repo-local runner:

```bash
php bin/setup-database.php
```

Useful variants:

```bash
php bin/setup-database.php --dry-run
php bin/setup-database.php --migrate-only
php bin/setup-database.php --seed-only
APP_ENV=testing php bin/setup-database.php --fresh
```

The runner creates the configured database if needed, applies pending SQL migrations once, and applies pending seed files once. Add future schema or seed changes as new ordered `.sql` files.
On the first run against an already manually prepared database, the runner adopts that existing state into tracking tables instead of replaying old files.

### 3. Run the PHP app

Serve the app through your local PHP/Apache/Nginx setup so the app is available on your local domain, for example:

- `http://verwaltung_app.test`

## Infra and Hybrid File Access

### Single-command start

Use the wrapper scripts instead of trying to run every YAML file manually.

Start demo stack:

```bash
infra/scripts/start-hybrid-services.sh demo
```

Start internal stack:

```bash
infra/scripts/start-hybrid-services.sh internal
```

Stop demo stack:

```bash
infra/scripts/stop-hybrid-services.sh demo
```

Stop internal stack:

```bash
infra/scripts/stop-hybrid-services.sh internal
```

### Why not "run all yml files"?

Because not every `.yml` file in the repository is a Docker Compose file. Some are service configuration files such as:

- `infra/file/config.yml`

The wrapper scripts choose the correct compose files and keep startup predictable.

## Samba / SMB Notes

The demo Samba service is exposed on:

- `localhost:1445`

This is not an HTTP webpage. It is an SMB service port mapped for demo use.

Recommended role mapping:

- IT: `teamlead-it`, `employee-it`
- HR: `teamlead-hr`, `employee-hr`
- Operations: `teamlead-operations`, `employee-operations`

Important:

- `infra/file/config.yml` is treated as local operational config
- `infra/file/config.yml.example` is the committed template
- app login credentials and Samba credentials are separate by design, but should follow the same department/role model

## Security Model

The project follows these principles:

- authenticate every protected action
- authorize on the server side
- deny by default when access is incomplete
- throttle repeated login failures on the server side
- use expiring single-use password reset links for guest recovery
- throttle repeated forgot-password requests on the server side
- separate technical account provisioning from HR-sensitive personnel processing
- keep department-sensitive and personnel-sensitive files on explicit access paths
- require first-login password rotation for provisioned users
- audit personnel-document access in a dedicated log stream

## Testing

The project includes a lightweight PHP test harness for fast local verification.

Current automated coverage includes:

- password strength and password-rotation rules
- IT-managed user provisioning validation
- HR personnel profile validation
- personnel-document audit logging output

Run the suite with:

```bash
php tests/run.php
```

Reset a clean test database before the suite when you want a fresh local baseline:

```bash
APP_ENV=testing php bin/setup-database.php --fresh
php tests/run.php
```

GitHub Actions now runs the same `php tests/run.php` suite on every push and pull request after a fresh `php bin/setup-database.php --fresh`.

## Documentation Workflow

Project changes are tracked in `_docs/`.

The repository uses step-by-step documentation and verification notes for meaningful changes, including:

- implementation intent
- verification evidence
- operational notes

## Current Status

Recent project capabilities include:

- IT-first and HR-second personnel workflow
- edit/delete flows for employee records and personnel documents
- automated tests for auth and HR provisioning rules
- service health indicators for mail and file infrastructure
- stronger audit logging around personnel-document access
- collapsible department management forms
- dashboard department shortcuts and summary stats
- safe browser opening for uploaded department files
- app-integrated web file browser
- documented hybrid web + SMB access model
- wrapper scripts for starting and stopping hybrid service stacks

## Operational Notes

- personnel-document audit entries are written to `storage/logs/personnel-document-access.log`
- `/services` now evaluates live infrastructure health and may show `Healthy`, `Degraded`, or `Down`
- HR document handling now supports create, open, download, delete, and employee-profile maintenance from the department workspace
- weekly audit report automation is available via `bin/send-weekly-audit-report.php` and `infra/scripts/send-weekly-audit-report.sh`

## Weekly Audit Report Automation

Manual dashboard sending remains available from `/audit`, but unattended delivery should use the CLI entrypoint.

Dry run:

```bash
php bin/send-weekly-audit-report.php --dry-run
```

Cron-friendly wrapper:

```bash
infra/scripts/send-weekly-audit-report.sh
```

Render systemd assets:

```bash
infra/scripts/render-weekly-audit-report-systemd.sh /tmp/systemd
```

Render systemd assets with an explicit host PHP binary:

```bash
infra/scripts/render-weekly-audit-report-systemd.sh /tmp/systemd www-data www-data admin@verwaltung.local "Mon *-*-* 07:00:00" /usr/bin/php8.2
```

Render `/etc/cron.d` style asset:

```bash
infra/scripts/render-weekly-audit-report-cron.sh /tmp/verwaltung-weekly-audit-report
```

Render a cron asset with an explicit host PHP binary:

```bash
infra/scripts/render-weekly-audit-report-cron.sh /tmp/verwaltung-weekly-audit-report root admin@verwaltung.local "0 7 * * 1" /var/log/verwaltung-weekly-audit-report.log /usr/bin/php8.2
```

Install systemd assets directly into a target directory:

```bash
sudo infra/scripts/install-weekly-audit-report-systemd.sh /etc/systemd/system www-data www-data admin@verwaltung.local "Mon *-*-* 07:00:00" /usr/bin/php8.2
```

Install a cron asset directly into a target path:

```bash
sudo infra/scripts/install-weekly-audit-report-cron.sh /etc/cron.d/verwaltung-weekly-audit-report root admin@verwaltung.local "0 7 * * 1" /var/log/verwaltung-weekly-audit-report.log /usr/bin/php8.2
```

Example cron entry:

```cron
PHP_BIN=/usr/bin/php8.2
0 7 * * 1 cd /path/to/verwaltung_app && /usr/bin/env bash infra/scripts/send-weekly-audit-report.sh >> /var/log/verwaltung-audit-report.log 2>&1
```

Suggested host install flow:

1. Either render assets for review or install them directly with the new helper scripts.
2. If the host should not resolve plain `php`, pass a final `PHP_BIN` argument such as `/usr/bin/php8.2`.
3. If you used the render-only flow, copy the rendered file into `/etc/systemd/system/` or `/etc/cron.d/`.
4. For systemd, run `systemctl daemon-reload` and `systemctl enable --now verwaltung-weekly-audit-report.timer`.
