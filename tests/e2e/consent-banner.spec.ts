import { test, expect } from '@playwright/test';

test.describe('WPShadow AJAX & Consent Banner Tests', () => {
	const baseURL = process.env.WP_BASE_URL || 'https://wpshadow.com';

	test('AJAX test handler should work (no auth required)', async ({ page }) => {
		// Test the AJAX handler directly without authentication
		const response = await page.evaluate(async (url) => {
			const result = await fetch(`${url}/wp-admin/admin-ajax.php`, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded',
				},
				body: 'action=wpshadow_test_ajax',
			});
			return await result.json();
		}, baseURL);
		
		expect(response.success).toBe(true);
		expect(response.data.message).toContain('AJAX is working');
	});

	test('consent handler should require nonce', async ({ page }) => {
		// Test that consent handler validates nonce
		const response = await page.evaluate(async (url) => {
			const result = await fetch(`${url}/wp-admin/admin-ajax.php`, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded',
				},
				body: 'action=wpshadow_save_consent&consent_preferences=all',
			});
			return await result.json();
		}, baseURL);
		
		expect(response.success).toBe(false);
		expect(response.data.message).toContain('Security check failed');
	});

	test('invalid nonce should be rejected', async ({ page }) => {
		// Test that invalid nonce is properly rejected
		const response = await page.evaluate(async (url) => {
			const result = await fetch(`${url}/wp-admin/admin-ajax.php`, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded',
				},
				body: 'action=wpshadow_save_consent&nonce=invalid_nonce_xyz&telemetry=false',
			});
			return await result.json();
		}, baseURL);
		
		expect(response.success).toBe(false);
		expect(response.data.message).toContain('Security check failed');
	});

	test('consent dismiss handler requires nonce', async ({ page }) => {
		// Test that dismiss handler also validates nonce
		const response = await page.evaluate(async (url) => {
			const result = await fetch(`${url}/wp-admin/admin-ajax.php`, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded',
				},
				body: 'action=wpshadow_dismiss_consent',
			});
			return await result.json();
		}, baseURL);
		
		expect(response.success).toBe(false);
		expect(response.data.message).toContain('Security check failed');
	});

	test('should load WordPress home page', async ({ page }) => {
		// Navigate to WordPress home
		await page.goto(baseURL, { waitUntil: 'load' });
		
		// Verify page loaded
		expect(page.url()).toContain(baseURL);
	});

	test('consent banner AJAX endpoints are registered', async ({ page }) => {
		// Navigate to home page
		await page.goto(baseURL);
		
		// Verify both handlers are registered by checking responses  are not "0"
		const saveResponse = await page.evaluate(async (url) => {
			const result = await fetch(`${url}/wp-admin/admin-ajax.php`, {
				method: 'POST',
				headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
				body: 'action=wpshadow_save_consent',
			});
			const text = await result.text();
			return text !== '0' ? 'Handler registered' : 'Handler not found';
		}, baseURL);
		
		const dismissResponse = await page.evaluate(async (url) => {
			const result = await fetch(`${url}/wp-admin/admin-ajax.php`, {
				method: 'POST',
				headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
				body: 'action=wpshadow_dismiss_consent',
			});
			const text = await result.text();
			return text !== '0' ? 'Handler registered' : 'Handler not found';
		}, baseURL);
		
		expect(saveResponse).toBe('Handler registered');
		expect(dismissResponse).toBe('Handler registered');
		
		console.log('✓ Both consent handlers are properly registered and responding');
	});
});
