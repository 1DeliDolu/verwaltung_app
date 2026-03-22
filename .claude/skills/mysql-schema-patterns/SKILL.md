# Purpose

Provide a reliable approach for designing relational structures that support department ownership, task lifecycle, comments, assignments, and auditability.

# When to Use

- Creating tables or modifying schema
- Adding foreign keys or indexes
- Defining status storage and workflow metadata
- Improving relational clarity for reports or dashboards

# Step-by-Step Application Approach

1. Define the domain entities and invariants.
2. Identify ownership and relationship cardinality.
3. Choose keys, constraints, nullability, and defaults carefully.
4. Align the schema with real read and write patterns.
5. Add indexes based on actual filtering and joining behavior.
6. Review migration rollout safety and backfill needs.
7. Validate integrity after the change.

# Anti-Patterns

- Encoding multiple meanings into one generic text column
- Avoiding foreign keys to hide weak application logic
- Adding indexes without expected query evidence
- Making nullable fields stand in for missing state design
- Storing workflow transitions without audit context where it matters

# Checklist

- [ ] Entities and relationships are explicit
- [ ] Foreign keys reflect real invariants
- [ ] Nullability is intentional
- [ ] Status representation is documented
- [ ] Indexes support expected queries
- [ ] Migration risk is reviewed
- [ ] Integrity checks are defined
- [ ] Rollback or recovery considerations exist

# Stack-Specific Decision Rules

- Use relational integrity to protect core business data such as users, departments, tasks, and assignments.
- Prefer explicit join tables for many-to-many relations instead of encoded lists.
- Keep status columns finite and backed by application transition rules.
- Add audit-friendly timestamps for workflow-heavy records.
