/**
 * WPShadow Glossary Tooltip Handler
 *
 * Manages interactive tooltips for glossary terms in content.
 */

(function($) {
	'use strict';

	const GlossaryTooltip = {
		init: function() {
			this.bindEvents();
		},

		bindEvents: function() {
			// Show tooltip on hover
			$(document).on('mouseenter', '.wpshadow-glossary-term', $.proxy(this.showTooltip, this));
			
			// Hide tooltip on mouse leave
			$(document).on('mouseleave', '.wpshadow-glossary-term', $.proxy(this.hideTooltip, this));
			
			// Handle keyboard navigation
			$(document).on('focus', '.wpshadow-glossary-term', $.proxy(this.showTooltip, this));
			$(document).on('blur', '.wpshadow-glossary-term', $.proxy(this.hideTooltip, this));
		},

		showTooltip: function(e) {
			const $term = $(e.currentTarget);
			const excerpt = $term.data('excerpt');
			const url = $term.data('url');
			const term = $term.data('term');

			// Prevent multiple tooltips
			if ($term.find('.wpshadow-glossary-tooltip').length > 0) {
				return;
			}

			const tooltip = $(`
				<div class="wpshadow-glossary-tooltip">
					<div class="wpshadow-glossary-tooltip-content">
						<p>${excerpt}</p>
						<a href="${url}" class="wpshadow-glossary-link">${wpshadowGlossary.viewTerm || 'View Term'}</a>
					</div>
					<div class="wpshadow-glossary-tooltip-arrow"></div>
				</div>
			`);

			$term.append(tooltip);

			// Position tooltip
			this.positionTooltip($term, tooltip);
		},

		hideTooltip: function(e) {
			$(e.currentTarget).find('.wpshadow-glossary-tooltip').remove();
		},

		positionTooltip: function($term, $tooltip) {
			const $window = $(window);
			const termOffset = $term.offset();
			const termWidth = $term.outerWidth();
			const termHeight = $term.outerHeight();
			const tooltipWidth = $tooltip.outerWidth();
			const tooltipHeight = $tooltip.outerHeight();

			let top = termOffset.top - tooltipHeight - 10;
			let left = termOffset.left + (termWidth / 2) - (tooltipWidth / 2);

			// Adjust if tooltip goes off screen
			if (left < 0) {
				left = 10;
			}
			if (left + tooltipWidth > $window.width()) {
				left = $window.width() - tooltipWidth - 10;
			}
			if (top < 0) {
				top = termOffset.top + termHeight + 10;
				$tooltip.addClass('wpshadow-glossary-tooltip-bottom');
			}

			$tooltip.css({
				'top': top + 'px',
				'left': left + 'px'
			});
		}
	};

	// Initialize on document ready
	$(document).ready(function() {
		GlossaryTooltip.init();
	});

})(jQuery);
