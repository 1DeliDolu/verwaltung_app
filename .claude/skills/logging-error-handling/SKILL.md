# Purpose

Create consistent error handling and useful operational diagnostics without leaking sensitive information.

# When to Use

- Adding or changing failure paths
- Implementing form validation and domain exceptions
- Handling unexpected workflow transitions
- Improving production visibility
- Investigating recurring bugs

# Step-by-Step Application Approach

1. Identify expected versus unexpected failures.
2. Return user-safe feedback for expected failures.
3. Preserve diagnostic detail in logs for unexpected failures.
4. Ensure errors are associated with enough context to debug safely.
5. Keep logging consistent across controllers and services.
6. Verify that failures are observable but not noisy.
7. Review whether logs reveal sensitive data.

# Anti-Patterns

- Swallowing exceptions silently
- Returning raw stack traces to the user
- Logging passwords, tokens, or sensitive personal data
- Using inconsistent message formats that block debugging
- Treating all failures as generic success redirects

# Checklist

- [ ] Expected validation failures are user-safe
- [ ] Unexpected failures are logged
- [ ] Sensitive data is redacted
- [ ] Error messages are consistent
- [ ] Logging location and severity are intentional
- [ ] Failure paths were verified
- [ ] Observability is sufficient for support work
- [ ] Noise is controlled

# Stack-Specific Decision Rules

- Controllers should translate exceptions into safe HTTP or UI responses.
- Service-level failures should preserve enough domain context for debugging.
- Security-relevant failures should be logged with care and reviewed.
- Do not let operational task failures disappear behind generic redirects without traceability.
