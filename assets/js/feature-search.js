(function ($) {
	'use strict';

	const config = window.wpshadowFeatureSearch || null;
	const $widget = $('.wpshadow-feature-search-widget');

	if (!config || !$widget.length) {
		return;
	}

	const $input = $('#wpshadow-feature-search-input');
	const $results = $('#wpshadow-search-results');
	const $clearInput = $('.wpshadow-feature-search-clear');
	const $clearAccessed = $('.wpshadow-feature-search-clear-accessed');
	let debounceHandle = null;

	const renderResults = (items) => {
		if (!Array.isArray(items)) {
			return;
		}

		if (!items.length) {
			$results.html('<div class="wpshadow-feature-search-no-results">' + config.strings.noResults + '</div>');
			return;
		}

		const rows = items.map((item) => {
			const safeName = $('<div/>').text(item.name || '').html();
			const safeDescription = $('<div/>').text(item.description || '').html();
			const safeMatch = $('<div/>').text(item.matched_alias || '').html();
			const url = item.url || '#';

			let matchRow = '';
			if (item.matched_alias) {
				matchRow = '<div class="wpshadow-feature-search-result-match">' + safeMatch + '</div>';
			}

			return [
				'<div class="wpshadow-feature-search-result-item" role="option" tabindex="0" data-feature-id="',
				item.id,
				'" data-url="',
				url,
				'">',
				'<div class="wpshadow-feature-search-result-name">', safeName, '</div>',
				'<div class="wpshadow-feature-search-result-description">', safeDescription, '</div>',
				matchRow,
				'</div>'
			].join('');
		}).join('');

		$results.html(rows);
	};

	const renderChips = (items) => {
		const $chips = $('.wpshadow-feature-search-chips');
		if (!$chips.length) {
			return;
		}

		if (!Array.isArray(items) || !items.length) {
			$chips.empty();
			return;
		}

		const html = items.map((item) => {
			const safeName = $('<div/>').text(item.name || '').html();
			const icon = item.icon || 'dashicons-admin-generic';
			const url = item.url || '#';
			return [
				'<button type="button" class="wpshadow-feature-chip" role="listitem" data-feature-id="',
				item.id,
				'" data-url="',
				url,
				'">',
				'<span class="dashicons ', icon, '" aria-hidden="true"></span>',
				'<span class="wpshadow-feature-chip-label">', safeName, '</span>',
				'</button>'
			].join('');
		}).join('');

		$chips.html(html);
	};

	const renderAccessed = (items) => {
		const $list = $('.wpshadow-feature-search-accessed');
		if (!$list.length) {
			return;
		}

		if (!Array.isArray(items) || !items.length) {
			$list.html('<li class="wpshadow-feature-search-no-results">' + config.strings.noResults + '</li>');
			return;
		}

		const html = items.map((item) => {
			const safeName = $('<div/>').text(item.name || '').html();
			const url = item.url || '#';
			const count = item.count || 0;
			const icon = item.icon || 'dashicons-admin-generic';
			return [
				'<li class="wpshadow-feature-search-accessed-item" role="listitem">',
				'<a class="wpshadow-feature-search-accessed-link" data-feature-id="', item.id, '" data-url="', url, '" href="', url, '">',
				'<span class="dashicons ', icon, '" aria-hidden="true"></span>',
				'<span class="wpshadow-feature-search-accessed-name">', safeName, '</span>',
				'<span class="wpshadow-feature-search-accessed-count">', count, '</span>',
				'</a>',
				'</li>'
			].join('');
		}).join('');

		$list.html(html);
	};

	const syncStateFromResponse = (data) => {
		if (data.suggestions) {
			renderChips(data.suggestions);
		}
		if (data.accessed) {
			renderAccessed(data.accessed);
		}
		if (data.results) {
			renderResults(data.results);
		}
	};

	const performSearch = (query) => {
		$.ajax({
			type: 'POST',
			url: config.ajaxUrl,
			dataType: 'json',
			data: {
				action: 'wpshadow_search_features',
				nonce: config.nonce,
				query: query
			}
		}).done((response) => {
			if (response && response.success && response.data) {
				syncStateFromResponse(response.data);
			}
		});
	};

	const trackAndVisit = (featureId, url) => {
		$.ajax({
			type: 'POST',
			url: config.ajaxUrl,
			data: {
				action: 'wpshadow_track_feature_access',
				nonce: config.nonce,
				feature_id: featureId
			}
		}).always(() => {
			window.location.href = url || '#';
		});
	};

	// Event bindings
	$input.on('input', function () {
		const query = $(this).val().trim();
		clearTimeout(debounceHandle);
		debounceHandle = setTimeout(() => {
			performSearch(query);
		}, 200);
	});

	$input.on('keydown', function (e) {
		if (e.key === 'Escape') {
			$input.val('');
			$results.empty();
		}
	});

	$clearInput.on('click', function () {
		$input.val('');
		$results.empty();
	});

	$widget.on('click keypress', '.wpshadow-feature-search-result-item', function (e) {
		if (e.type === 'keypress' && e.key !== 'Enter') {
			return;
		}
		const featureId = $(this).data('feature-id');
		const url = $(this).data('url');
		trackAndVisit(featureId, url);
	});

	$widget.on('click', '.wpshadow-feature-chip', function () {
		const featureId = $(this).data('feature-id');
		const url = $(this).data('url');
		trackAndVisit(featureId, url);
	});

	$widget.on('click', '.wpshadow-feature-search-accessed-link', function (e) {
		e.preventDefault();
		const featureId = $(this).data('feature-id');
		const url = $(this).data('url');
		trackAndVisit(featureId, url);
	});

	$clearAccessed.on('click', function () {
		$.ajax({
			type: 'POST',
			url: config.ajaxUrl,
			data: {
				action: 'wpshadow_clear_accessed_pages',
				nonce: config.nonce
			}
		}).done((response) => {
			if (response && response.success) {
				renderAccessed([]);
			}
		});
	});

	// Initialize with localized data
	renderChips(config.suggestions || []);
	renderAccessed(config.accessed || []);

})(jQuery);
