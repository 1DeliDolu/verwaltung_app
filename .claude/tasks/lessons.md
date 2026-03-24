# Lessons Learned

Review relevant lessons before starting work in the same area.

## Entry Format

- Date:
- Area:
- Mistake:
- Lesson:
- Prevention Rule:
- Applied In Future Work:

---

## Baseline Project Rules

### Lesson 1
- Date: 2026-03-22
- Area: Authorization
- Mistake: Treating department page access as a presentation concern instead of a backend authorization concern.
- Lesson: Department visibility and department action rights must be enforced in controllers or services, not only hidden in views.
- Prevention Rule: Any new department page or task action must include an explicit server-side authorization check.
- Applied In Future Work: 

### Lesson 2
- Date: 2026-03-22
- Area: Workflow Design
- Mistake: Modeling task progress with vague labels that do not define allowed transitions.
- Lesson: Operational systems need explicit workflow states and clear transition rules.
- Prevention Rule: Every task status must have documented entry conditions, exit conditions, and authorized actors.
- Applied In Future Work:

### Lesson 3
- Date: 2026-03-22
- Area: Maintainability
- Mistake: Putting business rules directly into controllers because it is faster in the short term.
- Lesson: Thin controllers and explicit service logic reduce future bugs and make review easier.
- Prevention Rule: If controller logic starts carrying workflow decisions, extract that logic into a service or domain class.
- Applied In Future Work:

### Lesson 4
- Date: 2026-03-24
- Area: Delivery Workflow
- Mistake: Grouping multiple implementation steps before documenting and committing them, which reduced traceability for the user.
- Lesson: For this workspace, each meaningful implementation step should be documented in `_docs` and committed as a separate unit.
- Prevention Rule: Before moving to the next step, create or update its `_docs` entry and make a dedicated commit for that step.
- Applied In Future Work:
