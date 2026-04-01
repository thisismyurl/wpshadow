/**
 * Playwright Configuration for WPShadow E2E Testing
 *
 * @see https://playwright.dev/docs/test-configuration
 */

// Load environment variables from .env file
const fs = require('fs');

require('dotenv').config({ path: './tests/e2e/.env' });

const { defineConfig, devices } = require('@playwright/test');

/**
 * WordPress site configuration
 * Update these values for your environment
 */
const WP_BASE_URL = process.env.WP_BASE_URL || 'http://localhost:9000';
const WP_ADMIN_USER = process.env.WP_ADMIN_USER || 'admin';
const WP_ADMIN_PASS = process.env.WP_ADMIN_PASS || 'password';
const PLAYWRIGHT_CHROMIUM_PATH = process.env.PW_CHROMIUM_PATH || '/usr/lib/chromium/chrome';
const chromiumExecutable = fs.existsSync( PLAYWRIGHT_CHROMIUM_PATH ) ? PLAYWRIGHT_CHROMIUM_PATH : undefined;

module.exports = defineConfig({
	testDir: './tests/e2e',

	/* Maximum time one test can run for */
	timeout: 30 * 1000, // Reduced to 30 seconds

	/* Test execution settings */
	fullyParallel: false, // Run tests sequentially for WordPress (avoid conflicts)
	forbidOnly: !!process.env.CI,
	retries: process.env.CI ? 0 : 0, // No retries to fail faster
	workers: process.env.CI ? 1 : 1, // WordPress needs sequential execution

	/* Reporter to use */
	reporter: [
		['html', { outputFolder: 'tests/e2e-reports' }],
		['list'],
	],

	/* Shared settings for all projects */
	use: {
		/* Base URL for navigation */
		baseURL: WP_BASE_URL,

		/* Collect trace when retrying failed test */
		trace: 'on-first-retry',

		/* Screenshot on failure */
		screenshot: 'only-on-failure',

		/* Video on failure */
		video: 'retain-on-failure',

		/* WordPress admin credentials */
		storageState: undefined, // Will be set up per test
	},

	/* Configure projects for major browsers */
	projects: [
		{
			name: 'chromium',
			use: {
				...devices['Desktop Chrome'],
				viewport: { width: 1920, height: 1080 },
				launchOptions: chromiumExecutable ? { executablePath: chromiumExecutable } : {},
			},
		},

		// Uncomment to test in Firefox
		// {
		// 	name: 'firefox',
		// 	use: { ...devices['Desktop Firefox'] },
		// },

		// Uncomment to test in Safari
		// {
		// 	name: 'webkit',
		// 	use: { ...devices['Desktop Safari'] },
		// },

		// Mobile testing (if needed)
		// {
		// 	name: 'Mobile Chrome',
		// 	use: { ...devices['Pixel 5'] },
		// },
	],

	/* Web server configuration (if you need to start WordPress automatically) */
	// webServer: {
	// 	command: 'cd dev-tools && docker-compose up',
	// 	url: WP_BASE_URL,
	// 	reuseExistingServer: true,
	// 	timeout: 120 * 1000,
	// },

	/* Global setup/teardown */
	globalSetup: require.resolve('./tests/e2e/helpers/global-setup.js'),
});
