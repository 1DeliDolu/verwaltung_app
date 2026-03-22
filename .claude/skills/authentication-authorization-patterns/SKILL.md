# Purpose

Define safe patterns for authentication, session handling, role checks, and department-based authorization in the internal portal.

# When to Use

- Implementing login or logout behavior
- Adding protected department pages
- Defining role access to task actions
- Changing session or CSRF handling
- Reviewing least-privilege behavior

# Step-by-Step Application Approach

1. Identify the actor, resource, and action.
2. Confirm the authentication requirement.
3. Define authorization rules using role and department context.
4. Enforce checks on the server side before any protected operation.
5. Validate all state-changing requests.
6. Return safe errors for unauthorized actions.
7. Log security-relevant failures appropriately.
8. Verify positive and negative access scenarios.

# Anti-Patterns

- Hiding buttons without enforcing backend checks
- Using a single broad admin bypass for convenience
- Trusting client-provided department identifiers without validation
- Mixing authentication logic into unrelated services
- Returning overly detailed access-denied error information

# Checklist

- [ ] Protected action requires authentication
- [ ] Authorization is explicit and testable
- [ ] Role and department boundaries are enforced
- [ ] CSRF handling exists for state changes
- [ ] Sessions use safe defaults
- [ ] Sensitive data is not leaked
- [ ] Unauthorized paths were tested
- [ ] Security-relevant events are observable

# Stack-Specific Decision Rules

- Always enforce page access and action access on the server side.
- Use deny-by-default when access rules are incomplete.
- If a user can operate in multiple departments, evaluate the active department context explicitly.
- Review every new state-changing form for CSRF and authorization together, not separately.
