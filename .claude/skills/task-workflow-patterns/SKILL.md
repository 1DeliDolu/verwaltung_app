# Purpose

Provide a structured pattern for designing and implementing task lifecycle behavior across departments.

# When to Use

- Defining task creation, assignment, review, completion, rejection, or escalation
- Changing task statuses or allowed transitions
- Adding department-specific workflow behavior
- Introducing dashboards or queues based on task state

# Step-by-Step Application Approach

1. Define the actors involved.
2. Define the task states and what each state means.
3. Define allowed transitions and who may trigger them.
4. Define side effects such as comments, notifications, or audit updates.
5. Separate universal task behavior from department-specific rules.
6. Implement state changes in a centralized service path.
7. Verify positive, negative, and cross-department scenarios.

# Anti-Patterns

- Using vague status names without transition meaning
- Allowing direct state jumps without policy review
- Spreading transition logic across controllers and templates
- Treating comments or audit history as optional when the process depends on accountability
- Letting department exceptions accumulate as scattered conditionals

# Checklist

- [ ] Actors are named
- [ ] States are explicit
- [ ] Allowed transitions are defined
- [ ] Unauthorized transitions are blocked
- [ ] Side effects are clear
- [ ] Audit needs are considered
- [ ] Department-specific rules are isolated
- [ ] Verification covers edge cases

# Stack-Specific Decision Rules

- Centralize task state changes in one service path when possible.
- Keep workflow policy explicit and reviewable by non-UI code.
- If departments differ, express those differences as configuration or isolated policy logic, not scattered conditionals.
- Preserve history for task reassignment, rejection, and completion events when operational accountability matters.
