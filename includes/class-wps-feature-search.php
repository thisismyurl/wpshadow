<?php
/**
 * Feature Search and Command Palette Integration
 *
 * Provides search functionality for WPShadow features and integrates
 * with WordPress Command Palette (Ctrl+K).
 *
 * @package WPShadow\CoreSupport
 * @since 1.2601.75006
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Feature Search Handler
 */
class WPSHADOW_Feature_Search {

	/**
	 * Initialize search functionality.
	 *
	 * @return void
	 */
	public static function init(): void {
		// AJAX endpoint for feature search
		add_action( 'wp_ajax_wpshadow_search_features', array( __CLASS__, 'ajax_search_features' ) );

		// Add search bar to dashboard
		add_action( 'wpshadow_dashboard_header', array( __CLASS__, 'render_search_bar' ) );

		// Enqueue assets for command palette integration
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_command_palette_integration' ) );

		// Register WordPress Command Palette commands
		add_action( 'admin_init', array( __CLASS__, 'register_command_palette_commands' ) );
	}

	/**
	 * Render search bar in dashboard header.
	 *
	 * @return void
	 */
	public static function render_search_bar(): void {
		?>
		<div class="wpshadow-search-bar-container" style="margin: 20px 0;">
			<div class="wpshadow-search-bar" style="max-width: 600px;">
				<input
					type="text"
					id="wpshadow-feature-search"
					class="wpshadow-feature-search-input"
					placeholder="<?php esc_attr_e( 'Search features... (e.g., "stealing images", "broken links", "speed up site")', 'wpshadow' ); ?>"
					style="width: 100%; padding: 12px 40px 12px 16px; font-size: 14px; border: 2px solid #ddd; border-radius: 4px;"
				/>
				<span class="dashicons dashicons-search" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); color: #666; pointer-events: none;"></span>
			</div>
			<div id="wpshadow-search-results" class="wpshadow-search-results" style="display: none; margin-top: 12px; background: #fff; border: 1px solid #ddd; border-radius: 4px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); max-height: 400px; overflow-y: auto;"></div>
		</div>

		<style>
			.wpshadow-search-bar-container {
				position: relative;
			}
			.wpshadow-feature-search-input:focus {
				outline: none;
				border-color: #2271b1;
				box-shadow: 0 0 0 1px #2271b1;
			}
			.wpshadow-search-result-item {
				padding: 12px 16px;
				border-bottom: 1px solid #f0f0f1;
				cursor: pointer;
				transition: background 0.2s;
			}
			.wpshadow-search-result-item:hover {
				background: #f6f7f7;
			}
			.wpshadow-search-result-item:last-child {
				border-bottom: none;
			}
			.wpshadow-search-result-name {
				font-weight: 600;
				color: #1d2327;
				margin-bottom: 4px;
			}
			.wpshadow-search-result-description {
				font-size: 13px;
				color: #646970;
			}
			.wpshadow-search-result-match {
				font-size: 12px;
				color: #2271b1;
				margin-top: 4px;
			}
			.wpshadow-search-no-results {
				padding: 16px;
				text-align: center;
				color: #646970;
			}
		</style>

