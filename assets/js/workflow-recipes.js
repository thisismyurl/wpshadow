/**
 * WPShadow Workflow Recipes
 *
 * UI for executing multi-step workflow recipes.
 * Implements WCAG AA accessibility standards.
 *
 * @package WPShadow
 * @since   1.6030.2200
 */

(function($) {
	'use strict';

	// Recipe execution state
	let activeRecipe = null;
	let recipeState = null;

	/**
	 * Initialize workflow recipes
	 */
	$(function() {
		// Handle recipe start button
		$(document).on('click', '.wpshadow-start-recipe', function(e) {
			e.preventDefault();
			const recipeId = $(this).data('recipe');
			startRecipe(recipeId);
		});

		// Handle step completion
		$(document).on('click', '.wpshadow-complete-step', function(e) {
			e.preventDefault();
			const stepId = $(this).data('step');
			completeStep(stepId);
		});

		// Handle recipe cancellation
		$(document).on('click', '.wpshadow-cancel-recipe', function(e) {
			e.preventDefault();
			window.WPShadowModal.confirm({
				title: 'Cancel Workflow',
				message: 'Are you sure you want to cancel this workflow? Progress will be lost.',
				confirmText: 'Yes, Cancel',
				cancelText: 'Keep Working',
				type: 'warning',
				onConfirm: function() {
					cancelRecipe();
				}
			});
		});

		// Load recipes on page load if on workflows page
		if ($('#wpshadow-recipes-container').length) {
			loadRecipes();
		}

		// Check for active recipe in URL
		const urlParams = new URLSearchParams(window.location.search);
		const recipeParam = urlParams.get('recipe');
		if (recipeParam) {
			startRecipe(recipeParam);
		}
	});

	/**
	 * Load available recipes
	 */
	function loadRecipes() {
		const $container = $('#wpshadow-recipes-container');
		
		$container.html('<div class="wpshadow-loading"><span class="spinner is-active"></span> Loading recipes...</div>');

		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: {
				action: 'wpshadow_get_recipes',
				nonce: $('#wpshadow-recipes-nonce').val()
			},
			success: function(response) {
				if (response.success && response.data.recipes) {
					renderRecipeCards(response.data.recipes);
				} else {
					$container.html('<div class="notice notice-error"><p>Failed to load recipes.</p></div>');
				}
			},
			error: function() {
				$container.html('<div class="notice notice-error"><p>Error loading recipes.</p></div>');
			}
		});
	}

	/**
	 * Render recipe cards
	 */
	function renderRecipeCards(recipes) {
		const $container = $('#wpshadow-recipes-container');
		
		let html = '<div class="wpshadow-recipes-grid">';
		
		Object.keys(recipes).forEach(function(recipeId) {
			const recipe = recipes[recipeId];
			const difficultyClass = 'difficulty-' + recipe.difficulty;
			
			html += `
				<div class="wpshadow-recipe-card ${difficultyClass}">
					<div class="recipe-icon">${recipe.icon}</div>
					<h3 class="recipe-title">${escapeHtml(recipe.title)}</h3>
					<p class="recipe-description">${escapeHtml(recipe.description)}</p>
					<div class="recipe-meta">
						<span class="recipe-time">
							<span class="dashicons dashicons-clock"></span>
							Saves ${recipe.time_saved} min
						</span>
						<span class="recipe-difficulty ${difficultyClass}">
							${capitalizeFirst(recipe.difficulty)}
						</span>
					</div>
					<div class="recipe-steps-preview">
						${recipe.steps.length} steps
					</div>
					<button type="button" 
						class="button button-primary wpshadow-start-recipe" 
						data-recipe="${recipeId}"
						aria-label="Start ${escapeHtml(recipe.title)} workflow">
						Start Workflow
					</button>
				</div>
			`;
		});
		
		html += '</div>';
		
		$container.html(html);
	}

	/**
	 * Start a recipe
	 */
	function startRecipe(recipeId) {
		const nonce = $('#wpshadow-recipes-nonce').val() || 
		             $('.wpshadow-start-recipe').first().data('nonce');

		// Show loading state
		showLoadingOverlay('Starting workflow...');

		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: {
				action: 'wpshadow_execute_recipe',
				nonce: nonce,
				recipe_id: recipeId
			},
			success: function(response) {
				hideLoadingOverlay();
				
				if (response.success && response.data) {
					activeRecipe = response.data.recipe;
					recipeState = response.data.state;
					
					// Render recipe execution UI
					renderRecipeExecution();
				} else {
					window.WPShadowModal.alert({
						title: 'Recipe Start Failed',
						message: 'Failed to start recipe: ' + (response.data.message || 'Unknown error'),
						type: 'danger'
					});
				}
			},
			error: function() {
				hideLoadingOverlay();
				window.WPShadowModal.alert({
					title: 'Error',
					message: 'Error starting recipe. Please try again.',
					type: 'danger'
				});
			}
		});
	}

	/**
	 * Render recipe execution UI
	 */
	function renderRecipeExecution() {
		const $body = $('body');
		
		// Create modal overlay
		const $modal = $('<div>')
			.addClass('wpshadow-recipe-modal')
			.attr({
				'role': 'dialog',
				'aria-modal': 'true',
				'aria-labelledby': 'recipe-title'
			});

		let html = `
			<div class="recipe-modal-content">
				<div class="recipe-modal-header">
					<h2 id="recipe-title">
						${activeRecipe.icon} ${escapeHtml(activeRecipe.title)}
					</h2>
					<button type="button" class="button wpshadow-cancel-recipe" aria-label="Cancel workflow">
						Cancel
					</button>
				</div>
				<div class="recipe-modal-body">
					<div class="recipe-progress-bar">
						<div class="progress-fill" style="width: ${calculateProgress()}%">
							<span class="screen-reader-text">${calculateProgress()}% complete</span>
						</div>
					</div>
					<div class="recipe-steps">
		`;

		activeRecipe.steps.forEach(function(step, index) {
			const isCompleted = recipeState.completed_steps.includes(step.id);
			const isCurrent = index === recipeState.current_step;
			const isPending = index > recipeState.current_step;
			
			let stepClass = 'recipe-step';
			if (isCompleted) stepClass += ' completed';
			if (isCurrent) stepClass += ' current';
			if (isPending) stepClass += ' pending';

			html += `
				<div class="${stepClass}" data-step="${step.id}">
					<div class="step-number">${index + 1}</div>
					<div class="step-content">
						<h3 class="step-title">${escapeHtml(step.title)}</h3>
						<p class="step-description">${escapeHtml(step.description)}</p>
						${isCurrent && !step.automated ? `
							<button type="button" 
								class="button button-primary wpshadow-complete-step" 
								data-step="${step.id}">
								Mark as Complete
							</button>
						` : ''}
						${isCurrent && step.automated ? `
							<div class="step-automated">
								<span class="dashicons dashicons-update spin"></span>
								Executing automatically...
							</div>
						` : ''}
					</div>
					<div class="step-status">
						${isCompleted ? '<span class="dashicons dashicons-yes-alt"></span>' : ''}
						${isPending ? '<span class="dashicons dashicons-minus"></span>' : ''}
					</div>
				</div>
			`;
		});

		html += `
					</div>
				</div>
			</div>
		`;

		$modal.html(html);
		$modal.appendTo($body).fadeIn(200);

		// Focus first interactive element
		$modal.find('button').first().focus();

		// Execute automated steps
		executeAutomatedSteps();
	}

	/**
	 * Calculate progress percentage
	 */
	function calculateProgress() {
		if (!activeRecipe || !recipeState) return 0;
		
		const total = activeRecipe.steps.length;
		const completed = recipeState.completed_steps.length;
		
		return Math.round((completed / total) * 100);
	}

	/**
	 * Execute automated steps
	 */
	function executeAutomatedSteps() {
		if (!activeRecipe || !recipeState) return;

		const currentStep = activeRecipe.steps[recipeState.current_step];
		
		if (currentStep && currentStep.automated) {
			// Simulate automated execution (in reality, this would trigger actual utility)
			setTimeout(function() {
				completeStep(currentStep.id);
			}, 2000); // 2 second delay for demo
		}
	}

	/**
	 * Complete a step
	 */
	function completeStep(stepId) {
		const nonce = $('#wpshadow-recipes-nonce').val();

		// Show loading state on button
		const $button = $(`.wpshadow-complete-step[data-step="${stepId}"]`);
		const originalText = $button.text();
		$button.prop('disabled', true)
			.html('<span class="spinner is-active"></span> Completing...');

		$.ajax({
			url: ajaxurl,
			type: 'POST',
			data: {
				action: 'wpshadow_recipe_step_complete',
				nonce: nonce,
				recipe_id: recipeState.recipe_id,
				step_id: stepId
			},
			success: function(response) {
				if (response.success) {
					// Mark step as completed
					recipeState.completed_steps.push(stepId);
					recipeState.current_step++;

					// Update UI
					const $step = $(`.recipe-step[data-step="${stepId}"]`);
					$step.removeClass('current').addClass('completed');
					$step.find('.step-status').html('<span class="dashicons dashicons-yes-alt"></span>');
					$button.remove();

					// Update progress bar
					$('.progress-fill').css('width', calculateProgress() + '%');

					// Check if recipe is complete
					if (response.data.completed) {
						showRecipeComplete();
					} else {
						// Move to next step
						const nextStepIndex = recipeState.current_step;
						const $nextStep = $('.recipe-step').eq(nextStepIndex);
						$nextStep.addClass('current');

						// Scroll to next step
						$nextStep[0].scrollIntoView({ behavior: 'smooth', block: 'center' });

						// Execute if automated
						executeAutomatedSteps();
					}
				} else {
					$button.prop('disabled', false).text(originalText);
					window.WPShadowModal.alert({
						title: 'Step Failed',
						message: 'Failed to complete step: ' + (response.data.message || 'Unknown error'),
						type: 'danger'
					});
				}
			},
			error: function() {
				$button.prop('disabled', false).text(originalText);
				window.WPShadowModal.alert({
					title: 'Error',
					message: 'Error completing step. Please try again.',
					type: 'danger'
				});
			}
		});
	}

	/**
	 * Show recipe completion
	 */
	function showRecipeComplete() {
		const $modal = $('.wpshadow-recipe-modal');
		
		const completionHtml = `
			<div class="recipe-completion">
				<div class="completion-icon">🎉</div>
				<h2>Workflow Complete!</h2>
				<p>You've successfully completed the <strong>${escapeHtml(activeRecipe.title)}</strong> workflow.</p>
				<p class="time-saved">
					<span class="dashicons dashicons-clock"></span>
					Time saved: <strong>${activeRecipe.time_saved} minutes</strong>
				</p>
				<div class="completion-actions">
					<button type="button" class="button button-primary" onclick="location.reload()">
						Done
					</button>
					<button type="button" class="button" onclick="$('.wpshadow-recipe-modal').fadeOut(200, function() { $(this).remove(); })">
						Close
					</button>
				</div>
			</div>
		`;

		$modal.find('.recipe-modal-body').html(completionHtml);
	}

	/**
	 * Cancel recipe
	 */
	function cancelRecipe() {
		$('.wpshadow-recipe-modal').fadeOut(200, function() {
			$(this).remove();
		});

		activeRecipe = null;
		recipeState = null;
	}

	/**
	 * Show loading overlay
	 */
	function showLoadingOverlay(message) {
		const $overlay = $('<div>')
			.addClass('wpshadow-loading-overlay')
			.html(`
				<div class="loading-content">
					<span class="spinner is-active"></span>
					<p>${message}</p>
				</div>
			`)
			.appendTo('body')
			.fadeIn(200);
	}

	/**
	 * Hide loading overlay
	 */
	function hideLoadingOverlay() {
		$('.wpshadow-loading-overlay').fadeOut(200, function() {
			$(this).remove();
		});
	}

	/**
	 * Escape HTML to prevent XSS
	 */
	function escapeHtml(text) {
		const div = document.createElement('div');
		div.textContent = text;
		return div.innerHTML;
	}

	/**
	 * Capitalize first letter
	 */
	function capitalizeFirst(text) {
		return text.charAt(0).toUpperCase() + text.slice(1);
	}

})(jQuery);
