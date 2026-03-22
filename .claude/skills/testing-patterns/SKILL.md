# Purpose

Define a practical verification strategy for workflow-heavy PHP features so changes can be accepted with confidence.

# When to Use

- Completing a new feature
- Fixing a bug
- Changing task status rules
- Updating access control
- Touching schema-driven behavior

# Step-by-Step Application Approach

1. Identify the risk of the change.
2. List critical scenarios, including invalid and unauthorized paths.
3. Choose automated tests, manual checks, or both.
4. Write concise scenario names that reflect business behavior.
5. Capture the observed result, not just the intended result.
6. Re-check nearby regression paths.
7. Record evidence in the task file.

# Anti-Patterns

- Testing only the happy path
- Marking work done based only on code review
- Ignoring role and department permutations
- Running one manual check and calling it sufficient for a risky change
- Treating missing observability as acceptable

# Checklist

- [ ] Positive path covered
- [ ] Negative path covered
- [ ] Unauthorized path covered
- [ ] Invalid input path covered
- [ ] Data integrity checked
- [ ] Regression risk reviewed
- [ ] Evidence recorded
- [ ] Open gaps documented

# Stack-Specific Decision Rules

- Any change to authorization requires a negative-path test.
- Any change to task lifecycle requires at least one state-transition regression check.
- Any change to schema-backed logic requires data integrity verification.
- Manual verification is acceptable for low-risk UI changes, but not as the only evidence for medium or high-risk backend behavior.
