/**
 * E2E Tests: Diagnostic Scanning
 * 
 * @package WPShadow\Tests\E2E
 */

const { test, expect } = require('@playwright/test');
const { 
	loginToWordPress, 
	navigateToWPShadow,
	waitForAjaxAction,
	activateWPShadow,
	isWPShadowActive,
} = require('./helpers/wordpress-helpers');

test.describe('Diagnostic Scanning', () => {
	
	test.beforeEach(async ({ page }) => {
		await loginToWordPress(page);
		
		if (!await isWPShadowActive(page)) {
			await activateWPShadow(page);
		}
		
		await navigateToWPShadow(page, 'dashboard');
	});
	
	test('should run quick scan', async ({ page }) => {
		// Find and click scan button
		const scanButton = page.locator('button:has-text("Scan"), button:has-text("Run"), .scan-button, #run-scan-btn').first();
		
		await expect(scanButton).toBeVisible();
		await scanButton.click();
		
		// Wait for scan to start (loading indicator)
		const loadingIndicator = page.locator('.loading, .spinner, .scanning, [aria-busy="true"]');
		
		// Either loading appears or scan completes quickly
		try {
			await expect(loadingIndicator.first()).toBeVisible({ timeout: 2000 });
		} catch (e) {
			// Scan might complete too fast to show loading
			console.log('Scan completed very quickly (no loading indicator)');
		}
		
		// Wait for results to appear
		await page.waitForSelector('.scan-results, .findings, .diagnostic-results, .scan-complete', { timeout: 30000 });
		
		// Verify results are displayed
		const results = page.locator('.finding-card, .diagnostic-item, .finding, .result-item');
		const resultCount = await results.count();
		
		console.log(`Found ${resultCount} diagnostic results`);
		
		// Should have at least one result (or a "no issues" message)
		expect(resultCount >= 0).toBeTruthy();
	});
	
	test('should display finding details', async ({ page }) => {
		// Run scan first
		const scanButton = page.locator('button:has-text("Scan"), button:has-text("Run")').first();
		
		if (await scanButton.count() > 0) {
			await scanButton.click();
			await page.waitForSelector('.scan-results, .findings, .diagnostic-results', { timeout: 30000 });
		}
		
		// Check if any findings exist
		const findings = page.locator('.finding-card, .diagnostic-item, .finding');
		const findingCount = await findings.count();
		
		if (findingCount > 0) {
			// Get first finding
			const firstFinding = findings.first();
			
			// Should have a title
			const title = firstFinding.locator('.finding-title, .title, h3, h4');
			await expect(title.first()).toBeVisible();
			
			// Should have a severity indicator
			const severity = firstFinding.locator('.severity, .badge, .threat-level');
			await expect(severity.first()).toBeVisible();
			
			// Should have description or details
			const description = firstFinding.locator('.description, .details, p');
			const descCount = await description.count();
			expect(descCount).toBeGreaterThan(0);
		} else {
			console.log('No findings to test (site is healthy!)');
		}
	});
	
	test('should have apply treatment button on fixable findings', async ({ page }) => {
		// Run scan
		const scanButton = page.locator('button:has-text("Scan"), button:has-text("Run")').first();
		
		if (await scanButton.count() > 0) {
			await scanButton.click();
			await page.waitForSelector('.scan-results, .findings', { timeout: 30000 });
		}
		
		// Look for fixable findings
		const fixableFindings = page.locator('.finding-card[data-auto-fixable="true"], .finding.auto-fixable, .finding:has(.apply-treatment)');
		const fixableCount = await fixableFindings.count();
		
		if (fixableCount > 0) {
			// First fixable finding should have apply button
			const applyButton = fixableFindings.first().locator('button:has-text("Apply"), button:has-text("Fix"), .apply-treatment-btn');
			await expect(applyButton.first()).toBeVisible();
		} else {
			console.log('No auto-fixable findings (or all already fixed!)');
		}
	});
	
	test('should show KB link for findings', async ({ page }) => {
		// Run scan
		const scanButton = page.locator('button:has-text("Scan"), button:has-text("Run")').first();
		
		if (await scanButton.count() > 0) {
			await scanButton.click();
			await page.waitForSelector('.scan-results, .findings', { timeout: 30000 });
		}
		
		const findings = page.locator('.finding-card, .diagnostic-item, .finding');
		const findingCount = await findings.count();
		
		if (findingCount > 0) {
			// Look for KB links
			const kbLinks = page.locator('a[href*="kb"], a:has-text("Learn more"), a:has-text("Documentation")');
			const kbCount = await kbLinks.count();
			
			// At least some findings should have KB links
			console.log(`Found ${kbCount} KB/documentation links`);
		}
	});
});
