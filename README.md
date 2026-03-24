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
tests/                Test placeholders and future coverage
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
  Infrastructure overview
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

Apply SQL files in `database/migrations/` and then seed files in `database/seeds/`.

This project currently relies on ordered SQL migration files rather than a framework migration runner.

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
- separate technical account provisioning from HR-sensitive personnel processing
- keep department-sensitive and personnel-sensitive files on explicit access paths
- require first-login password rotation for provisioned users

## Documentation Workflow

Project changes are tracked in `_docs/`.

The repository uses step-by-step documentation and verification notes for meaningful changes, including:

- implementation intent
- verification evidence
- operational notes

## Current Status

Recent project capabilities include:

- IT-first and HR-second personnel workflow
- collapsible department management forms
- dashboard department shortcuts and summary stats
- safe browser opening for uploaded department files
- app-integrated web file browser
- documented hybrid web + SMB access model
- wrapper scripts for starting and stopping hybrid service stacks

## Next Practical Improvements

- add edit/delete flows for employee records and personnel documents
- add automated tests for auth and HR provisioning rules
- add service health indicators for mail and file infrastructure
- add stronger audit logging around personnel-document access
