/**
 * WPShadow Modern Form Controls - Interactive Components
 * Version: 1.0 (2026 Design Update)
 *
 * Handles toggle switches, sliders, dropdowns, and button groups
 * Provides keyboard navigation and screen reader support
 *
 * Philosophy Alignment:
 * - Commandment #8: Inspire Confidence - Smooth interactions
 * - CANON Accessibility: Keyboard + screen reader accessible
 *
 * @package WPShadow
 */

(function($) {
	'use strict';

	/**
	 * Initialize when document is ready
	 */
	$(document).ready(function() {
		WPShadowFormControls.init();
	});

	/**
	 * WPShadow Form Controls Object
	 */
	window.WPShadowFormControls = {

		// Cache computed styles for performance
		_cachedColors: null,

		/**
		 * Get design system colors (cached)
		 */
		_getColors: function() {
			if (!this._cachedColors) {
				const root = document.documentElement;
				const styles = getComputedStyle(root);
				this._cachedColors = {
					progress: styles.getPropertyValue('--wps-info').trim() || '#3b82f6',
					track: styles.getPropertyValue('--wps-gray-200').trim() || '#e5e7eb'
				};
			}
			return this._cachedColors;
		},

		/**
		 * Initialize all form controls
		 */
		init: function() {
			this.initToggles();
			this.initSliders();
			this.initDropdowns();
			this.initButtonGroups();
			this.initTextareas();
		},

		/**
		 * Initialize Toggle Switches
		 */
		initToggles: function() {
			const self = this;

			// Handle toggle click
			$(document).on('click', '.wps-toggle', function(e) {
				const $toggle = $(this);
				const hasNativeCheckbox = $toggle.is('label') && $toggle.find('input[type="checkbox"]').length;
				if (hasNativeCheckbox) {
					return;
				}

				if (!$toggle.is('[role="switch"]')) {
					return;
				}

				e.preventDefault();

				if ($toggle.is(':disabled')) {
					return;
				}

				const isChecked = $toggle.attr('aria-checked') === 'true';
				const newState = !isChecked;

				// Update ARIA attribute
				$toggle.attr('aria-checked', newState);

				// Update hidden input if exists
				const settingName = $toggle.data('setting');
				if (settingName) {
					const $hiddenInput = $(`input[name="${settingName}"]`);
					if ($hiddenInput.length) {
						$hiddenInput.val(newState ? '1' : '0').trigger('change');
					}
				}

				// Trigger custom event
				$toggle.trigger('wps:toggle:change', [newState]);

				// Screen reader announcement
				self.announceToScreenReader(
					newState
						? $toggle.data('label-on') || 'On'
						: $toggle.data('label-off') || 'Off'
				);
			});

			// Keyboard support (Space/Enter)
			$(document).on('keydown', '.wps-toggle', function(e) {
				if (!$(this).is('[role="switch"]')) {
					return;
				}
				if (e.key === ' ' || e.key === 'Enter') {
					e.preventDefault();
					$(this).click();
				}
			});
		},

		/**
		 * Initialize Sliders with Value Display
		 */
		initSliders: function() {
			const self = this;

			// Update value display on input
			$(document).on('input', '.wps-slider', function() {
				const $slider = $(this);
				const value = $slider.val();
				const $display = $(`#${$slider.attr('id')}-display`);

				if ($display.length) {
					$display.text(value);
				}

				// Update aria-valuenow
				$slider.attr('aria-valuenow', value);

				// Update aria-valuetext with unit if specified
				const unit = $slider.data('unit') || '';
				$slider.attr('aria-valuetext', `${value} ${unit}`.trim());

				// Update progress fill (visual effect)
				self.updateSliderProgress($slider);
			});

			// Initialize progress fill on page load
			$('.wps-slider').each(function() {
				self.updateSliderProgress($(this));

				// Set initial display value
				const value = $(this).val();
				const $display = $(`#${$(this).attr('id')}-display`);
				if ($display.length) {
					$display.text(value);
				}
			});

			// Keyboard shortcuts for larger steps
			$(document).on('keydown', '.wps-slider', function(e) {
				const $slider = $(this);
				const step = parseFloat($slider.attr('step')) || 1;
				const largeStep = step * 10;

				if (e.key === 'PageUp') {
					e.preventDefault();
					const newVal = Math.min(
						parseFloat($slider.attr('max')),
						parseFloat($slider.val()) + largeStep
					);
					$slider.val(newVal).trigger('input');
				} else if (e.key === 'PageDown') {
					e.preventDefault();
					const newVal = Math.max(
						parseFloat($slider.attr('min')),
						parseFloat($slider.val()) - largeStep
					);
					$slider.val(newVal).trigger('input');
				}
			});
		},

		/**
		 * Update slider progress fill effect
		 */
		updateSliderProgress: function($slider) {
			const min = parseFloat($slider.attr('min')) || 0;
			const max = parseFloat($slider.attr('max')) || 100;
			const value = parseFloat($slider.val());
			const percentage = ((value - min) / (max - min)) * 100;

			// Get cached colors
			const colors = this._getColors();

			$slider.css('background',
				`linear-gradient(to right, ${colors.progress} 0%, ${colors.progress} ${percentage}%, ${colors.track} ${percentage}%, ${colors.track} 100%)`
			);
		},

		/**
		 * Initialize Styled Dropdowns
		 */
		initDropdowns: function() {
			const self = this;

			// Toggle dropdown open/close
			$(document).on('click', '.wps-dropdown:not([aria-disabled="true"])', function(e) {
				const $dropdown = $(this);
				const isOpen = $dropdown.attr('aria-expanded') === 'true';

				// Close all other dropdowns first
				$('.wps-dropdown[aria-expanded="true"]').not($dropdown).each(function() {
					$(this).attr('aria-expanded', 'false');
				});

				// Toggle this dropdown
				$dropdown.attr('aria-expanded', !isOpen);

				if (!isOpen) {
					// Focus first option when opening
					setTimeout(() => {
						$dropdown.find('.wps-dropdown-list li:first').focus();
					}, 50);
				}

				e.stopPropagation();
			});

			// Select option
			$(document).on('click', '.wps-dropdown-list li', function(e) {
				e.stopPropagation();

				const $option = $(this);
				const $dropdown = $option.closest('.wps-dropdown');
				const value = $option.data('value');
				const text = $option.text();

				// Update selected text
				$dropdown.find('.wps-dropdown-text')
					.text(text)
					.removeClass('placeholder');

				// Update hidden input
				const $hiddenInput = $dropdown.siblings('input[type="hidden"]');
				if ($hiddenInput.length) {
					$hiddenInput.val(value).trigger('change');
				}

				// Update aria-selected
				$dropdown.find('.wps-dropdown-list li').attr('aria-selected', 'false');
				$option.attr('aria-selected', 'true');

				// Close dropdown
				$dropdown.attr('aria-expanded', 'false');
				$dropdown.focus();

				// Trigger custom event
				$dropdown.trigger('wps:dropdown:change', [value, text]);

				// Screen reader announcement
				self.announceToScreenReader(`Selected: ${text}`);
			});

			// Close dropdown when clicking outside
			$(document).on('click', function(e) {
				if (!$(e.target).closest('.wps-dropdown').length) {
					$('.wps-dropdown[aria-expanded="true"]').attr('aria-expanded', 'false');
				}
			});

			// Keyboard navigation
			$(document).on('keydown', '.wps-dropdown', function(e) {
				const $dropdown = $(this);
				const isOpen = $dropdown.attr('aria-expanded') === 'true';

				if (e.key === 'Escape') {
					$dropdown.attr('aria-expanded', 'false');
					$dropdown.focus();
				} else if ((e.key === 'Enter' || e.key === ' ') && !isOpen) {
					e.preventDefault();
					$dropdown.click();
				} else if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
					e.preventDefault();
					if (!isOpen) {
						$dropdown.click();
					}
				}
			});

			// Navigate options with arrow keys
			$(document).on('keydown', '.wps-dropdown-list li', function(e) {
				const $option = $(this);
				const $dropdown = $option.closest('.wps-dropdown');

				if (e.key === 'ArrowDown') {
					e.preventDefault();
					const $next = $option.next('li');
					if ($next.length) {
						$next.focus();
					}
				} else if (e.key === 'ArrowUp') {
					e.preventDefault();
					const $prev = $option.prev('li');
					if ($prev.length) {
						$prev.focus();
					}
				} else if (e.key === 'Enter' || e.key === ' ') {
					e.preventDefault();
					$option.click();
				} else if (e.key === 'Escape') {
					e.preventDefault();
					$dropdown.attr('aria-expanded', 'false');
					$dropdown.focus();
				}
			});
		},

		/**
		 * Initialize Button Groups (Radio Button Replacement)
		 */
		initButtonGroups: function() {
			const self = this;

			// Handle button group click
			$(document).on('click', '.wps-btn-group-item', function(e) {
				e.preventDefault();

				if ($(this).is(':disabled')) {
					return;
				}

				const $button = $(this);
				const $group = $button.closest('.wps-button-group');
				const value = $button.data('value');

				// Update aria-checked for all buttons in group
				$group.find('.wps-btn-group-item').attr('aria-checked', 'false');
				$button.attr('aria-checked', 'true');

				// Update hidden input if exists
				const $hiddenInput = $group.siblings('input[type="hidden"]');
				if ($hiddenInput.length) {
					$hiddenInput.val(value).trigger('change');
				}

				// Trigger custom event
				$group.trigger('wps:buttongroup:change', [value, $button.text().trim()]);

				// Screen reader announcement
				self.announceToScreenReader(`Selected: ${$button.text().trim()}`);
			});

			// Keyboard navigation with arrow keys
			$(document).on('keydown', '.wps-btn-group-item', function(e) {
				const $button = $(this);
				const $group = $button.closest('.wps-button-group');
				const $buttons = $group.find('.wps-btn-group-item');
				const currentIndex = $buttons.index($button);

				if (e.key === 'ArrowRight' || e.key === 'ArrowDown') {
					e.preventDefault();
					const nextIndex = (currentIndex + 1) % $buttons.length;
					$buttons.eq(nextIndex).focus();
				} else if (e.key === 'ArrowLeft' || e.key === 'ArrowUp') {
					e.preventDefault();
					const prevIndex = (currentIndex - 1 + $buttons.length) % $buttons.length;
					$buttons.eq(prevIndex).focus();
				} else if (e.key === ' ' || e.key === 'Enter') {
					e.preventDefault();
					$button.click();
				}
			});
		},

		/**
		 * Initialize Textareas with Character Counter
		 */
		initTextareas: function() {
			// Update character count on input
			$(document).on('input', '.wps-textarea', function() {
				const $textarea = $(this);
				const maxLength = $textarea.attr('maxlength');

				if (maxLength) {
					const currentLength = $textarea.val().length;
					const $counter = $textarea.closest('.wps-form-field')
						.find('.wps-char-count #char-current');

					if ($counter.length) {
						$counter.text(currentLength);

						// Warning color if near limit
						const $charCount = $counter.closest('.wps-char-count');
						if (currentLength > maxLength * 0.9) {
							$charCount.css('color', 'var(--wps-warning)');
						} else {
							$charCount.css('color', '');
						}
					}
				}
			});

			// Initialize counters
			$('.wps-textarea').each(function() {
				$(this).trigger('input');
			});
		},

		/**
		 * Announce message to screen readers
		 */
		announceToScreenReader: function(message) {
			// Create or use existing live region
			let $liveRegion = $('#wps-live-region');

			if (!$liveRegion.length) {
				$liveRegion = $('<div>', {
					id: 'wps-live-region',
					'aria-live': 'polite',
					'aria-atomic': 'true',
					class: 'screen-reader-text'
				}).appendTo('body');
			}

			// Clear and set new message
			$liveRegion.text('');
			setTimeout(() => {
				$liveRegion.text(message);
			}, 100);
		},

		/**
		 * Sync form control state from hidden input
		 * Useful when form is populated programmatically
		 */
		syncFromHiddenInput: function($input) {
			const name = $input.attr('name');
			const value = $input.val();

			// Find corresponding control
			const $toggle = $(`.wps-toggle[data-setting="${name}"]`);
			if ($toggle.length) {
				$toggle.attr('aria-checked', value === '1' ? 'true' : 'false');
			}

			const $dropdown = $input.siblings('.wps-dropdown');
			if ($dropdown.length) {
				const $option = $dropdown.find(`.wps-dropdown-list li[data-value="${value}"]`);
				if ($option.length) {
					$dropdown.find('.wps-dropdown-text').text($option.text());
					$dropdown.find('.wps-dropdown-list li').attr('aria-selected', 'false');
					$option.attr('aria-selected', 'true');
				}
			}

			const $buttonGroup = $input.siblings('.wps-button-group');
			if ($buttonGroup.length) {
				$buttonGroup.find('.wps-btn-group-item').attr('aria-checked', 'false');
				$buttonGroup.find(`.wps-btn-group-item[data-value="${value}"]`).attr('aria-checked', 'true');
			}
		},

		/**
		 * Get toggle state
		 */
		getToggleState: function(toggleId) {
			const $toggle = $(`#${toggleId}`);
			return $toggle.attr('aria-checked') === 'true';
		},

		/**
		 * Set toggle state
		 */
		setToggleState: function(toggleId, state) {
			const $toggle = $(`#${toggleId}`);
			$toggle.attr('aria-checked', state ? 'true' : 'false');

			const settingName = $toggle.data('setting');
			if (settingName) {
				$(`input[name="${settingName}"]`).val(state ? '1' : '0').trigger('change');
			}
		},

		/**
		 * Get slider value
		 */
		getSliderValue: function(sliderId) {
			return parseFloat($(`#${sliderId}`).val());
		},

		/**
		 * Set slider value
		 */
		setSliderValue: function(sliderId, value) {
			const $slider = $(`#${sliderId}`);
			$slider.val(value).trigger('input');
		},

		/**
		 * Get dropdown value
		 */
		getDropdownValue: function(dropdownId) {
			const $dropdown = $(`#${dropdownId}`);
			return $dropdown.siblings('input[type="hidden"]').val();
		},

		/**
		 * Set dropdown value
		 */
		setDropdownValue: function(dropdownId, value) {
			const $dropdown = $(`#${dropdownId}`);
			const $option = $dropdown.find(`.wps-dropdown-list li[data-value="${value}"]`);
			if ($option.length) {
				$option.click();
			}
		}
	};

})(jQuery);
