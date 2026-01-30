/**
 * WPShadow Feature Tour
 *
 * Interactive guided tours for feature discovery.
 * Implements WCAG AA accessibility standards.
 *
 * @package WPShadow
 * @since   1.2601.2200
 */

(function($) {
	'use strict';

	// Tour state
	let currentTour = null;
	let currentStep = 0;
	let $overlay = null;
	let $spotlight = null;
	let $tooltip = null;

	/**
	 * Initialize feature tour system
	 */
	$(function() {
		// Handle tour start button
		$(document).on('click', '.wpshadow-start-tour', function(e) {
			e.preventDefault();
			const tourId = $(this).data('tour');
			startTour(tourId);
		});

		// Handle tour dismissal
		$(document).on('click', '.wpshadow-dismiss-tour', function(e) {
			e.preventDefault();
			const tourId = $(this).data('tour');
			dismissTour(tourId);
		});

		// Keyboard navigation
		$(document).on('keydown', function(e) {
			if (!currentTour) return;

			switch(e.key) {
				case 'Escape':
					endTour();
					break;
				case 'ArrowRight':
					nextStep();
					break;
				case 'ArrowLeft':
					previousStep();
					break;
			}
		});

		// Handle clicks on tour controls
		$(document).on('click', '.wpshadow-tour-next', nextStep);
		$(document).on('click', '.wpshadow-tour-prev', previousStep);
		$(document).on('click', '.wpshadow-tour-skip', endTour);
		$(document).on('click', '.wpshadow-tour-complete', endTour);
	});

	/**
	 * Start a tour
	 */
	function startTour(tourId) {
		if (!wpShadowTour || !wpShadowTour.tours[tourId]) {
			console.error('Tour not found:', tourId);
			return;
		}

		currentTour = wpShadowTour.tours[tourId];
		currentStep = 0;

		// Notify backend
		$.ajax({
			url: wpShadowTour.ajaxUrl,
			type: 'POST',
			data: {
				action: 'wpshadow_start_tour',
				nonce: wpShadowTour.nonce,
				tour_id: tourId
			},
			success: function(response) {
				if (response.success) {
					// Hide the tour prompt
					$('.wpshadow-tour-prompt').fadeOut();
					
					// Create tour overlay
					createTourOverlay();
					
					// Show first step
					showStep(0);
				}
			}
		});
	}

	/**
	 * Create tour overlay elements
	 */
	function createTourOverlay() {
		// Create overlay
		$overlay = $('<div>')
			.addClass('wpshadow-tour-overlay')
			.attr({
				'role': 'dialog',
				'aria-modal': 'true',
				'aria-labelledby': 'wpshadow-tour-title'
			})
			.appendTo('body');

		// Create spotlight (highlights target element)
		$spotlight = $('<div>')
			.addClass('wpshadow-tour-spotlight')
			.appendTo('body');

		// Create tooltip
		$tooltip = $('<div>')
			.addClass('wpshadow-tour-tooltip')
			.attr('role', 'document')
			.appendTo('body');

		// Trap focus in tooltip
		trapFocus($tooltip);
	}

	/**
	 * Show a specific step
	 */
	function showStep(stepIndex) {
		if (!currentTour || !currentTour.steps[stepIndex]) {
			return;
		}

		currentStep = stepIndex;
		const step = currentTour.steps[stepIndex];
		const isFirstStep = stepIndex === 0;
		const isLastStep = stepIndex === currentTour.steps.length - 1;

		// Position spotlight on target element
		if (step.target && step.target !== 'body') {
			const $target = $(step.target);
			if ($target.length) {
				positionSpotlight($target);
				
				// Scroll target into view
				$target[0].scrollIntoView({
					behavior: 'smooth',
					block: 'center'
				});
			} else {
				// Target not found, center the tooltip
				$spotlight.hide();
			}
		} else {
			// Center tooltip for general steps
			$spotlight.hide();
		}

		// Build tooltip content
		const tooltipContent = `
			<div class="wpshadow-tour-header">
				<h2 id="wpshadow-tour-title" class="wpshadow-tour-title">
					${escapeHtml(step.title)}
				</h2>
				<button type="button" class="wpshadow-tour-close" aria-label="Close tour">
					<span class="dashicons dashicons-no"></span>
				</button>
			</div>
			<div class="wpshadow-tour-body">
				<p>${escapeHtml(step.content)}</p>
				${step.action_url ? `
					<a href="${escapeHtml(step.action_url)}" class="button button-primary wpshadow-tour-action">
						${escapeHtml(step.action_text || 'Continue')}
					</a>
				` : ''}
			</div>
			<div class="wpshadow-tour-footer">
				<div class="wpshadow-tour-progress">
					<span class="screen-reader-text">Step ${stepIndex + 1} of ${currentTour.steps.length}</span>
					<span aria-hidden="true">${stepIndex + 1} / ${currentTour.steps.length}</span>
				</div>
				<div class="wpshadow-tour-controls">
					${!isFirstStep ? '<button type="button" class="button wpshadow-tour-prev">Previous</button>' : ''}
					${!isLastStep ? '<button type="button" class="button button-primary wpshadow-tour-next">Next</button>' : ''}
					${isLastStep ? '<button type="button" class="button button-primary wpshadow-tour-complete">Finish Tour</button>' : ''}
					<button type="button" class="button wpshadow-tour-skip">Skip Tour</button>
				</div>
			</div>
		`;

		$tooltip.html(tooltipContent);

		// Position tooltip based on placement
		positionTooltip(step.target, step.placement);

		// Show overlay and tooltip
		$overlay.fadeIn(200);
		$tooltip.fadeIn(200, function() {
			// Focus first interactive element
			$tooltip.find('a, button').first().focus();
		});

		// Handle close button
		$tooltip.find('.wpshadow-tour-close').on('click', endTour);
	}

	/**
	 * Position spotlight on target element
	 */
	function positionSpotlight($target) {
		const offset = $target.offset();
		const width = $target.outerWidth();
		const height = $target.outerHeight();
		const padding = 10;

		$spotlight.css({
			top: offset.top - padding,
			left: offset.left - padding,
			width: width + (padding * 2),
			height: height + (padding * 2)
		}).show();
	}

	/**
	 * Position tooltip relative to target
	 */
	function positionTooltip(target, placement) {
		if (!target || target === 'body' || !$(target).length) {
			// Center tooltip
			$tooltip.css({
				position: 'fixed',
				top: '50%',
				left: '50%',
				transform: 'translate(-50%, -50%)'
			});
			return;
		}

		const $target = $(target);
		const offset = $target.offset();
		const targetWidth = $target.outerWidth();
		const targetHeight = $target.outerHeight();
		const tooltipWidth = 400; // Fixed width
		const tooltipHeight = $tooltip.outerHeight() || 300;
		const spacing = 20;

		let top, left;

		switch(placement) {
			case 'right':
				top = offset.top;
				left = offset.left + targetWidth + spacing;
				break;
			case 'left':
				top = offset.top;
				left = offset.left - tooltipWidth - spacing;
				break;
			case 'top':
				top = offset.top - tooltipHeight - spacing;
				left = offset.left + (targetWidth / 2) - (tooltipWidth / 2);
				break;
			case 'bottom':
			default:
				top = offset.top + targetHeight + spacing;
				left = offset.left + (targetWidth / 2) - (tooltipWidth / 2);
				break;
		}

		// Keep tooltip within viewport
		const $window = $(window);
		const scrollTop = $window.scrollTop();
		const scrollLeft = $window.scrollLeft();
		const viewportWidth = $window.width();
		const viewportHeight = $window.height();

		if (left < scrollLeft) left = scrollLeft + 10;
		if (left + tooltipWidth > scrollLeft + viewportWidth) {
			left = scrollLeft + viewportWidth - tooltipWidth - 10;
		}
		if (top < scrollTop) top = scrollTop + 10;
		if (top + tooltipHeight > scrollTop + viewportHeight) {
			top = scrollTop + viewportHeight - tooltipHeight - 10;
		}

		$tooltip.css({
			position: 'absolute',
			top: top,
			left: left,
			width: tooltipWidth
		});
	}

	/**
	 * Go to next step
	 */
	function nextStep() {
		if (!currentTour) return;

		const nextIndex = currentStep + 1;
		if (nextIndex < currentTour.steps.length) {
			completeStep(currentStep);
			showStep(nextIndex);
		} else {
			endTour();
		}
	}

	/**
	 * Go to previous step
	 */
	function previousStep() {
		if (!currentTour || currentStep === 0) return;

		showStep(currentStep - 1);
	}

	/**
	 * Complete current step
	 */
	function completeStep(stepIndex) {
		const step = currentTour.steps[stepIndex];

		$.ajax({
			url: wpShadowTour.ajaxUrl,
			type: 'POST',
			data: {
				action: 'wpshadow_complete_tour_step',
				nonce: wpShadowTour.nonce,
				tour_id: currentTour.id || 'killer-utilities',
				step_id: step.id
			}
		});
	}

	/**
	 * End the tour
	 */
	function endTour() {
		// Fade out and remove elements
		if ($overlay) {
			$overlay.fadeOut(200, function() {
				$(this).remove();
			});
		}
		if ($spotlight) {
			$spotlight.fadeOut(200, function() {
				$(this).remove();
			});
		}
		if ($tooltip) {
			$tooltip.fadeOut(200, function() {
				$(this).remove();
			});
		}

		// Reset state
		currentTour = null;
		currentStep = 0;
		$overlay = null;
		$spotlight = null;
		$tooltip = null;
	}

	/**
	 * Dismiss tour (don't show again)
	 */
	function dismissTour(tourId) {
		$.ajax({
			url: wpShadowTour.ajaxUrl,
			type: 'POST',
			data: {
				action: 'wpshadow_dismiss_tour',
				nonce: wpShadowTour.nonce,
				tour_id: tourId
			},
			success: function(response) {
				if (response.success) {
					$('.wpshadow-tour-prompt[data-tour="' + tourId + '"]').fadeOut();
				}
			}
		});
	}

	/**
	 * Trap focus within element (accessibility)
	 */
	function trapFocus($element) {
		$element.on('keydown', function(e) {
			if (e.key !== 'Tab') return;

			const focusableElements = $element.find('a, button, input, select, textarea, [tabindex]:not([tabindex="-1"])');
			const firstElement = focusableElements.first()[0];
			const lastElement = focusableElements.last()[0];

			if (e.shiftKey) {
				// Shift + Tab
				if (document.activeElement === firstElement) {
					e.preventDefault();
					lastElement.focus();
				}
			} else {
				// Tab
				if (document.activeElement === lastElement) {
					e.preventDefault();
					firstElement.focus();
				}
			}
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

})(jQuery);