		<script>
		jQuery(document).ready(function($) {
			const searchInput = $('#wpshadow-feature-search');
			const searchResults = $('#wpshadow-search-results');
			let searchTimeout;

			searchInput.on('input', function() {
				clearTimeout(searchTimeout);
				const query = $(this).val().trim();

				if (query.length < 2) {
					searchResults.hide();
					return;
				}

				searchTimeout = setTimeout(function() {
					$.ajax({
						url: ajaxurl,
						type: 'POST',
						data: {
							action: 'wpshadow_search_features',
							nonce: '<?php echo esc_js( wp_create_nonce( 'wpshadow_search_features' ) ); ?>',
							query: query
						},
						success: function(response) {
							if (response.success && response.data.features) {
								renderResults(response.data.features, query);
							}
						}
					});
				}, 300);
			});

			function renderResults(features, query) {
				if (features.length === 0) {
					searchResults.html('<div class="wpshadow-search-no-results"><?php esc_html_e( 'No features found matching your search.', 'wpshadow' ); ?></div>').show();
					return;
				}

				let html = '';
				features.forEach(function(feature) {
					const settingsUrl = 'admin.php?page=wpshadow&wpshadow_tab=features&feature=' + feature.id;
					html += '<div class="wpshadow-search-result-item" data-feature-id="' + feature.id + '" data-url="' + settingsUrl + '">';
					html += '<div class="wpshadow-search-result-name">' + escapeHtml(feature.name) + '</div>';
					html += '<div class="wpshadow-search-result-description">' + escapeHtml(feature.description) + '</div>';
					if (feature.matched_alias) {
						html += '<div class="wpshadow-search-result-match">✓ Matched: ' + escapeHtml(feature.matched_alias) + '</div>';
					}
					html += '</div>';
				});

				searchResults.html(html).show();

				$('.wpshadow-search-result-item').on('click', function() {
					window.location.href = $(this).data('url');
				});
			}

			function escapeHtml(text) {
				const map = {
					'&': '&amp;',
					'<': '&lt;',
					'>': '&gt;',
					'"': '&quot;',
					"'": '&#039;'
				};
				return text.replace(/[&<>"']/g, function(m) { return map[m]; });
			}

			// Close results when clicking outside
			$(document).on('click', function(e) {
				if (!$(e.target).closest('.wpshadow-search-bar-container').length) {
					searchResults.hide();
				}
			});

			// Clear search on Escape
			searchInput.on('keydown', function(e) {
				if (e.key === 'Escape') {
					$(this).val('');
					searchResults.hide();
				}
			});
		});
		</script>
		<?php
	}

	/**
	 * AJAX handler for feature search.
	 *
	 * @return void
	 */
	public static function ajax_search_features(): void {
		check_ajax_referer( 'wpshadow_search_features', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'wpshadow' ) ) );
		}

		$query = isset( $_POST['query'] ) ? sanitize_text_field( wp_unslash( $_POST['query'] ) ) : '';

		if ( empty( $query ) ) {
			wp_send_json_success( array( 'features' => array() ) );
		}

		$features = self::search_features( $query );

		wp_send_json_success( array( 'features' => $features ) );
	}

	/**
	 * Search features by query string.
	 *
	 * @param string $query Search query.
	 * @return array<int, array<string, mixed>>
	 */
	public static function search_features( string $query ): array {
		$query = strtolower( trim( $query ) );
		if ( empty( $query ) ) {
			return array();
		}

		$all_features = WPSHADOW_get_features();
		$results      = array();

		foreach ( $all_features as $feature ) {
			$score         = 0;
			$matched_alias = '';

			// Check feature name (highest priority)
			$name = strtolower( $feature->get_name() );
			if ( strpos( $name, $query ) !== false ) {
				$score         += 100;
				$matched_alias  = 'Feature name';
			}

			// Check description
			$description = strtolower( $feature->get_description() );
			if ( strpos( $description, $query ) !== false ) {
				$score += 50;
				if ( empty( $matched_alias ) ) {
					$matched_alias = 'Description';
				}
			}

			// Check aliases
			$aliases = $feature->get_aliases();
			foreach ( $aliases as $alias ) {
				$alias_lower = strtolower( $alias );
				if ( strpos( $alias_lower, $query ) !== false ) {
					$score         += 80;
					$matched_alias  = $alias;
					break;
				}
			}

			// Check sub-features
			$sub_features = $feature->get_sub_features();
			foreach ( $sub_features as $sub_feature_key => $sub_feature_name ) {
				$sub_feature_lower = strtolower( $sub_feature_name );
				if ( strpos( $sub_feature_lower, $query ) !== false ) {
					$score += 30;
					if ( empty( $matched_alias ) ) {
						$matched_alias = 'Sub-feature';
					}
				}
			}

			// Fuzzy match for common search terms
			$score += self::fuzzy_match_score( $query, $feature );

			if ( $score > 0 ) {
				$results[] = array(
					'id'            => $feature->get_id(),
					'name'          => $feature->get_name(),
					'description'   => $feature->get_description(),
					'score'         => $score,
					'matched_alias' => $matched_alias,
				);
			}
		}

		// Sort by score (highest first)
		usort( $results, function ( $a, $b ) {
			return $b['score'] <=> $a['score'];
		} );

		// Limit to top 10 results
		return array_slice( $results, 0, 10 );
	}

	/**
	 * Calculate fuzzy match score for common search terms.
	 *
	 * @param string                       $query   Search query.
	 * @param WPSHADOW_Abstract_Feature $feature Feature object.
	 * @return int
	 */
	private static function fuzzy_match_score( string $query, $feature ): int {
		$score = 0;

		// Common search term mappings
		$fuzzy_mappings = array(
			// Security terms
			'stealing images'     => array( 'hotlink', 'protection', 'bandwidth' ),
			'protect images'      => array( 'hotlink', 'protection' ),
			'stop hotlinking'     => array( 'hotlink', 'protection' ),
			'image theft'         => array( 'hotlink', 'protection' ),
			
			// Performance terms
			'speed up'            => array( 'cache', 'performance', 'optimization', 'minify' ),
			'faster site'         => array( 'cache', 'performance', 'optimization' ),
			'slow site'           => array( 'cache', 'performance', 'optimization' ),
			
			// SEO terms
			'google'              => array( 'seo', 'search', 'ranking' ),
			'search engines'      => array( 'seo', 'search', 'ranking' ),
			'rank higher'         => array( 'seo', 'search', 'ranking' ),
			
			// Content quality terms
			'broken links'        => array( 'broken', 'link', '404', 'dead' ),
			'404 errors'          => array( 'broken', 'link', '404' ),
			'dead links'          => array( 'broken', 'link', '404' ),
			'word formatting'     => array( 'paste', 'cleanup', 'word' ),
			'messy content'       => array( 'paste', 'cleanup', 'formatting' ),
			
			// Accessibility terms
			'accessibility'       => array( 'a11y', 'wcag', 'screen reader', 'contrast', 'alt text' ),
			'screen readers'      => array( 'a11y', 'accessibility', 'aria' ),
			'blind users'         => array( 'a11y', 'accessibility', 'screen reader' ),
			
			// Security terms
			'hacked'              => array( 'security', 'malware', 'hardening', 'integrity' ),
			'virus'               => array( 'security', 'malware', 'scanner' ),
			'compromised'         => array( 'security', 'integrity', 'audit' ),
		);

		$name        = strtolower( $feature->get_name() );
		$description = strtolower( $feature->get_description() );
		$aliases     = array_map( 'strtolower', $feature->get_aliases() );

		foreach ( $fuzzy_mappings as $search_term => $keywords ) {
			if ( strpos( $query, $search_term ) !== false ) {
				foreach ( $keywords as $keyword ) {
					if ( strpos( $name, $keyword ) !== false ||
						 strpos( $description, $keyword ) !== false ||
						 in_array( $keyword, $aliases, true ) ) {
						$score += 40;
						break;
					}
				}
			}
		}

		return $score;
	}

	/**
	 * Enqueue command palette integration assets.
	 *
	 * @return void
	 */
	public static function enqueue_command_palette_integration(): void {
		$screen = get_current_screen();
		
		// Only load on WPShadow pages
		if ( ! $screen || strpos( $screen->id, 'wpshadow' ) === false ) {
			return;
		}

		wp_enqueue_script(
			'wpshadow-command-palette',
			plugins_url( 'assets/js/command-palette.js', WPSHADOW_FILE ),
			array( 'jquery' ),
			WPSHADOW_VERSION,
			true
		);

		wp_localize_script(
			'wpshadow-command-palette',
			'wpshadowCommands',
			array(
				'features' => self::get_features_for_command_palette(),
				'nonce'    => wp_create_nonce( 'wpshadow_commands' ),
			)
		);
	}

	/**
	 * Get features formatted for command palette.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	private static function get_features_for_command_palette(): array {
		$all_features = WPSHADOW_get_features();
		$commands     = array();

		foreach ( $all_features as $feature ) {
			$commands[] = array(
				'id'          => $feature->get_id(),
				'name'        => $feature->get_name(),
				'description' => $feature->get_description(),
				'aliases'     => $feature->get_aliases(),
				'url'         => admin_url( 'admin.php?page=wpshadow&wpshadow_tab=features&feature=' . $feature->get_id() ),
				'category'    => $feature->get_widget_label(),
			);
		}

		return $commands;
	}

	/**
	 * Register WordPress Command Palette commands.
	 *
	 * @return void
	 */
	public static function register_command_palette_commands(): void {
		// This hook is available in WordPress 6.5+
		if ( ! function_exists( 'wp_command_palette_register_command' ) ) {
			return;
		}

		$all_features = WPSHADOW_get_features();

		foreach ( $all_features as $feature ) {
			$aliases = $feature->get_aliases();
			$keywords = array_merge( array( $feature->get_name() ), $aliases );

			wp_command_palette_register_command(
				array(
					'name'        => sprintf( 'WPShadow: %s', $feature->get_name() ),
					'description' => $feature->get_description(),
					'category'    => 'WPShadow Features',
					'keywords'    => $keywords,
					'callback'    => function () use ( $feature ) {
						wp_safe_redirect( admin_url( 'admin.php?page=wpshadow&wpshadow_tab=features&feature=' . $feature->get_id() ) );
						exit;
					},
				)
			);
		}
	}
}

// Initialize on admin pages
if ( is_admin() ) {
	WPSHADOW_Feature_Search::init();
}
