# Purpose

Provide a repeatable pattern for implementing features in a PHP MVC-style application without mixing routing, business rules, and presentation logic.

# When to Use

- Adding a new department page
- Building a task creation or update flow
- Refactoring controller-heavy code
- Standardizing request validation and response behavior

# Step-by-Step Application Approach

1. Define the business action clearly.
2. Identify the route and request boundary.
3. Validate and normalize input early.
4. Keep the controller focused on orchestration.
5. Move workflow and decision logic into a service or domain class.
6. Access persistence through explicit model or repository interactions.
7. Render a view that consumes prepared data without deciding business rules.
8. Verify both the successful and failed paths.

# Anti-Patterns

- Putting workflow decisions directly in the template
- Letting the controller contain long conditional trees
- Reading raw request data deep inside service code
- Duplicating department-specific logic across multiple controllers
- Returning inconsistent response behavior for similar actions

# Checklist

- [ ] Route responsibility is clear
- [ ] Request input is validated and normalized
- [ ] Controller remains thin
- [ ] Service or domain logic is explicit
- [ ] View is presentation-oriented
- [ ] Authorization is enforced server-side
- [ ] Error handling is consistent
- [ ] Verification covers positive and negative paths

# Stack-Specific Decision Rules

- If the feature changes task state, prefer a service method over inline controller logic.
- If multiple departments share behavior, centralize the workflow rule.
- If a view needs to know whether an action is allowed, compute that from backend authorization results rather than embedding raw policy logic in the template.
- If the controller exceeds a simple orchestration role, split the logic before adding more conditions.
