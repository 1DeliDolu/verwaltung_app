The project test suite now includes real route-level feature coverage instead of leaving the feature test files empty.

Implemented changes:
- Added a test-only redirect exception so HTTP redirects can be asserted without terminating the PHP process.
- Extended the base test case with a lightweight route dispatch helper that boots a fresh app instance, loads real routes, and returns:
  - status code
  - redirect target
  - rendered content
  - session state
- Updated the custom test runner to print pass output after execution, preventing response-code warnings during feature assertions.

Feature coverage added:
- password-rotation users are redirected from `/dashboard` to `/password/change`
- password-change screen renders for authenticated users in forced-rotation state
- guests are redirected from `/calendar` to `/login`
- public `/news` remains reachable for guests
- guests are redirected from `/tasks` to `/login`
- unknown routes return the application 404 page

Result:
- The previously empty feature test layer now verifies real route and middleware behavior through the application entry flow.
