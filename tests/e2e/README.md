# WPShadow E2E Testing with Playwright

End-to-end browser automation tests for WPShadow WordPress plugin.

## Prerequisites

1. **Node.js** (v18+) - Already installed
2. **WordPress site** - Running and accessible (local or remote)
3. **WPShadow plugin** - Installed in the WordPress site

## Quick Start

### 1. Install Dependencies

```bash
npm install
```

### 2. Configure Environment

Copy `.env.example` to `.env` and update:

```bash
cp tests/e2e/.env.example tests/e2e/.env
```

Edit `.env`:
```env
WP_BASE_URL=http://localhost:9000
WP_ADMIN_USER=admin
WP_ADMIN_PASS=password
```

### 3. WordPress should be running in Codespaces

The WordPress environment is automatically set up in GitHub Codespaces.

Wait for WordPress to be ready:
```bash
# Check if accessible (URL shown in terminal on Codespaces start)
curl http://localhost:9000/wp-admin/
```

### 4. Run Tests

```bash
# Run all tests
npm run test:e2e

# Run specific test file
npx playwright test tests/e2e/02-dashboard.spec.js

# Run tests in headed mode (see browser)
npx playwright test --headed

# Run tests with UI (interactive)
npx playwright test --ui

# Debug specific test
npx playwright test --debug tests/e2e/03-diagnostics.spec.js
```

## Test Structure

```
tests/e2e/
├── helpers/
│   ├── global-setup.js       # Runs once before all tests
│   └── wordpress-helpers.js  # WordPress-specific helper functions
├── 01-plugin-activation.spec.js    # Plugin activation/deactivation
├── 02-dashboard.spec.js            # Dashboard loading and display
├── 03-diagnostics.spec.js          # Diagnostic scanning
├── 04-treatments.spec.js           # Treatment application
├── 05-kanban-board.spec.js         # Kanban board interactions
├── 06-workflow-builder.spec.js     # Workflow builder
└── README.md                       # This file
```

## Available Test Suites

### Plugin Activation (`01-plugin-activation.spec.js`)
- ✓ Plugin listed in plugins page
- ✓ Activates successfully
- ✓ Adds menu item when active
- ✓ Has submenu items

### Dashboard (`02-dashboard.spec.js`)
- ✓ Loads dashboard page
- ✓ Displays site health summary
- ✓ Has scan button
- ✓ Displays statistics/KPIs
- ✓ No JavaScript errors
- ✓ Accessible navigation

### Diagnostics (`03-diagnostics.spec.js`)
- ✓ Runs quick scan
- ✓ Displays finding details
- ✓ Apply treatment button on fixable findings
- ✓ Shows KB links

### Treatments (`04-treatments.spec.js`)
- ✓ Shows confirmation modal
- ✓ Applies treatment successfully
- ✓ Shows undo button after applying
- ✓ Handles errors gracefully

### Kanban Board (`05-kanban-board.spec.js`)
- ✓ Loads kanban board page
- ✓ Displays columns
- ✓ Displays finding cards
- ✓ Cards are draggable
- ✓ Drag between columns
- ✓ Persists position after reload

### Workflow Builder (`06-workflow-builder.spec.js`)
- ✓ Loads workflow builder page
- ✓ Has create workflow button
- ✓ Displays existing workflows
- ✓ Opens wizard modal
- ✓ Shows trigger selection
- ✓ Shows action selection
- ✓ Saves new workflow

## Helper Functions

Available in `helpers/wordpress-helpers.js`:

```javascript
// Login to WordPress
await loginToWordPress(page, 'admin', 'password');

// Navigate to WPShadow
await navigateToWPShadow(page, 'dashboard');

// Wait for AJAX action
await waitForAjaxAction(page, 'wpshadow_run_scan');

// Wait for WordPress notice
const message = await waitForNotice(page, 'success');

// Dismiss admin notices
await dismissNotices(page);

// Check if plugin is active
const isActive = await isWPShadowActive(page);

// Activate plugin
await activateWPShadow(page);

// Take screenshot
await takeScreenshot(page, 'diagnostic-scan');
```

## Viewing Test Results

After running tests:

```bash
# Open HTML report
npx playwright show-report tests/e2e-reports
```

The report includes:
- Test pass/fail status
- Screenshots of failures
- Videos of failed tests
- Network requests
- Console logs

## Debugging Tests

### Interactive UI Mode
```bash
npx playwright test --ui
```

### Debug Mode (Step Through)
```bash
npx playwright test --debug
```

### Headed Mode (See Browser)
```bash
npx playwright test --headed
```

### Slow Motion (Watch Actions)
```bash
npx playwright test --headed --slow-mo 1000
```

### Single Test
```bash
npx playwright test --headed -g "should run quick scan"
```

## CI/CD Integration

### GitHub Actions Example

```yaml
name: E2E Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3

      - name: Setup Node.js
        uses: actions/setup-node@v3
        with:
          node-version: 18

      - name: Install dependencies
        run: npm install

      - name: Install Playwright browsers
        run: npx playwright install --with-deps chromium

      - name: Ensure WordPress is ready
        run: |
          echo "WordPress should be running in test environment"
          sleep 10

      - name: Run E2E tests
        run: npm run test:e2e
        env:
          WP_BASE_URL: http://localhost:9000
          WP_ADMIN_USER: admin
          WP_ADMIN_PASS: password

      - name: Upload test results
        if: always()
        uses: actions/upload-artifact@v3
        with:
          name: playwright-report
          path: tests/e2e-reports/
```

## Best Practices

1. **Run tests in CI/CD** - Catch issues before deployment
2. **Keep tests independent** - Each test should work alone
3. **Use data attributes** - Add `data-testid` to elements for reliable selectors
4. **Wait for AJAX** - Use helper functions for async operations
5. **Take screenshots** - On failure for debugging
6. **Clean up** - Undo changes made during tests

## Troubleshooting

### WordPress not accessible
```bash
# Check if WordPress is running
curl http://localhost:9000/wp-admin/

# WordPress runs in GitHub Codespaces environment
# Check the terminal for the correct URL
```

### Tests timing out
- Increase timeout in test: `test.setTimeout(120000)`
- Check WordPress performance
- Verify network connectivity

### Element not found
- Use `page.pause()` to inspect page
- Run with `--headed` to see what's happening
- Update selectors based on actual HTML

### Browser won't start
```bash
# Reinstall browsers
npx playwright install --force chromium
```

## Writing New Tests

1. Create new spec file: `tests/e2e/07-my-feature.spec.js`
2. Use existing helpers from `wordpress-helpers.js`
3. Follow naming convention: `test.describe` → feature, `test` → specific behavior
4. Add to documentation when complete

Example:
```javascript
const { test, expect } = require('@playwright/test');
const { loginToWordPress, navigateToWPShadow } = require('./helpers/wordpress-helpers');

test.describe('My Feature', () => {
	test.beforeEach(async ({ page }) => {
		await loginToWordPress(page);
		await navigateToWPShadow(page);
	});

	test('should do something', async ({ page }) => {
		// Your test code here
		const element = page.locator('.my-element');
		await expect(element).toBeVisible();
	});
});
```

## Resources

- [Playwright Documentation](https://playwright.dev)
- [Playwright Test API](https://playwright.dev/docs/api/class-test)
- [Selectors Guide](https://playwright.dev/docs/selectors)
- [Best Practices](https://playwright.dev/docs/best-practices)
