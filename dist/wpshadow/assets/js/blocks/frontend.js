/**
 * WPShadow Blocks Frontend JavaScript
 *
 * Handles interactivity for custom blocks with advanced features.
 *
 * @package WPShadow
 * @since   0.6034.1500
 */

(function($) {
	'use strict';

	/**
	 * Pricing Table Monthly/Annual Toggle
	 */
	function initPricingTableToggle() {
		$('.wpshadow-pricing-toggle').on('click', '.wpshadow-pricing-toggle__option', function() {
			const $toggle = $(this).closest('.wpshadow-pricing-toggle');
			const period = $(this).data('period');

			$toggle.find('.wpshadow-pricing-toggle__option').removeClass('is-active');
			$(this).addClass('is-active');

			// Animate price changes
			const $table = $toggle.closest('.wpshadow-pricing-table');
			$table.find('.wpshadow-pricing-plan').each(function() {
				const $priceEl = $(this).find('.wpshadow-pricing-plan__price');
				const monthlyPrice = $priceEl.data('monthly');
				const annualPrice = $priceEl.data('annual');

				if (monthlyPrice && annualPrice) {
					$priceEl.fadeOut(150, function() {
						const newPrice = period === 'annual' ? annualPrice : monthlyPrice;
						$priceEl.text(newPrice).fadeIn(150);
					});
				}
			});
		});
	}

	/**
	 * FAQ Search
	 */
	function initFAQSearch() {
		$('.wpshadow-faq-search').on('input', function() {
			const query = $(this).val().toLowerCase();
			const $accordion = $(this).closest('.wpshadow-faq-accordion');
			const $items = $accordion.find('.wpshadow-faq-item');
			let visibleCount = 0;

			$items.each(function() {
				const question = $(this).find('.wpshadow-faq-question').text().toLowerCase();
				const answer = $(this).find('.wpshadow-faq-answer').text().toLowerCase();

				if (question.includes(query) || answer.includes(query)) {
					$(this).show();
					visibleCount++;
				} else {
					$(this).hide();
				}
			});

			// Show/hide no results message
			let $noResults = $accordion.find('.wpshadow-faq-no-results');
			if (visibleCount === 0 && query.length > 0) {
				if ($noResults.length === 0) {
					$noResults = $('<p class="wpshadow-faq-no-results">No FAQs found matching your search.</p>');
					$accordion.append($noResults);
				}
			} else {
				$noResults.remove();
			}
		});
	}

	/**
	 * FAQ Accordion
	 */
	function initFAQAccordion() {
		$('.wpshadow-faq-question').on('click', function() {
			const $item = $(this).closest('.wpshadow-faq-item');
			const $accordion = $item.closest('.wpshadow-faq-accordion');
			const allowMultiple = $accordion.data('allow-multiple') === 1;
			const $answer = $item.find('.wpshadow-faq-answer');
			const isOpen = $item.hasClass('wpshadow-open');

			// Close other items if not allowing multiple
			if (!allowMultiple && !isOpen) {
				$accordion.find('.wpshadow-faq-item').removeClass('wpshadow-open');
				$accordion.find('.wpshadow-faq-answer').attr('hidden', true);
				$accordion.find('.wpshadow-faq-question').attr('aria-expanded', 'false');
			}

			// Toggle current item
			$item.toggleClass('wpshadow-open');
			$(this).attr('aria-expanded', !isOpen);

			if (isOpen) {
				$answer.attr('hidden', true);
			} else {
				$answer.removeAttr('hidden');
			}
		});

		// Keyboard navigation
		$('.wpshadow-faq-question').on('keydown', function(e) {
			if (e.key === 'Enter' || e.key === ' ') {
				e.preventDefault();
				$(this).click();
			}
		});
	}

	/**
	 * Before/After Slider
	 */
	function initBeforeAfterSlider() {
		$('.wpshadow-before-after').each(function() {
			const $container = $(this);
			const $slider = $container.find('.wpshadow-ba-slider');
			const $after = $container.find('.wpshadow-ba-after');
			const containerRect = this.getBoundingClientRect();
			let isDragging = false;

			function updateSlider(x) {
				const offsetX = x - containerRect.left;
				const percentage = Math.max(0, Math.min(100, (offsetX / containerRect.width) * 100));

				$slider.css('left', percentage + '%');
				$after.css('clip-path', `inset(0 0 0 ${percentage}%)`);
				$slider.attr('aria-valuenow', Math.round(percentage));
			}

			// Mouse events
			$slider.on('mousedown', function() {
				isDragging = true;
			});

			$(document).on('mousemove', function(e) {
				if (isDragging) {
					updateSlider(e.clientX);
				}
			});

			$(document).on('mouseup', function() {
				isDragging = false;
			});

			// Touch events
			$slider.on('touchstart', function() {
				isDragging = true;
			});

			$(document).on('touchmove', function(e) {
				if (isDragging && e.touches && e.touches.length > 0) {
					updateSlider(e.touches[0].clientX);
				}
			});

			$(document).on('touchend', function() {
				isDragging = false;
			});

			// Keyboard navigation
			$slider.on('keydown', function(e) {
				const current = parseInt($(this).attr('aria-valuenow'), 10);
				let newValue = current;

				if (e.key === 'ArrowLeft') {
					newValue = Math.max(0, current - 5);
				} else if (e.key === 'ArrowRight') {
					newValue = Math.min(100, current + 5);
				} else {
					return;
				}

				e.preventDefault();
				const newX = containerRect.left + (newValue / 100) * containerRect.width;
				updateSlider(newX);
			});

			// Double-click to zoom
			let isZoomed = false;
			$container.find('.wpshadow-ba-before, .wpshadow-ba-after').on('dblclick', function(e) {
				e.preventDefault();
				isZoomed = !isZoomed;

				if (isZoomed) {
					$container.addClass('is-zoomed');
					const rect = $container[0].getBoundingClientRect();
					const x = ((e.clientX - rect.left) / rect.width) * 100;
					const y = ((e.clientY - rect.top) / rect.height) * 100;
					$container.css('transform-origin', x + '% ' + y + '%');
				} else {
					$container.removeClass('is-zoomed');
				}
			});
		});
	}

	/**
	 * Stats Counter with Animation
	 */
	function initStatsCounter() {
		const $counters = $('.wpshadow-stats-counter[data-animate="1"]');

		if (!$counters.length) {
			return;
		}

		const observer = new IntersectionObserver(function(entries) {
			entries.forEach(function(entry) {
				if (entry.isIntersecting && !$(entry.target).data('animated')) {
					$(entry.target).data('animated', true);
					animateCounters($(entry.target));
				}
			});
		}, { threshold: 0.5 });

		$counters.each(function() {
			observer.observe(this);
		});
	}

	function animateCounters($container) {
		const duration = parseInt($container.data('duration'), 10) || 2000;

		$container.find('.wpshadow-counter').each(function(index) {
			const $counter = $(this);
			const $stat = $counter.closest('.wpshadow-stat');
			const target = parseFloat($counter.data('target'));
			const startTime = Date.now();

			function update() {
				const elapsed = Date.now() - startTime;
				const progress = Math.min(elapsed / duration, 1);
				const value = Math.floor(progress * target);

				$counter.text(value);

				if (progress < 1) {
					requestAnimationFrame(update);
				} else {
					$counter.text(target);
					// Celebrate milestone with pulse effect
					$stat.addClass('milestone-reached');
					setTimeout(() => $stat.removeClass('milestone-reached'), 1000);
				}
			}

			// Stagger animation start
			setTimeout(() => requestAnimationFrame(update), index * 200);
		});
	}

	/**
	 * Countdown Timer with Milestones
	 */
	function initCountdownTimer() {
		$('.wpshadow-countdown').each(function() {
			const $countdown = $(this);
			const targetDate = new Date($countdown.data('target-date')).getTime();
			const expiredText = $countdown.data('expired-text');
			let lastAlertedHour = null;

			function updateCountdown() {
				const now = new Date().getTime();
				const distance = targetDate - now;

				if (distance < 0) {
					$countdown.find('.wpshadow-countdown-timer').hide();
					$countdown.find('.wpshadow-countdown-expired').show();
					return;
				}

				const days = Math.floor(distance / (1000 * 60 * 60 * 24));
				const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
				const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
				const seconds = Math.floor((distance % (1000 * 60)) / 1000);

				// Milestone alerts (24 hours, 1 hour remaining)
				const totalHours = Math.floor(distance / (1000 * 60 * 60));
				if ((totalHours === 24 || totalHours === 1) && lastAlertedHour !== totalHours) {
					lastAlertedHour = totalHours;
					$countdown.addClass('milestone-alert');
					setTimeout(() => $countdown.removeClass('milestone-alert'), 2000);
				}

				$countdown.find('[data-unit="days"]').text(String(days).padStart(2, '0'));
				$countdown.find('[data-unit="hours"]').text(String(hours).padStart(2, '0'));
				$countdown.find('[data-unit="minutes"]').text(String(minutes).padStart(2, '0'));
				$countdown.find('[data-unit="seconds"]').text(String(seconds).padStart(2, '0'));

				setTimeout(updateCountdown, 1000);
			}

			updateCountdown();
		});
	}

	/**
	 * Content Tabs with Deep Linking
	 */
	function initContentTabs() {
		// Handle deep linking on page load
		const hash = window.location.hash.substring(1);
		if (hash) {
			const $targetTab = $('[data-tab="' + hash + '"]');
			if ($targetTab.length) {
				$targetTab.click();
			}
		}

		$('.wpshadow-tab-button').on('click', function() {
			const $button = $(this);
			const $tabs = $button.closest('.wpshadow-content-tabs');
			const panelId = $button.attr('aria-controls');
			const tabId = $button.data('tab');

			// Update URL hash without scrolling
			if (tabId && history.pushState) {
				history.pushState(null, null, '#' + tabId);
			}

			// Update buttons
			$tabs.find('.wpshadow-tab-button')
				.removeClass('wpshadow-active')
				.attr('aria-selected', 'false')
				.attr('tabindex', '-1');

			$button
				.addClass('wpshadow-active')
				.attr('aria-selected', 'true')
				.attr('tabindex', '0');

			// Update panels
			$tabs.find('.wpshadow-tab-panel')
				.removeClass('wpshadow-active')
				.attr('hidden', true);

			$('#' + panelId)
				.addClass('wpshadow-active')
				.removeAttr('hidden');
		});

		// Keyboard navigation
		$('.wpshadow-tab-button').on('keydown', function(e) {
			const $button = $(this);
			const $buttons = $button.closest('.wpshadow-tab-list').find('.wpshadow-tab-button');
			const index = $buttons.index($button);
			let newIndex = index;

			if (e.key === 'ArrowLeft' || e.key === 'ArrowUp') {
				newIndex = index > 0 ? index - 1 : $buttons.length - 1;
			} else if (e.key === 'ArrowRight' || e.key === 'ArrowDown') {
				newIndex = index < $buttons.length - 1 ? index + 1 : 0;
			} else if (e.key === 'Home') {
				newIndex = 0;
			} else if (e.key === 'End') {
				newIndex = $buttons.length - 1;
			} else {
				return;
			}

			e.preventDefault();
			$buttons.eq(newIndex).focus().click();
		});
	}

	/**
	 * Alert Dismiss with Auto-Dismiss
	 */
	function initAlertDismiss() {
		// Auto-dismiss alerts
		$('.wpshadow-alert[data-auto-dismiss]').each(function() {
			const $alert = $(this);
			const seconds = parseInt($alert.data('auto-dismiss'), 10);

			if (seconds > 0) {
				// Add progress bar
				const $progress = $('<div class="wpshadow-alert__progress"></div>');
				$alert.append($progress);

				// Animate progress bar
				setTimeout(() => $progress.css('width', '0'), 100);

				// Auto-dismiss after duration
				setTimeout(() => {
					$alert.fadeOut(300, function() {
						$(this).remove();
					});
				}, seconds * 1000);
			}
		});

		// Manual dismiss
		$('.wpshadow-alert-dismiss').on('click', function() {
			$(this).closest('.wpshadow-alert').fadeOut(300, function() {
				$(this).remove();
			});
		});
	}

	/**
	 * Progress Bar Animation
	 */
	function initProgressBars() {
		const $containers = $('.wpshadow-progress-bars[data-animate="1"]');

		if (!$containers.length) {
			return;
		}

		const observer = new IntersectionObserver(function(entries) {
			entries.forEach(function(entry) {
				if (entry.isIntersecting && !$(entry.target).data('animated')) {
					$(entry.target).data('animated', true);
					animateProgressBars($(entry.target));
				}
			});
		}, { threshold: 0.5 });

		$containers.each(function() {
			observer.observe(this);
		});
	}

	function animateProgressBars($container) {
		$container.find('.wpshadow-progress-fill').each(function(index) {
			const $fill = $(this);
			const $bar = $fill.closest('.wpshadow-progress-bar');
			const percentage = $fill.data('percentage');

			// Stagger animations
			setTimeout(() => {
				$fill.css('width', percentage + '%');

				// Confetti celebration for 100% completion
				if (parseInt(percentage, 10) === 100) {
					setTimeout(() => {
						$bar.addClass('completed');
						createConfetti($bar[0]);
					}, 500);
				}
			}, index * 200);
		});
	}

	/**
	 * Create confetti particles
	 */
	function createConfetti(container) {
		const colors = ['#ff6b6b', '#4ecdc4', '#45b7d1', '#f9ca24', '#6c5ce7'];
		for (let i = 0; i < 15; i++) {
			const $confetti = $('<span class="wpshadow-confetti"></span>');
			$confetti.css({
				left: (Math.random() * 100) + '%',
				animationDelay: (Math.random() * 0.5) + 's',
				backgroundColor: colors[Math.floor(Math.random() * colors.length)]
			});
			$(container).append($confetti);
			setTimeout(() => $confetti.remove(), 2000);
		}
	}

	/**
	 * Logo Tooltips
	 */
	function initLogoTooltips() {
		$('.wpshadow-logo-item[data-testimonial]').each(function() {
			const $logo = $(this);
			const testimonial = $logo.data('testimonial');

			if (testimonial) {
				const $tooltip = $('<div class="wpshadow-logo-tooltip">' + testimonial + '</div>');
				$logo.append($tooltip);

				$logo.on('mouseenter', () => $tooltip.addClass('is-visible'));
				$logo.on('mouseleave', () => $tooltip.removeClass('is-visible'));
			}
		});
	}

	/**
	 * Logo Grid Carousel (if enabled)
	 */
	function initLogoCarousel() {
		$('.wpshadow-logo-grid.wpshadow-layout-carousel[data-autoplay="1"]').each(function() {
			const $container = $(this);
			const $logoContainer = $container.find('.wpshadow-logo-container');
			const speed = parseInt($container.data('speed'), 10) || 3000;
			let currentIndex = 0;
			const $logos = $logoContainer.find('.wpshadow-logo-item');
			const totalLogos = $logos.length;

			if (totalLogos === 0) {
				return;
			}

			function rotateLogos() {
				currentIndex = (currentIndex + 1) % totalLogos;
				const offset = -currentIndex * (100 / totalLogos);
				$logoContainer.css('transform', `translateX(${offset}%)`);
			}

			setInterval(rotateLogos, speed);
		});
	}

	/**
	 * Initialize all blocks
	 */
	$(document).ready(function() {
		initPricingTableToggle();
		initFAQSearch();
		initFAQAccordion();
		initBeforeAfterSlider();
		initStatsCounter();
		initLogoTooltips();
		initCountdownTimer();
		initContentTabs();
		initAlertDismiss();
		initProgressBars();
		initLogoCarousel();
	});

})(jQuery);
