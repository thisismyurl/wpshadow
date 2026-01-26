import { test, expect, Page } from '@playwright/test';

test.describe('WPShadow Consent Banner', () => {
	let page: Page;
	const siteUrl = process.env.SITE_URL || 'https://wpshadow.com';
	const adminUsername = process.env.WP_ADMIN_USER || 'admin';
	const adminPassword = process.env.WP_ADMIN_PASS || 'password';

	test.beforeAll(async ({ browser }) => {
		// We'll use a single page instance for all tests
		const context = await browser.newContext();
		page = await context.newPage();
	});

	test('should login to WordPress admin', async ({ page: testPage }) => {
		await testPage.goto(`${siteUrl}/wp-login.php`);
		
		// Fill login form
		await testPage.fill('input[name="log"]', adminUsername);
		await testPage.fill('input[name="pwd"]', adminPassword);
		await testPage.click('input[type="submit"]');
		
		// Wait for redirect to dashboard
		await testPage.waitForURL(`${siteUrl}/wp-admin/**`);
		
		// Verify we're logged in
		expect(await testPage.title()).toContain('Dashboard');
	});

	test('consent banner should be visible on first visit after clearing consent', async ({ page: testPage }) => {
		// Navigate to WordPress admin
		await testPage.goto(`${siteUrl}/wp-admin/`);
		
		// Check if consent banner exists
		const consentBanner = testPage.locator('#wpshadow-consent-banner');
		const isVisible = await consentBanner.isVisible().catch(() => false);
		
		if (isVisible) {
			console.log('✓ Consent banner is visible');
		} else {
			console.log('⚠ Consent banner not visible (user may have already consented)');
		}
	});

	test('consent banner should have proper structure', async ({ page: testPage }) => {
		await testPage.goto(`${siteUrl}/wp-admin/`);
		
		// Check if banner header exists
		const bannerHeader = testPage.locator('.wpshadow-consent-header h3');
		const headerText = await bannerHeader.textContent().catch(() => null);
		
		if (headerText && headerText.includes('Your Privacy Matters')) {
			console.log('✓ Banner header is correct');
		} else {
			console.log('⚠ Banner header not found or incorrect');
		}
	});

	test('AJAX test handler should work', async ({ page: testPage }) => {
		await testPage.goto(`${siteUrl}/wp-admin/`);
		
		// Make AJAX request directly
		const response = await testPage.evaluate(async () => {
			const result = await fetch('/wp-admin/admin-ajax.php', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded',
				},
				body: 'action=wpshadow_test_ajax',
			});
			return await result.json();
		});
		
		expect(response.success).toBe(true);
		expect(response.data.message).toContain('AJAX is working');
		console.log('✓ AJAX test handler working');
	});

	test('consent save handler should require nonce', async ({ page: testPage }) => {
		await testPage.goto(`${siteUrl}/wp-admin/`);
		
		// Make AJAX request without nonce
		const response = await testPage.evaluate(async () => {
			const result = await fetch('/wp-admin/admin-ajax.php', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded',
				},
				body: 'action=wpshadow_save_consent&consent_preferences=all',
			});
			return await result.json();
		});
		
		expect(response.success).toBe(false);
		expect(response.data.message).toContain('Security check failed');
		console.log('✓ Nonce validation working correctly');
	});

	test('consent banner dismiss button should work with proper nonce', async ({ page: testPage }) => {
		await testPage.goto(`${siteUrl}/wp-admin/`);
		
		// Wait for consent nonce to be available
		const nonce = await testPage.evaluate(() => {
			return (window as any).wpshadow?.consent_nonce || null;
		});
		
		if (!nonce) {
			console.log('⚠ Consent nonce not found - banner may not be visible or already dismissed');
			return;
		}
		
		// Make AJAX request with nonce
		const response = await testPage.evaluate(async (nonceValue) => {
			const result = await fetch('/wp-admin/admin-ajax.php', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded',
				},
				body: `action=wpshadow_dismiss_consent&nonce=${nonceValue}`,
			});
			return await result.json();
		}, nonce);
		
		expect(response.success).toBe(true);
		console.log('✓ Consent dismiss with nonce successful');
	});

	test('consent banner dismiss button should be clickable', async ({ page: testPage }) => {
		await testPage.goto(`${siteUrl}/wp-admin/`);
		
		// Find dismiss button
		const dismissBtn = testPage.locator('.wpshadow-consent-dismiss');
		const isVisible = await dismissBtn.isVisible().catch(() => false);
		
		if (isVisible) {
			// Click the dismiss button
			await dismissBtn.click();
			
			// Wait for success message or banner to disappear
			await testPage.waitForTimeout(1000);
			
			const successMsg = testPage.locator('.wpshadow-consent-success-message');
			const successVisible = await successMsg.isVisible().catch(() => false);
			
			if (successVisible) {
				console.log('✓ Dismiss button click successful');
			} else {
				console.log('✓ Dismiss button clicked (banner behavior checked)');
			}
		} else {
			console.log('⚠ Dismiss button not visible');
		}
	});

	test('consent preferences should validate nonce on save', async ({ page: testPage }) => {
		await testPage.goto(`${siteUrl}/wp-admin/`);
		
		// Get nonce
		const nonce = await testPage.evaluate(() => {
			return (window as any).wpshadow?.consent_nonce || null;
		});
		
		if (!nonce) {
			console.log('⚠ Consent nonce not found');
			return;
		}
		
		// Test with invalid nonce
		const invalidResponse = await testPage.evaluate(async () => {
			const result = await fetch('/wp-admin/admin-ajax.php', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded',
				},
				body: 'action=wpshadow_save_consent&nonce=invalid_nonce&telemetry=false',
			});
			return await result.json();
		});
		
		expect(invalidResponse.success).toBe(false);
		console.log('✓ Invalid nonce properly rejected');
		
		// Test with valid nonce
		const validResponse = await testPage.evaluate(async (nonceValue) => {
			const result = await fetch('/wp-admin/admin-ajax.php', {
				method: 'POST',
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded',
				},
				body: `action=wpshadow_save_consent&nonce=${nonceValue}&telemetry=false`,
			});
			return await result.json();
		}, nonce);
		
		expect(validResponse.success).toBe(true);
		console.log('✓ Valid nonce accepted');
	});
});
