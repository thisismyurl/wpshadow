/**
 * WPS Responsive Navigation
 * Handles mobile navigation drawer and hamburger menu
 *
 * @package WPSHADOW_CORE_SUPPORT
 * @since 1.2601.71920
 */

(function($) {
	'use strict';

	/**
	 * Initialize responsive navigation
	 */
	function initResponsiveNav() {
		// Check if mobile navigation elements exist
		let $toggle = $('.wps-mobile-nav-toggle');
		let $drawer = $('.wps-mobile-nav-drawer');
		let $overlay = $('.wps-mobile-nav-overlay');

		// If elements don't exist, create them
		if ($toggle.length === 0) {
			createMobileNav();
			$toggle = $('.wps-mobile-nav-toggle');
			$drawer = $('.wps-mobile-nav-drawer');
			$overlay = $('.wps-mobile-nav-overlay');
		}

		// Toggle mobile navigation
		$toggle.on('click', function(e) {
			e.preventDefault();
			toggleMobileNav();
		});

		// Close on overlay click
		$overlay.on('click', function() {
			closeMobileNav();
		});

		// Close on escape key
		$(document).on('keydown', function(e) {
			if (e.key === 'Escape' && $drawer.hasClass('is-open')) {
				closeMobileNav();
			}
		});

		// Close on navigation link click
		$drawer.find('a').on('click', function() {
			closeMobileNav();
		});

		// Handle window resize
		let resizeTimer;
		$(window).on('resize', function() {
			clearTimeout(resizeTimer);
			resizeTimer = setTimeout(function() {
				// Close mobile nav if window is resized to desktop
				if ($(window).width() >= 1024) {
					closeMobileNav();
				}
			}, 250);
		});

		// Prevent body scroll when drawer is open
		$drawer.on('touchmove', function(e) {
			if ($drawer.hasClass('is-open')) {
				e.stopPropagation();
			}
		});
	}

	/**
	 * Create mobile navigation elements
	 */
	function createMobileNav() {
		const $body = $('body');

		// Create toggle button
		const $toggle = $('<button>', {
			'class': 'wps-mobile-nav-toggle',
			'aria-label': 'Toggle mobile navigation',
			'aria-expanded': 'false',
			'aria-controls': 'wps-mobile-nav-drawer'
		}).html('<span class="dashicons dashicons-menu"></span>');

		// Create overlay
		const $overlay = $('<div>', {
			'class': 'wps-mobile-nav-overlay',
			'aria-hidden': 'true'
		});

		// Create drawer
		const $drawer = $('<div>', {
			'class': 'wps-mobile-nav-drawer',
			'id': 'wps-mobile-nav-drawer',
			'role': 'navigation',
			'aria-label': 'Mobile navigation'
		});

		// Clone main navigation if it exists
		const $mainNav = $('.wps-tab-navigation');
		if ($mainNav.length > 0) {
			const $navList = $('<nav>').html('<ul></ul>');
			$mainNav.find('.nav-tab').each(function() {
				const $tab = $(this);
				const $listItem = $('<li>').html(
					$('<a>', {
						href: $tab.attr('href') || '#',
						text: $tab.text().trim()
					})
				);
				$navList.find('ul').append($listItem);
			});
			$drawer.append($navList);
		} else {
			// Fallback: Create basic navigation from page context
			const $navList = $('<nav>').html('<ul></ul>');
			
			// Add Dashboard link
			$navList.find('ul').append(
				$('<li>').html(
					$('<a>', {
						href: 'admin.php?page=wpshadow',
						html: '<span class="dashicons dashicons-dashboard"></span> Dashboard'
					})
				)
			);

			// Add Modules link if user can manage options
			if ($('body').hasClass('admin_page_wpshadow-modules') || 
			    $('#adminmenu a[href*="wpshadow-modules"]').length > 0) {
				$navList.find('ul').append(
					$('<li>').html(
						$('<a>', {
							href: 'admin.php?page=wpshadow-modules',
							html: '<span class="dashicons dashicons-admin-plugins"></span> Modules'
						})
					)
				);
			}

			// Add Settings link
			$navList.find('ul').append(
				$('<li>').html(
					$('<a>', {
						href: 'admin.php?page=wpshadow-settings',
						html: '<span class="dashicons dashicons-admin-settings"></span> Settings'
					})
				)
			);

			$drawer.append($navList);
		}

		// Append elements to body
		$body.append($toggle, $overlay, $drawer);
	}

	/**
	 * Toggle mobile navigation
	 */
	function toggleMobileNav() {
		const $toggle = $('.wps-mobile-nav-toggle');
		const $drawer = $('.wps-mobile-nav-drawer');
		const $overlay = $('.wps-mobile-nav-overlay');
		const isOpen = $drawer.hasClass('is-open');

		if (isOpen) {
			closeMobileNav();
		} else {
			openMobileNav();
		}
	}

	/**
	 * Open mobile navigation
	 */
	function openMobileNav() {
		const $toggle = $('.wps-mobile-nav-toggle');
		const $drawer = $('.wps-mobile-nav-drawer');
		const $overlay = $('.wps-mobile-nav-overlay');

		$drawer.addClass('is-open');
		$overlay.addClass('is-visible');
		$toggle.attr('aria-expanded', 'true');
		$toggle.find('.dashicons').removeClass('dashicons-menu').addClass('dashicons-no-alt');
		
		// Prevent body scroll
		$('body').css('overflow', 'hidden');
		
		// Focus first link in drawer
		setTimeout(function() {
			$drawer.find('a:first').focus();
		}, 300);
	}

	/**
	 * Close mobile navigation
	 */
	function closeMobileNav() {
		const $toggle = $('.wps-mobile-nav-toggle');
		const $drawer = $('.wps-mobile-nav-drawer');
		const $overlay = $('.wps-mobile-nav-overlay');

		$drawer.removeClass('is-open');
		$overlay.removeClass('is-visible');
		$toggle.attr('aria-expanded', 'false');
		$toggle.find('.dashicons').removeClass('dashicons-no-alt').addClass('dashicons-menu');
		
		// Restore body scroll
		$('body').css('overflow', '');
	}

	/**
	 * Initialize on document ready
	 */
	$(document).ready(function() {
		// Only initialize on WPS pages
		if ($('.wps-core-wrap').length > 0 || $('body[class*="wpshadow"]').length > 0) {
			initResponsiveNav();
		}
	});

	/**
	 * Re-initialize on AJAX complete (for dynamic content)
	 */
	$(document).ajaxComplete(function() {
		if ($('.wps-mobile-nav-toggle').length === 0 && $('.wps-core-wrap').length > 0) {
			initResponsiveNav();
		}
	});

})(jQuery);
