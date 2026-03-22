# CLAUDE Workspace Operating Guide

## Project Context

This workspace supports an internal PHP application for company department pages and departmental task execution. The domain is operational, permission-sensitive, and workflow-heavy. The system must remain understandable to business stakeholders while still meeting software engineering standards for reliability, security, and maintainability.

The portal is expected to support departments such as management, finance, HR, sales, marketing, operations, IT, customer support, legal, quality, and other internal functions. Some workflows are department-specific, while others cross team boundaries.

## Project Goals

The engineering goals are:

- build a maintainable internal business platform
- keep authorization boundaries explicit and safe
- support department-specific pages without fragmenting the architecture
- model task lifecycle rules cleanly
- make workflow changes affordable over time
- reduce repeated implementation mistakes through structured guidance

## Working Philosophy

This workspace follows a disciplined, plan-first, verification-first approach.

Core philosophy:

- think before editing
- keep responsibilities separated
- verify before claiming completion
- prefer explicit business rules over hidden behavior
- fix root causes, not symptoms
- write changes that another senior engineer can reason about quickly

## Plan-First Rule

Plan mode is the default for any non-trivial task.

A task requires a written plan when it includes at least one of the following:

- 3 or more implementation steps
- architecture or schema decisions
- authentication or authorization changes
- workflow state changes
- cross-file refactors
- user-facing behavior with multiple scenarios

A good plan should include:

- the problem statement
- assumptions
- affected layers
- implementation steps
- risks
- verification steps

If execution starts to drift, stop and re-plan.

## Verification-Before-Done Rule

Never mark work as complete without evidence.

Required verification depends on the change, but at minimum one of the following must be present:

- passing automated tests
- a manual verification checklist with observed results
- schema validation evidence
- permission-path verification
- logs or example outputs that prove the change behaves correctly

For medium and high-risk changes, more than one verification method is expected.

## Lessons Update Requirement

Any user correction, repeated bug, or avoidable rework must result in an update to `.claude/tasks/lessons.md`.

Each lesson entry must follow this logic:

- mistake
- lesson
- prevention rule

Before starting work in a similar area, review the relevant lessons.

## Specialist and Subagent Strategy

Use one lead agent per task and bring in specialists only when required.

Keep the main execution path clean by splitting responsibilities:

- architecture decisions -> `php-architect`
- feature implementation -> `php-mvc-engineer`
- workflow and department rule shaping -> `workflow-domain-analyst`
- schema and relational design -> `mysql-schema-engineer`
- access control and session risk -> `security-engineer`
- review and maintainability assessment -> `code-reviewer`
- tests and regression coverage -> `test-automator`
- deployment and operational configuration -> `devops-engineer`

Do not involve extra specialists unless they materially improve the result.

## Root-Cause Resolution Requirement

Workarounds are not acceptable as the default outcome.

When a defect is reported:

1. reproduce the problem
2. identify the failing layer
3. inspect logs, request flow, validation logic, and state transitions
4. isolate the root cause
5. implement the smallest durable fix
6. verify both the corrected path and nearby regression risks

Temporary patches are acceptable only when explicitly framed as temporary and tracked for replacement.

## Anti-Workaround Stance

Avoid solutions that:

- duplicate logic to bypass poor design
- hardcode department-specific exceptions in unrelated layers
- mix authorization checks into presentation only
- hide invalid state transitions instead of preventing them
- silence errors without preserving diagnostics
- solve a data issue by weakening constraints

The preferred solution is the one that remains understandable six months later.

## Security and Least-Privilege Expectations

This application is an internal system, but internal does not mean low risk.

Security requirements:

- authenticate every protected action
- authorize by both role and department context where applicable
- deny by default
- validate and normalize all request input
- protect sessions and CSRF boundaries
- avoid exposing internal identifiers unnecessarily
- keep secrets out of version control
- log security-relevant failures without leaking sensitive data

Any change to authentication, sessions, role checks, or department access paths must be reviewed by `security-engineer`.

## Maintainability Expectations

Maintainability requirements:

- controllers should coordinate, not contain domain-heavy logic
- services should express business rules clearly
- data access should be explicit and bounded
- naming should reflect the business domain
- state transitions should be understandable from the code
- repeated workflow logic should be centralized
- view templates should stay presentation-oriented

Whenever a short-term implementation conflicts with maintainability, pause and reconsider the design.

## Stack-Specific Architectural Principles

For this PHP workspace, use the following architectural defaults:

- prefer a clear MVC or MVC-like structure
- keep controllers thin
- place workflow rules in services or dedicated domain classes
- keep database constraints aligned with business invariants
- use transactions for multi-step state changes
- avoid business logic in templates
- centralize role and department access checks
- design task lifecycle states explicitly
- keep audit-friendly fields for operational entities
- validate all external input at the boundary

## Agent Selection Guide

### Use `php-architect` when:
- creating or changing application boundaries
- deciding where business rules should live
- evaluating a refactor or extension strategy
- introducing a new major workflow area

### Use `php-mvc-engineer` when:
- implementing pages, forms, controllers, services, and views
- adding department-specific feature flows
- wiring request handling to business behavior

### Use `mysql-schema-engineer` when:
- adding or changing tables, relations, indexes, or migrations
- reviewing query shape for workflow-heavy features
- protecting integrity with keys and constraints

### Use `security-engineer` when:
- touching login, sessions, permissions, CSRF, or sensitive data
- reviewing role leakage or unauthorized page access
- designing least-privilege behavior

### Use `workflow-domain-analyst` when:
- defining task statuses, approvals, escalations, ownership, or handoffs
- mapping department behavior into system rules
- clarifying ambiguous business processes

### Use `code-reviewer` when:
- reviewing a change set before completion
- challenging complexity or maintainability
- checking whether the implementation meets a senior-level quality bar

### Use `test-automator` when:
- creating or expanding regression coverage
- designing end-to-end workflow scenarios
- verifying negative paths and permission edges

### Use `devops-engineer` when:
- configuring deployment, runtime, logs, queues, cache, or web server behavior
- hardening the operational environment
- diagnosing environment-specific failures

## When Skills Should Be Invoked

Read the relevant skill before implementation when the task affects one of these areas:

- `php-mvc-patterns` for request-to-response architecture
- `authentication-authorization-patterns` for identity and access logic
- `mysql-schema-patterns` for schema and relational decisions
- `testing-patterns` for verification design
- `logging-error-handling` for diagnostics and failure flow
- `task-workflow-patterns` for task lifecycle and cross-department execution

Skills are not optional reference material for non-trivial changes. They are part of the expected working discipline.

## Definition of Done

Work is done only when:

- the plan was appropriate to the task
- the implementation matches the plan or documented re-plan
- the relevant skills were applied
- the result is verified with evidence
- maintainability and security expectations were reviewed
- lessons were updated when needed

If any of these are missing, the task is not done.
