/**
 * WordPress Helper Functions for E2E Tests
 *
 * @package WPShadow\Tests\E2E
 */

/**
 * Login to WordPress admin
 *
 * @param {import('@playwright/test').Page} page - Playwright page object
 * @param {string} username - WordPress username (default: 'admin')
 * @param {string} password - WordPress password (default: 'password')
 */
async function loginToWordPress(page, username = null, password = null) {
	const user = username || process.env.WP_ADMIN_USER || 'admin';
	const pass = password || process.env.WP_ADMIN_PASS || 'password';

	// Navigate to wp-login
	await page.goto( '/wp-login.php' );

	// Fill login form
	await page.fill( '#user_login', user );
	await page.fill( '#user_pass', pass );

	// Submit
	await page.click( '#wp-submit' );

	// Wait for admin dashboard
	await page.waitForURL( '**/wp-admin/**', { timeout: 10000 } );

	// Verify we're logged in
	const bodyClass = await page.locator( 'body' ).getAttribute( 'class' );
	if ( ! bodyClass || ! bodyClass.includes( 'wp-admin' )) {
		throw new Error( 'Failed to login to WordPress' );
	}
}

/**
 * Navigate to WPShadow admin page
 *
 * @param {import('@playwright/test').Page} page - Playwright page object
 * @param {string} subpage - Optional subpage (dashboard, kanban, workflow, etc)
 */
async function navigateToWPShadow(page, subpage = 'dashboard') {
	// Click WPShadow menu item
	await page.click( '#toplevel_page_wpshadow a.menu-top, #menu-posts-wpshadow a' );

	// Wait for page load
	await page.waitForLoadState( 'networkidle' );

	// Verify we're on WPShadow page
	const url = page.url();
	if ( ! url.includes( 'page=wpshadow' )) {
		throw new Error( 'Failed to navigate to WPShadow page' );
	}
}

/**
 * Wait for AJAX request to complete
 *
 * @param {import('@playwright/test').Page} page - Playwright page object
 * @param {string} action - WordPress AJAX action name (e.g., 'wpshadow_run_scan')
 */
async function waitForAjaxAction(page, action) {
	// Wait for admin-ajax.php request with specific action
	await page.waitForResponse(
		response =>
		response.url().includes( 'admin-ajax.php' ) &&
			response.request().postData() ? .includes( `action = ${action}` ),
		{ timeout: 30000 }
	);
}

/**
 * Wait for WordPress notice to appear
 *
 * @param {import('@playwright/test').Page} page - Playwright page object
 * @param {string} type - Notice type ('success', 'error', 'warning', 'info')
 */
async function waitForNotice(page, type = 'success') {
	const selector = `.notice - ${type}, .updated, .error`;
	await page.waitForSelector( selector, { timeout: 10000 } );
	return await page.locator( selector ).first().textContent();
}

/**
 * Dismiss WordPress admin notices
 *
 * @param {import('@playwright/test').Page} page - Playwright page object
 */
async function dismissNotices(page) {
	const dismissButtons = page.locator( '.notice-dismiss' );
	const count          = await dismissButtons.count();

	for (let i = 0; i < count; i++) {
		await dismissButtons.nth( i ).click( { force: true } );
	}
}

/**
 * Check if WPShadow plugin is active
 *
 * @param {import('@playwright/test').Page} page - Playwright page object
 * @returns {Promise<boolean>}
 */
async function isWPShadowActive(page) {
	await page.goto( '/wp-admin/plugins.php' );

	// Look for WPShadow in active plugins
	const pluginRow = page.locator( 'tr[data-slug="wpshadow"]' );

	if (await pluginRow.count() === 0) {
		return false;
	}

	const classes = await pluginRow.getAttribute( 'class' );
	return classes.includes( 'active' );
}

/**
 * Activate WPShadow plugin
 *
 * @param {import('@playwright/test').Page} page - Playwright page object
 */
async function activateWPShadow(page) {
	if (await isWPShadowActive( page )) {
		console.log( '✓ WPShadow already active' );
		return;
	}

	await page.goto( '/wp-admin/plugins.php' );

	const activateLink = page.locator( 'tr[data-slug="wpshadow"] .activate a' );

	if (await activateLink.count() > 0) {
		await activateLink.click();
		await waitForNotice( page, 'success' );
		console.log( '✓ Activated WPShadow plugin' );
	} else {
		throw new Error( 'Cannot find WPShadow plugin to activate' );
	}
}

/**
 * Take screenshot with descriptive name
 *
 * @param {import('@playwright/test').Page} page - Playwright page object
 * @param {string} name - Screenshot name
 */
async function takeScreenshot(page, name) {
	const timestamp = new Date().toISOString().replace( /[:.]/g, '-' );
	await page.screenshot(
		{
			path: `tests / e2e - reports / screenshots / ${name} - ${timestamp}.png`,
			fullPage: true
		}
	);
}

module.exports = {
	loginToWordPress,
	navigateToWPShadow,
	waitForAjaxAction,
	waitForNotice,
	dismissNotices,
	isWPShadowActive,
	activateWPShadow,
	takeScreenshot,
};
