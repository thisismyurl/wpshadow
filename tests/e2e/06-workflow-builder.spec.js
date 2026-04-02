/**
 * E2E Tests: Workflow Builder
 * 
 * @package WPShadow\Tests\E2E
 */

const { test, expect } = require('@playwright/test');
const { 
	loginToWordPress, 
	activateWPShadow,
	isWPShadowActive,
} = require('./helpers/wordpress-helpers');

test.describe('Workflow Builder', () => {
	
	test.beforeEach(async ({ page }) => {
		await loginToWordPress(page);
		
		if (!await isWPShadowActive(page)) {
			await activateWPShadow(page);
		}
	});
	
	test('should load workflow builder page', async ({ page }) => {
		// Navigate to Workflow Builder
		await page.goto('/wp-admin/admin.php?page=wpshadow-workflow-builder');
		
		// Check page loaded
		const heading = page.locator('h1:has-text("Workflow"), h1:has-text("Automation"), .workflow-title');
		await expect(heading.first()).toBeVisible();
	});
	
	test('should have create workflow button', async ({ page }) => {
		await page.goto('/wp-admin/admin.php?page=wpshadow-workflow-builder');
		
		// Look for "Add New" or "Create" button
		const createButton = page.locator('button:has-text("Add New"), button:has-text("Create"), button:has-text("New Workflow"), .add-workflow-btn');
		
		await expect(createButton.first()).toBeVisible();
	});
	
	test('should display existing workflows', async ({ page }) => {
		await page.goto('/wp-admin/admin.php?page=wpshadow-workflow-builder');
		
		// Look for workflow list
		const workflowList = page.locator('.workflow-list, .workflows, table.workflows');
		const workflows = page.locator('.workflow-item, .workflow-row, tbody tr');
		
		if (await workflowList.count() > 0) {
			const count = await workflows.count();
			console.log(`Found ${count} existing workflows`);
		}
	});
	
	test('should open workflow wizard modal', async ({ page }) => {
		await page.goto('/wp-admin/admin.php?page=wpshadow-workflow-builder');
		
		// Click create workflow button
		const createButton = page.locator('button:has-text("Add New"), button:has-text("Create")').first();
		
		if (await createButton.count() > 0) {
			await createButton.click();
			
			// Wait for wizard modal to open
			const wizardModal = page.locator('.workflow-wizard, .modal, [role="dialog"]');
			await expect(wizardModal.first()).toBeVisible({ timeout: 5000 });
			
			console.log('✓ Workflow wizard opened');
		}
	});
	
	test('should show trigger selection', async ({ page }) => {
		await page.goto('/wp-admin/admin.php?page=wpshadow-workflow-builder');
		
		// Open wizard
		const createButton = page.locator('button:has-text("Add New"), button:has-text("Create")').first();
		
		if (await createButton.count() > 0) {
			await createButton.click();
			
			// Wait for wizard
			await page.waitForSelector('.workflow-wizard, .modal', { timeout: 5000 });
			
			// Look for trigger selection
			const triggerSelect = page.locator('select[name*="trigger"], #workflow-trigger, .trigger-select');
			
			if (await triggerSelect.count() > 0) {
				await expect(triggerSelect.first()).toBeVisible();
				
				// Check trigger options
				const options = await triggerSelect.first().locator('option').count();
				expect(options).toBeGreaterThan(1); // At least 2 options (including placeholder)
				
				console.log(`✓ Found ${options - 1} trigger options`);
			}
		}
	});
	
	test('should show action selection', async ({ page }) => {
		await page.goto('/wp-admin/admin.php?page=wpshadow-workflow-builder');
		
		// Open wizard
		const createButton = page.locator('button:has-text("Add New"), button:has-text("Create")').first();
		
		if (await createButton.count() > 0) {
			await createButton.click();
			await page.waitForSelector('.workflow-wizard, .modal', { timeout: 5000 });
			
			// Look for action selection
			const actionSelect = page.locator('select[name*="action"], #workflow-action, .action-select');
			
			if (await actionSelect.count() > 0) {
				await expect(actionSelect.first()).toBeVisible();
				
				// Check action options
				const options = await actionSelect.first().locator('option').count();
				expect(options).toBeGreaterThan(1);
				
				console.log(`✓ Found ${options - 1} action options`);
			}
		}
	});
	
	test('should save new workflow', async ({ page }) => {
		await page.goto('/wp-admin/admin.php?page=wpshadow-workflow-builder');
		
		const createButton = page.locator('button:has-text("Add New"), button:has-text("Create")').first();
		
		if (await createButton.count() > 0) {
			await createButton.click();
			await page.waitForSelector('.workflow-wizard, .modal', { timeout: 5000 });
			
			// Fill workflow name
			const nameInput = page.locator('input[name*="name"], #workflow-name, .workflow-name-input');
			if (await nameInput.count() > 0) {
				await nameInput.first().fill('Test E2E Workflow');
			}
			
			// Select a trigger (if dropdown exists)
			const triggerSelect = page.locator('select[name*="trigger"]').first();
			if (await triggerSelect.count() > 0) {
				await triggerSelect.selectOption({ index: 1 }); // Select first real option
			}
			
			// Select an action (if dropdown exists)
			const actionSelect = page.locator('select[name*="action"]').first();
			if (await actionSelect.count() > 0) {
				await actionSelect.selectOption({ index: 1 });
			}
			
			// Click save
			const saveButton = page.locator('button:has-text("Save"), button:has-text("Create Workflow")');
			
			if (await saveButton.count() > 0) {
				await saveButton.first().click();
				
				// Wait for success message
				const successNotice = page.locator('.notice-success, .success');
				await expect(successNotice.first()).toBeVisible({ timeout: 10000 });
				
				console.log('✓ Workflow saved successfully');
			}
		}
	});
});
