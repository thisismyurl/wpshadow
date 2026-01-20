/**
 * WPShadow Command Palette Integration
 *
 * Integrates WPShadow features with WordPress Command Palette (Ctrl+K).
 * Allows users to quickly find and navigate to features using keyboard shortcuts.
 *
 * @package WPShadow\CoreSupport
 * @since 1.2601.75006
 */

(function($) {
	'use strict';

	// Wait for WordPress Command Palette API to be ready
	$(document).ready(function() {
		// Check if WordPress Command Palette exists (WP 6.5+)
		if (typeof wp !== 'undefined' && wp.commands && wp.commands.useCommands) {
			initializeCommandPalette();
		} else {
			// Fallback: Create our own Ctrl+K handler
			initializeFallbackCommandPalette();
		}
	});

	/**
	 * Initialize integration with WordPress native Command Palette
	 */
	function initializeCommandPalette() {
		if (!window.wpshadowCommands || !window.wpshadowCommands.features) {
			return;
		}

		const features = window.wpshadowCommands.features;

		// Register each feature as a command
		features.forEach(function(feature) {
			const keywords = [feature.name, ...feature.aliases];

			wp.commands.registerCommand({
				name: 'wpshadow-feature-' + feature.id,
				label: 'WPShadow: ' + feature.name,
				description: feature.description,
				category: feature.category || 'WPShadow Features',
				keywords: keywords,
				callback: function() {
					window.location.href = feature.url;
				}
			});
		});

		console.log('WPShadow: Registered ' + features.length + ' features with Command Palette');
	}

	/**
	 * Initialize fallback command palette for older WordPress versions
	 */
	function initializeFallbackCommandPalette() {
		let commandPaletteVisible = false;
		let commandSearchInput = null;
		let commandResults = null;
		let selectedIndex = 0;

		// Create command palette overlay
		function createCommandPalette() {
			const overlay = $('<div>', {
				id: 'wpshadow-command-palette-overlay',
				css: {
					position: 'fixed',
					top: 0,
					left: 0,
					right: 0,
					bottom: 0,
					backgroundColor: 'rgba(0, 0, 0, 0.5)',
					zIndex: 999999,
					display: 'none',
					alignItems: 'flex-start',
					justifyContent: 'center',
					paddingTop: '100px'
				}
			});

			const palette = $('<div>', {
				class: 'wpshadow-command-palette',
				css: {
					backgroundColor: '#fff',
					borderRadius: '8px',
					boxShadow: '0 10px 40px rgba(0, 0, 0, 0.2)',
					width: '90%',
					maxWidth: '600px',
					maxHeight: '500px',
					overflow: 'hidden'
				}
			});

			const searchContainer = $('<div>', {
				css: {
					padding: '16px',
					borderBottom: '1px solid #ddd'
				}
			});

			commandSearchInput = $('<input>', {
				type: 'text',
				placeholder: 'Search WPShadow features... (e.g., "stealing images", "broken links")',
				css: {
					width: '100%',
					padding: '12px 16px',
					fontSize: '16px',
					border: 'none',
					outline: 'none'
				}
			});

			searchContainer.append(commandSearchInput);

			commandResults = $('<div>', {
				class: 'wpshadow-command-results',
				css: {
					maxHeight: '400px',
					overflowY: 'auto'
				}
			});

			palette.append(searchContainer);
			palette.append(commandResults);
			overlay.append(palette);
			$('body').append(overlay);

			// Event handlers
			commandSearchInput.on('input', function() {
				const query = $(this).val();
				updateCommandResults(query);
			});

			commandSearchInput.on('keydown', function(e) {
				if (e.key === 'Escape') {
					hideCommandPalette();
				} else if (e.key === 'ArrowDown') {
					e.preventDefault();
					selectNext();
				} else if (e.key === 'ArrowUp') {
					e.preventDefault();
					selectPrevious();
				} else if (e.key === 'Enter') {
					e.preventDefault();
					executeSelected();
				}
			});

			overlay.on('click', function(e) {
				if (e.target === overlay[0]) {
					hideCommandPalette();
				}
			});

			return overlay;
		}

		// Update command results based on search
		function updateCommandResults(query) {
			if (!window.wpshadowCommands || !window.wpshadowCommands.features) {
				return;
			}

			const features = window.wpshadowCommands.features;
			const results = searchFeatures(query, features);

			commandResults.empty();
			selectedIndex = 0;

			if (results.length === 0) {
				commandResults.html('<div style="padding: 20px; text-align: center; color: #666;">No features found</div>');
				return;
			}

			results.forEach(function(feature, index) {
				const item = $('<div>', {
					class: 'wpshadow-command-item',
					'data-url': feature.url,
					'data-index': index,
					css: {
						padding: '12px 16px',
						cursor: 'pointer',
						borderBottom: '1px solid #f0f0f1',
						backgroundColor: index === 0 ? '#f6f7f7' : '#fff'
					}
				});

				const name = $('<div>', {
					css: {
						fontWeight: '600',
						marginBottom: '4px'
					},
					text: feature.name
				});

				const description = $('<div>', {
					css: {
						fontSize: '13px',
						color: '#646970'
					},
					text: feature.description
				});

				item.append(name);
				item.append(description);

				if (feature.matched_alias) {
					const match = $('<div>', {
						css: {
							fontSize: '12px',
							color: '#2271b1',
							marginTop: '4px'
						},
						text: '✓ Matched: ' + feature.matched_alias
					});
					item.append(match);
				}

				item.on('click', function() {
					window.location.href = $(this).data('url');
				});

				item.on('mouseenter', function() {
					selectedIndex = $(this).data('index');
					updateSelection();
				});

				commandResults.append(item);
			});
		}

		// Search features
		function searchFeatures(query, features) {
			if (!query || query.length < 2) {
				return features.slice(0, 10);
			}

			query = query.toLowerCase();
			const results = [];

			features.forEach(function(feature) {
				let score = 0;
				let matchedAlias = '';

				// Check name
				if (feature.name.toLowerCase().indexOf(query) !== -1) {
					score += 100;
					matchedAlias = 'Feature name';
				}

				// Check description
				if (feature.description.toLowerCase().indexOf(query) !== -1) {
					score += 50;
				}

				// Check aliases
				feature.aliases.forEach(function(alias) {
					if (alias.toLowerCase().indexOf(query) !== -1) {
						score += 80;
						matchedAlias = alias;
					}
				});

				if (score > 0) {
					results.push({
						...feature,
						score: score,
						matched_alias: matchedAlias
					});
				}
			});

			results.sort(function(a, b) {
				return b.score - a.score;
			});

			return results.slice(0, 10);
		}

		// Navigation
		function selectNext() {
			const items = commandResults.find('.wpshadow-command-item');
			if (items.length > 0) {
				selectedIndex = (selectedIndex + 1) % items.length;
				updateSelection();
			}
		}

		function selectPrevious() {
			const items = commandResults.find('.wpshadow-command-item');
			if (items.length > 0) {
				selectedIndex = (selectedIndex - 1 + items.length) % items.length;
				updateSelection();
			}
		}

		function updateSelection() {
			commandResults.find('.wpshadow-command-item').each(function(index) {
				$(this).css('backgroundColor', index === selectedIndex ? '#f6f7f7' : '#fff');
			});
		}

		function executeSelected() {
			const selected = commandResults.find('.wpshadow-command-item').eq(selectedIndex);
			if (selected.length > 0) {
				window.location.href = selected.data('url');
			}
		}

		// Show/hide command palette
		function showCommandPalette() {
			let overlay = $('#wpshadow-command-palette-overlay');
			if (overlay.length === 0) {
				overlay = createCommandPalette();
			}

			overlay.css('display', 'flex');
			commandSearchInput.val('').focus();
			updateCommandResults('');
			commandPaletteVisible = true;
		}

		function hideCommandPalette() {
			$('#wpshadow-command-palette-overlay').css('display', 'none');
			commandPaletteVisible = false;
		}

		// Keyboard shortcut: Ctrl+K or Cmd+K
		$(document).on('keydown', function(e) {
			// Ctrl+K or Cmd+K
			if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
				// Only activate on WPShadow pages
				if (window.location.href.indexOf('wpshadow') !== -1) {
					e.preventDefault();
					if (commandPaletteVisible) {
						hideCommandPalette();
					} else {
						showCommandPalette();
					}
				}
			}

			// Escape to close
			if (e.key === 'Escape' && commandPaletteVisible) {
				hideCommandPalette();
			}
		});

		console.log('WPShadow: Command Palette fallback initialized (Ctrl+K to open)');
	}

})(jQuery);
