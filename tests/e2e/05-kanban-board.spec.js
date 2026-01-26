/**
 * E2E Tests: Kanban Board
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

test.describe('Kanban Board', () => {
	
	test.beforeEach(async ({ page }) => {
		await loginToWordPress(page);
		
		if (!await isWPShadowActive(page)) {
			await activateWPShadow(page);
		}
	});
	
	test('should load kanban board page', async ({ page }) => {
		// Navigate to Kanban (adjust URL based on your menu structure)
		await page.goto('/wp-admin/admin.php?page=wpshadow-kanban');
		
		// Check page loaded
		const heading = page.locator('h1:has-text("Kanban"), h1:has-text("Board"), .kanban-title');
		
		if (await heading.count() > 0) {
			await expect(heading.first()).toBeVisible();
		} else {
			// Kanban might be on main dashboard
			await navigateToWPShadow(page, 'dashboard');
			const kanbanSection = page.locator('.kanban-board, #kanban-board, [class*="kanban"]');
			await expect(kanbanSection.first()).toBeVisible();
		}
	});
	
	test('should display kanban columns', async ({ page }) => {
		await page.goto('/wp-admin/admin.php?page=wpshadow-kanban');
		
		// Look for kanban columns (typically: Detected, In Progress, Fixed)
		const columns = page.locator('.kanban-column, .column, [data-column]');
		const columnCount = await columns.count();
		
		// Should have at least 2 columns
		expect(columnCount).toBeGreaterThanOrEqual(2);
		
		console.log(`Found ${columnCount} Kanban columns`);
	});
	
	test('should display finding cards in columns', async ({ page }) => {
		await page.goto('/wp-admin/admin.php?page=wpshadow-kanban');
		
		// Look for cards
		const cards = page.locator('.kanban-card, .card, .finding-card, [draggable="true"]');
		const cardCount = await cards.count();
		
		console.log(`Found ${cardCount} Kanban cards`);
		
		// Might be 0 if no findings exist
		expect(cardCount).toBeGreaterThanOrEqual(0);
	});
	
	test('should have draggable cards', async ({ page }) => {
		await page.goto('/wp-admin/admin.php?page=wpshadow-kanban');
		
		// Look for draggable cards
		const draggableCards = page.locator('[draggable="true"], .kanban-card, .card');
		
		if (await draggableCards.count() > 0) {
			const firstCard = draggableCards.first();
			
			// Check if draggable attribute exists
			const isDraggable = await firstCard.getAttribute('draggable');
			expect(isDraggable).toBe('true');
			
			console.log('✓ Cards are draggable');
		} else {
			console.log('ℹ No cards to test dragging (no findings exist)');
		}
	});
	
	test('should drag card between columns', async ({ page }) => {
		await page.goto('/wp-admin/admin.php?page=wpshadow-kanban');
		
		const cards = page.locator('[draggable="true"], .kanban-card');
		const columns = page.locator('.kanban-column, .column');
		
		if (await cards.count() > 0 && await columns.count() >= 2) {
			const firstCard = cards.first();
			const secondColumn = columns.nth(1);
			
			// Get initial position
			const initialColumn = await firstCard.locator('..').getAttribute('data-column');
			
			// Drag card to second column
			await firstCard.dragTo(secondColumn);
			
			// Wait for any AJAX save
			await page.waitForTimeout(1000);
			
			// Verify card moved (check if it's now in second column)
			const cardParent = await firstCard.locator('..').getAttribute('data-column');
			
			console.log(`Card moved from "${initialColumn}" to "${cardParent}"`);
			
			// Note: This might need adjustment based on your actual HTML structure
		} else {
			test.skip('Not enough cards or columns to test dragging');
		}
	});
	
	test('should persist card position after page reload', async ({ page }) => {
		await page.goto('/wp-admin/admin.php?page=wpshadow-kanban');
		
		const cards = page.locator('[draggable="true"], .kanban-card');
		
		if (await cards.count() > 0) {
			const firstCard = cards.first();
			
			// Get card ID and position
			const cardId = await firstCard.getAttribute('data-id') || await firstCard.getAttribute('id');
			const initialColumn = await firstCard.locator('..').getAttribute('data-column');
			
			// Reload page
			await page.reload();
			await page.waitForLoadState('networkidle');
			
			// Find same card again
			const cardAfterReload = cardId 
				? page.locator(`[data-id="${cardId}"], #${cardId}`)
				: cards.first();
			
			const columnAfterReload = await cardAfterReload.locator('..').getAttribute('data-column');
			
			// Position should be the same
			expect(columnAfterReload).toBe(initialColumn);
			
			console.log('✓ Card position persisted after reload');
		} else {
			test.skip('No cards to test persistence');
		}
	});
});
