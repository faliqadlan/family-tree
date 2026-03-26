# E2E Setup (Playwright)

## Install browser runtime

```bash
npm run e2e:install
```

## Run E2E tests

Use a running app server (default: `http://127.0.0.1:8888`):

```bash
npm run e2e
```

Override base URL if needed:

```bash
PLAYWRIGHT_BASE_URL=http://127.0.0.1:8000 npm run e2e
```

## Included smoke test

- `tests/e2e/inertia-auth-navigation.spec.ts`
  - Registers a new user
  - Verifies dashboard renders
  - Verifies Family Tree page renders
  - Verifies Profile Management page renders
