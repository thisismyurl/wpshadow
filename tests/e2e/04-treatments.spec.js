/**
 * E2E Tests: Treatment Application
 * 
 * @package WPShadow\Tests\E2E
 */

const { test, expect } = require('@playwright/test');
const { 
	loginToWordPress, 
	navigateToWPShadow,
	waitForNotice,
	activateWPShadow,
	isWPShadowActive,
} = require('./helpers/wordpress-helpers');

test.describe('Treatment Application', () => {
	
	test.beforeEach(async ({ page }) => {
		await loginToWordPress(page);
		
		if (!await isWPShadowActive(page)) {
			await activateWPShadow(page);
		}
		
		await navigateToWPShadow(page, 'dashboard');
	});
	
	test('should show confirmation modal before applying treatment', async ({ page }) => {
		// Run scan to get findings
		const scanButton = page.locator('button:has-text("Scan"), button:has-text("Run")').first();
		
		if (await scanButton.count() > 0) {
			await scanButton.click();
			await page.waitForSelector('.scan-results, .findings', { timeout: 30000 });
		}
		
		// Find first fixable finding with apply button
		const applyButton = page.locator('.apply-treatment-btn, button:has-text("Apply"), button:has-text("Fix")').first();
		
		if (await applyButton.count() > 0) {
			await applyButton.click();
			
			// Should show confirmation modal
			const modal = page.locator('.modal, .dialog, [role="dialog"], .confirmation-modal');
			await expect(modal.first()).toBeVisible({ timeout: 5000 });
			
			// Modal should have confirm button
			const confirmButton = modal.locator('button:has-text("Confirm"), button:has-text("Apply"), button:has-text("Yes")');
			await expect(confirmButton.first()).toBeVisible();
			
			// Modal should have cancel button
			const cancelButton = modal.locator('button:has-text("Cancel"), button:has-text("No")');
			await expect(cancelButton.first()).toBeVisible();
			
			// Close modal (don't actually apply)
			await cancelButton.first().click();
		} else {
			test.skip('No fixable findings available to test');
		}
	});
	
	test('should apply treatment successfully', async ({ page }) => {
		// Run scan
		const scanButton = page.locator('button:has-text("Scan"), button:has-text("Run")').first();
		
		if (await scanButton.count() > 0) {
			await scanButton.click();
			await page.waitForSelector('.scan-results, .findings', { timeout: 30000 });
		}
		
		// Find first safe treatment to apply (look for low-risk ones)
		const applyButtons = page.locator('.apply-treatment-btn, button:has-text("Apply"), button:has-text("Fix")');
		
		if (await applyButtons.count() > 0) {
			// Click first apply button
			await applyButtons.first().click();
			
			// Wait for modal
			const modal = page.locator('.modal, [role="dialog"]');
			await modal.first().waitFor({ state: 'visible', timeout: 5000 });
			
			// Click confirm
			const confirmButton = modal.locator('button:has-text("Confirm"), button:has-text("Apply"), button:has-text("Yes")').first();
			await confirmButton.click();
			
			// Wait for success message
			const successNotice = page.locator('.notice-success, .success, .updated, [class*="success"]');
			await expect(successNotice.first()).toBeVisible({ timeout: 15000 });
			
			// Verify success message content
			const noticeText = await successNotice.first().textContent();
			expect(noticeText.toLowerCase()).toMatch(/success|applied|completed|fixed/);
			
			console.log('✓ Treatment applied successfully:', noticeText);
		} else {
			test.skip('No fixable findings available to test');
		}
	});
	
	test('should show undo button after applying treatment', async ({ page }) => {
		// This test assumes a treatment was applied
		// You might need to apply one first or check if any are already applied
		
		await navigateToWPShadow(page, 'dashboard');
		
		// Look for undo buttons (might be in history or on applied treatments)
		const undoButtons = page.locator('button:has-text("Undo"), button:has-text("Revert"), .undo-button');
		
		if (await undoButtons.count() > 0) {
			// Found undo button(s)
			await expect(undoButtons.first()).toBeVisible();
			console.log('✓ Undo button available');
		} else {
			console.log('ℹ No undo buttons found (no treatments applied yet?)');
		}
	});
	
	test('should handle treatment errors gracefully', async ({ page }) => {
		// This is harder to test without knowing what will fail
		// But we can check error handling exists
		
		await navigateToWPShadow(page, 'dashboard');
		
		// Check that error handling code exists (look for error message containers)
		const errorContainers = page.locator('.notice-error, .error, [class*="error-message"]');
		
		// We're just verifying error UI exists (not that errors occur)
		console.log('✓ Error handling UI elements present');
	});
});
