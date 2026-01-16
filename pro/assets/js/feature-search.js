/**
 * Feature Search JavaScript
 *
 * @package WPShadow\CoreSupport
 * @since 1.2601.78000
 */

(function($) {
	'use strict';

	let searchTimeout = null;
	let currentFocusIndex = -1;
	let searchResults = [];

	/**
	 * Initialize feature search.
	 */
	function initFeatureSearch() {
		const $searchInput = $('#wpshadow-feature-search-input');
		const $searchResults = $('#wpshadow-search-results');
		const $clearBtn = $('.wpshadow-clear-accessed');

		if (!$searchInput.length) {
			return;
		}

		// Handle search input.
		$searchInput.on('input', function() {
			clearTimeout(searchTimeout);
			const query = $(this).val().trim();

			searchTimeout = setTimeout(function() {
				performSearch(query);
			}, 300);
		});

		// Handle focus - show suggestions.
		$searchInput.on('focus', function() {
			if ($(this).val().trim() === '') {
				performSearch('');
			}
		});

		// Handle keyboard navigation.
		$searchInput.on('keydown', function(e) {
			const $items = $searchResults.find('.wpshadow-search-result-item');
			
			if (!$items.length) {
				return;
			}

			switch(e.key) {
				case 'ArrowDown':
					e.preventDefault();
					navigateResults(1, $items);
					break;
				case 'ArrowUp':
					e.preventDefault();
					navigateResults(-1, $items);
					break;
				case 'Enter':
					e.preventDefault();
					if (currentFocusIndex >= 0 && currentFocusIndex < searchResults.length) {
						selectResult(searchResults[currentFocusIndex]);
					}
					break;
				case 'Escape':
					closeResults();
					$searchInput.blur();
					break;
			}
		});

		// Handle click outside to close.
		$(document).on('click', function(e) {
			if (!$(e.target).closest('.wpshadow-feature-search-container').length) {
				closeResults();
			}
		});

		// Handle clear accessed pages button.
		$clearBtn.on('click', function(e) {
			e.preventDefault();
			clearAccessedPages();
		});

		// Track feature access when navigating to feature details.
		$(document).on('click', 'a[href*="wpshadow-feature-details"]', function() {
			const url = $(this).attr('href');
			const match = url.match(/feature=([^&]+)/);
			
			if (match && match[1]) {
				trackFeatureAccess(match[1]);
			}
		});
	}

	/**
	 * Navigate search results with keyboard.
	 *
	 * @param {number} direction - 1 for down, -1 for up.
	 * @param {jQuery} $items - Result items.
	 */
	function navigateResults(direction, $items) {
		currentFocusIndex += direction;

		if (currentFocusIndex < 0) {
			currentFocusIndex = $items.length - 1;
		} else if (currentFocusIndex >= $items.length) {
			currentFocusIndex = 0;
		}

		$items.removeClass('focused');
		$items.eq(currentFocusIndex).addClass('focused').focus();
		
		// Update ARIA.
		$('#wpshadow-feature-search-input')
			.attr('aria-activedescendant', $items.eq(currentFocusIndex).attr('id'));
	}

	/**
	 * Perform feature search.
	 *
	 * @param {string} query - Search query.
	 */
	function performSearch(query) {
		const $searchResults = $('#wpshadow-search-results');
		const $searchInput = $('#wpshadow-feature-search-input');

		// Show loading state.
		$searchResults.html('<div class="wpshadow-search-loading">' + 'Searching...' + '</div>').addClass('active');
		$searchInput.attr('aria-expanded', 'true');

		$.ajax({
			url: wpshadowFeatureSearch.ajaxUrl,
			type: 'POST',
			data: {
				action: 'wpshadow_search_features',
				nonce: wpshadowFeatureSearch.nonce,
				query: query
			},
			success: function(response) {
				if (response.success && response.data.results) {
					displayResults(response.data.results);
				} else {
					displayNoResults();
				}
			},
			error: function() {
				displayNoResults();
			}
		});
	}

	/**
	 * Display search results.
	 *
	 * @param {Array} results - Search results.
	 */
	function displayResults(results) {
		const $searchResults = $('#wpshadow-search-results');
		searchResults = results;
		currentFocusIndex = -1;

		if (results.length === 0) {
			displayNoResults();
			return;
		}

		let html = '';
		results.forEach(function(result, index) {
			html += '<div class="wpshadow-search-result-item" ' +
					'id="wpshadow-result-' + index + '" ' +
					'role="option" ' +
					'data-feature-id="' + result.id + '" ' +
					'data-url="' + result.url + '" ' +
					'tabindex="0">' +
					'<span class="dashicons ' + result.icon + ' wpshadow-search-result-icon"></span>' +
					'<div class="wpshadow-search-result-content">' +
					'<span class="wpshadow-search-result-name">' + result.name + '</span>' +
					'<div class="wpshadow-search-result-description">' + result.description + '</div>' +
					'</div>' +
					'</div>';
		});

		$searchResults.html(html).addClass('active');

		// Attach click handlers.
		$searchResults.find('.wpshadow-search-result-item').on('click', function() {
			const index = $(this).index();
			selectResult(searchResults[index]);
		}).on('keypress', function(e) {
			if (e.key === 'Enter' || e.key === ' ') {
				e.preventDefault();
				const index = $(this).index();
				selectResult(searchResults[index]);
			}
		});
	}

	/**
	 * Display no results message.
	 */
	function displayNoResults() {
		const $searchResults = $('#wpshadow-search-results');
		$searchResults.html(
			'<div class="wpshadow-search-no-results">' +
			wpshadowFeatureSearch.noResults +
			'</div>'
		).addClass('active');
	}

	/**
	 * Select a search result.
	 *
	 * @param {Object} result - Selected result.
	 */
	function selectResult(result) {
		trackFeatureAccess(result.id);
		window.location.href = result.url;
	}

	/**
	 * Close search results.
	 */
	function closeResults() {
		$('#wpshadow-search-results').removeClass('active');
		$('#wpshadow-feature-search-input')
			.attr('aria-expanded', 'false')
			.removeAttr('aria-activedescendant');
		currentFocusIndex = -1;
	}

	/**
	 * Track feature access.
	 *
	 * @param {string} featureId - Feature ID.
	 */
	function trackFeatureAccess(featureId) {
		$.ajax({
			url: wpshadowFeatureSearch.ajaxUrl,
			type: 'POST',
			data: {
				action: 'wpshadow_track_feature_access',
				nonce: wpshadowFeatureSearch.nonce,
				feature_id: featureId
			}
		});
	}

	/**
	 * Clear accessed pages list.
	 */
	function clearAccessedPages() {
		if (!confirm('Are you sure you want to clear your commonly accessed pages?')) {
			return;
		}

		$.ajax({
			url: wpshadowFeatureSearch.ajaxUrl,
			type: 'POST',
			data: {
				action: 'wpshadow_clear_accessed_pages',
				nonce: wpshadowFeatureSearch.nonce
			},
			success: function(response) {
				if (response.success) {
					$('.wpshadow-commonly-accessed').fadeOut(300, function() {
						$(this).remove();
					});
				}
			}
		});
	}

	// Initialize on document ready.
	$(document).ready(function() {
		initFeatureSearch();
	});

})(jQuery);
