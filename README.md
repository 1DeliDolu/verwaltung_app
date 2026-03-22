# Department Operations Portal

## Project Overview

Department Operations Portal is an internal PHP application for managing company departments, their operational pages, and their task workflows in a single controlled workspace. Each department has its own page, responsibilities, access rules, and execution flow, while leadership can still observe progress across the organization.

The application is designed for a modern company structure that includes both revenue-generating teams and support functions, such as management, finance, HR, sales, marketing, operations, IT, legal, quality, and customer support.

## Project Goal

The primary goal is to provide a maintainable internal business application where:

- each department has a dedicated operational area
- users can see only the departments and actions they are allowed to access
- tasks can be created, assigned, tracked, reviewed, and closed consistently
- cross-department coordination remains visible and auditable
- future workflow expansion can happen without rewriting the entire system

## Assumed Technology Stack

This workspace assumes the following baseline stack:

- PHP 8.2+
- MySQL or MariaDB
- Server-rendered MVC-style architecture
- HTML, CSS, and light JavaScript for the interface
- Session-based authentication
- Role-based and department-based authorization
- Linux deployment behind Apache or Nginx

These assumptions are intentionally conservative so the workspace guidance stays directly usable for a standard internal PHP business application.

## Purpose of This Workspace

This repository includes a `.claude` workspace that defines operational guidance for AI-assisted development. It is intended to make project work more structured, safer, and more repeatable.

The workspace exists to help the team:

- plan work before implementation
- select the right specialist role for a given task
- apply repeatable engineering skills
- track active work and verification steps
- record lessons from mistakes and user corrections
- keep architectural and security decisions consistent

## What `.claude` Is Used For

The `.claude` directory contains project operating material for agent-driven development:

- `settings.local.json` stores simple local workflow expectations
- `tasks/todo.md` is the primary active work template
- `tasks/lessons.md` records mistakes, lessons, and prevention rules
- `agents/` contains specialist operating roles
- `skills/` contains reusable implementation and review patterns

This structure is not application code. It is the working discipline layer around the codebase.

## How Agent Files Should Be Used

Each file under `.claude/agents` represents a specialist role. Use the agent whose scope best matches the problem:

- use **php-architect** for architectural direction and boundary decisions
- use **php-mvc-engineer** for feature implementation in controllers, services, views, and request flow
- use **mysql-schema-engineer** for database schema design, migrations, and query-sensitive data modeling
- use **security-engineer** for authentication, authorization, secrets, sessions, and abuse prevention
- use **workflow-domain-analyst** for task lifecycle, department behavior, approval paths, and business rules
- use **code-reviewer** for change review and maintainability evaluation
- use **test-automator** for verification strategy and regression coverage
- use **devops-engineer** for deployment, runtime configuration, logs, and operational hardening

Choose one lead agent for a task and bring in specialist support only where needed.

## How Skill Folders Should Be Used

Each skill folder captures a reusable engineering pattern. Skills are meant to guide execution, not replace judgment.

Recommended usage:

- read the relevant skill before starting non-trivial implementation
- apply its checklist while working
- use its anti-pattern list during review
- prefer the skill's decision rules when multiple solutions seem possible

Examples:

- use `php-mvc-patterns` before designing a new feature flow
- use `authentication-authorization-patterns` before changing login, session, role, or access logic
- use `mysql-schema-patterns` before adding or changing relational structures
- use `testing-patterns` before marking a feature as complete
- use `logging-error-handling` when touching failure paths or operational visibility
- use `task-workflow-patterns` when changing how departments create, assign, escalate, or close work

## Task Management Flow

The expected task flow is:

1. Define the request in `todo.md`.
2. Write assumptions, constraints, and a short execution plan.
3. Identify which agent should lead the work.
4. Identify which skills apply.
5. Execute the work in small, reviewable steps.
6. Record verification evidence.
7. Summarize the result and open risks.
8. Update `lessons.md` if a mistake, correction, or repeated issue occurred.

A task is not complete just because the code was written. It is complete only when the result was verified.

## Recommended Working Discipline

Use the following discipline as a default:

- plan first for any task with more than a trivial change
- prefer root-cause fixes over patches
- keep controllers thin and domain logic explicit
- design for role isolation and least privilege
- make department rules visible in code rather than hidden in views
- avoid mixed responsibilities in a single class or file
- document assumptions when the domain is incomplete
- keep naming aligned with business concepts used by the company

## Verification Approach

Verification should match the risk of the change.

Typical verification evidence includes:

- manual scenario walkthroughs for page flow and permissions
- unit tests for business rules and service methods
- integration tests for end-to-end task flow
- database validation for schema and relational integrity
- negative-path checks for unauthorized access, invalid state changes, and bad input
- log review for failures and unexpected warnings

Do not mark work as done without concrete evidence.

## Contribution and Extension Notes

When extending this project:

- preserve clear separation between presentation, business rules, and persistence
- add new departments through configuration and explicit authorization rules
- avoid copy-paste department logic; centralize reusable workflow behavior
- expand skills and agents only when there is a repeated need
- update lessons whenever the team discovers a repeated mistake pattern

A good extension improves clarity and reduces future maintenance cost, not just current delivery speed.
