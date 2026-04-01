/**
 * E2E Tests: WPShadow Dashboard
 *
 * @package WPShadow\Tests\E2E
 */

const { test, expect } = require('@playwright/test');
const {
	loginToWordPress,
	navigateToWPShadow,
	activateWPShadow,
	isWPShadowActive,
} = require('./helpers/wordpress-helpers');

test.describe('WPShadow Dashboard', () => {

	test.beforeEach(async ({ page }) => {
		await loginToWordPress(page);

		// Ensure plugin is active
		if (!await isWPShadowActive(page)) {
			await activateWPShadow(page);
		}
	});

	test('should load dashboard page', async ({ page }) => {
		await navigateToWPShadow(page, 'dashboard');

		// Check page title
		const heading = page.locator('h1, .wpshadow-page-title');
		await expect(heading.first()).toBeVisible();

		// Verify we're on WPShadow page
		const url = page.url();
		expect(url).toContain('page=wpshadow');
	});

	test('should display site health summary', async ({ page }) => {
		await navigateToWPShadow(page, 'dashboard');

		// Look for health score or status indicators
		const healthWidget = page.locator('.site-health-summary, .wpshadow-health-score, .health-widget');

		// At least one health indicator should be visible
		const count = await healthWidget.count();
		expect(count).toBeGreaterThan(0);
	});

	test('should have scan button', async ({ page }) => {
		await navigateToWPShadow(page, 'dashboard');

		// Look for scan button (adjust selector based on your implementation)
		const scanButton = page.locator('button:has-text("Scan"), button:has-text("Run"), .scan-button, #run-scan-btn');

		await expect(scanButton.first()).toBeVisible();
	});

	test('should display statistics or KPIs', async ({ page }) => {
		await navigateToWPShadow(page, 'dashboard');

		// Look for stats/KPI widgets
		const statsWidgets = page.locator('.wpshadow-stat, .kpi-widget, .dashboard-stat, .stat-card');

		// Should have at least some stats
		const count = await statsWidgets.count();
		expect(count).toBeGreaterThan(0);
	});

	test('should not have JavaScript errors', async ({ page }) => {
		const errors = [];

		// Capture console errors
		page.on('console', msg => {
			if (msg.type() === 'error') {
				errors.push(msg.text());
			}
		});

		// Capture page errors
		page.on('pageerror', error => {
			errors.push(error.message);
		});

		await navigateToWPShadow(page, 'dashboard');

		// Wait a bit for any async errors
		await page.waitForTimeout(2000);

		// Filter out known WordPress warnings
		const criticalErrors = errors.filter(error =>
			!error.includes('wp-embed') &&
			!error.includes('heartbeat') &&
			!error.includes('autosave')
		);

		if (criticalErrors.length > 0) {
			console.log('JavaScript errors detected:', criticalErrors);
		}

		expect(criticalErrors).toHaveLength(0);
	});

	test('should have accessible navigation', async ({ page }) => {
		await navigateToWPShadow(page, 'dashboard');

		// Check for proper heading hierarchy
		const h1Count = await page.locator('h1').count();
		expect(h1Count).toBeGreaterThanOrEqual(1);

		// Check for landmarks
		const mainContent = page.locator('main, [role="main"], .wpshadow-content');
		const hasLandmark = await mainContent.count() > 0;

		// At minimum, content should be in a meaningful container
		expect(hasLandmark || h1Count >= 1).toBeTruthy();
	});
});
