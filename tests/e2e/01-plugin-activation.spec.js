/**
 * E2E Tests: Plugin Activation and Deactivation
 *
 * @package WPShadow\Tests\E2E
 */

const { test, expect } = require('@playwright/test');
const {
	loginToWordPress,
	isWPShadowActive,
	activateWPShadow,
	waitForNotice,
} = require('./helpers/wordpress-helpers');

test.describe('WPShadow Plugin Activation', () => {

	test.beforeEach(async ({ page }) => {
		await loginToWordPress(page);
	});

	test('should be listed in plugins page', async ({ page }) => {
		await page.goto('/wp-admin/plugins.php');

		// Check if WPShadow plugin row exists
		const pluginRow = page.locator('tr[data-slug="wpshadow"]');
		await expect(pluginRow).toBeVisible();

		// Check plugin details
		const pluginName = await pluginRow.locator('.plugin-title strong').textContent();
		expect(pluginName).toContain('WPShadow');
	});

	test('should activate successfully', async ({ page }) => {
		const isActive = await isWPShadowActive(page);

		if (!isActive) {
			await activateWPShadow(page);
		}

		// Verify activation
		expect(await isWPShadowActive(page)).toBe(true);
	});

	test('should add menu item when active', async ({ page }) => {
		// Ensure plugin is active
		if (!await isWPShadowActive(page)) {
			await activateWPShadow(page);
		}

		await page.goto('/wp-admin/');

		// Check for WPShadow menu item
		const menuItem = page.locator('#toplevel_page_wpshadow, #menu-posts-wpshadow');
		await expect(menuItem).toBeVisible();

		// Check menu text
		const menuText = await menuItem.locator('.wp-menu-name').textContent();
		expect(menuText).toContain('WPShadow');
	});

	test('should have submenu items', async ({ page }) => {
		if (!await isWPShadowActive(page)) {
			await activateWPShadow(page);
		}

		await page.goto('/wp-admin/');

		// Hover over WPShadow menu to show submenu
		const menuItem = page.locator('#toplevel_page_wpshadow');
		await menuItem.hover();

		// Check for submenu items
		const submenuItems = page.locator('#toplevel_page_wpshadow .wp-submenu li');
		const count = await submenuItems.count();

		expect(count).toBeGreaterThan(0);

		// Verify expected submenu items exist
		const submenuText = await submenuItems.allTextContents();
		const submenuString = submenuText.join(' ');

		// Check for key pages (adjust based on your actual menu structure)
		expect(submenuString).toMatch(/Dashboard|Diagnostics|Kanban|Workflow/i);
	});
});
