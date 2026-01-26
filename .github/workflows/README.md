# GitHub Actions Workflows

This directory contains automated workflows for WPShadow.

## Available Workflows

### 1. E2E Tests (`e2e-tests.yml`)
Runs Playwright end-to-end tests on every push and PR.

**Triggers:**
- Push to `main` or `develop`
- Pull requests to `main` or `develop`
- Manual trigger via Actions UI

**What it does:**
- Installs Node.js and Playwright
- Runs all 31 E2E tests
- Uploads test reports as artifacts
- Comments on PRs with results

### 2. E2E Tests Scheduled (`e2e-tests-scheduled.yml`)
Runs E2E tests on a schedule for continuous monitoring.

**Triggers:**
- Daily at 2 AM UTC
- Manual trigger via Actions UI

**What it does:**
- Runs all E2E tests against staging/production
- Creates GitHub issue if tests fail
- Uploads detailed test reports

## Setup Required

See [GITHUB_ACTIONS_SETUP.md](../GITHUB_ACTIONS_SETUP.md) for complete setup instructions.

**Quick setup:**
1. Add repository secrets: `WP_BASE_URL`, `WP_ADMIN_USER`, `WP_ADMIN_PASS`
2. Push code to trigger first run
3. View results in Actions tab

## Viewing Results

1. Navigate to **Actions** tab in GitHub
2. Click on workflow run
3. Download **playwright-report** artifact
4. Extract and open `index.html`

## Manual Trigger

1. Go to **Actions** tab
2. Select **E2E Tests** workflow
3. Click **Run workflow** button
4. Click **Run workflow** to confirm

## Customization

Edit workflow files to:
- Change trigger branches
- Adjust schedule timing
- Add notification channels
- Modify test timeout
- Add more environments

## Documentation

- [Complete Setup Guide](../GITHUB_ACTIONS_SETUP.md)
- [E2E Testing Guide](../../tests/e2e/README.md)
- [Quick Start](../../E2E_TESTING_QUICKSTART.md)
