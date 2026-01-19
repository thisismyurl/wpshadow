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
	 * User meta keys and limits for search memory.
	 */
	private const SEARCH_HISTORY_META = 'wpshadow_search_history';
	private const ACCESSED_PAGES_META = 'wpshadow_accessed_pages';
	private const MAX_HISTORY_ITEMS   = 10;
	private const MAX_ACCESSED_ITEMS  = 6;

	/**
	 * Default suggested feature identifiers.
	 */
	private const DEFAULT_SUGGESTIONS = array(
		'page-cache',
		'head-cleanup',
		'security-hardening',
		'jquery-cleanup',
		'database-cleanup',
		'image-optimizer',
		'brute-force-protection',
		'cdn-integration',
	);

	/**
	 * Prevent duplicate rendering on the same request.
	 */
	private static bool $rendered = false;

	/**
	 * Initialize search functionality.
	 *
	 * @return void
	 */
	public static function init(): void {
		// AJAX endpoints for search, tracking, and clearing history.
		add_action( 'wp_ajax_wpshadow_search_features', array( __CLASS__, 'ajax_search_features' ) );
		add_action( 'wp_ajax_wpshadow_track_feature_access', array( __CLASS__, 'ajax_track_feature_access' ) );
		add_action( 'wp_ajax_wpshadow_clear_accessed_pages', array( __CLASS__, 'ajax_clear_accessed_pages' ) );

		// Render the search widget on all WPShadow admin screens.
		add_action( 'all_admin_notices', array( __CLASS__, 'render_search_bar' ) );
		add_action( 'wpshadow_dashboard_header', array( __CLASS__, 'render_search_bar' ) );

		// Enqueue assets and register commands.
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_admin_assets' ) );
		add_action( 'admin_init', array( __CLASS__, 'register_command_palette_commands' ) );
	}

	/**
	 * Render search bar in dashboard header.
	 *
	 * @return void
	 */
	public static function render_search_bar(): void {
		if ( self::$rendered ) {
			return;
		}

		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( ! $screen || false === strpos( $screen->id, 'wpshadow' ) ) {
			return;
		}

		self::$rendered = true;
		$suggestions = self::get_suggestion_payload();
		$accessed    = self::get_accessed_payload();
		?>
		<div class="wpshadow-feature-search-widget" role="region" aria-label="<?php esc_attr_e( 'WPShadow feature search', 'wpshadow' ); ?>">
			<div class="wpshadow-feature-search-header">
				<div>
					<p class="wpshadow-feature-search-title"><?php esc_html_e( 'Find features fast', 'wpshadow' ); ?></p>
					<p class="wpshadow-feature-search-subtitle"><?php esc_html_e( 'Search, jump, or revisit frequently used tools without leaving the page.', 'wpshadow' ); ?></p>
				</div>
				<div class="wpshadow-feature-search-actions">
					<span class="wpshadow-feature-search-kb">Ctrl / Cmd + K</span>
				</div>
			</div>

			<div class="wpshadow-feature-search-body">
				<label for="wpshadow-feature-search-input" class="screen-reader-text"><?php esc_html_e( 'Search features', 'wpshadow' ); ?></label>
				<div class="wpshadow-feature-search-input-wrap">
					<span class="dashicons dashicons-search" aria-hidden="true"></span>
					<input
						type="search"
						id="wpshadow-feature-search-input"
						class="wpshadow-feature-search-input"
						placeholder="<?php esc_attr_e( 'Search features...', 'wpshadow' ); ?>"
						autocomplete="off"
						aria-autocomplete="list"
						aria-controls="wpshadow-search-results"
						aria-expanded="false"
					/>
					<button type="button" class="wpshadow-feature-search-clear" aria-label="<?php esc_attr_e( 'Clear search', 'wpshadow' ); ?>">
						<span class="dashicons dashicons-no"></span>
					</button>
				</div>
				<div id="wpshadow-search-results" class="wpshadow-feature-search-results" role="listbox" aria-label="<?php esc_attr_e( 'Search results', 'wpshadow' ); ?>"></div>
			</div>

			<?php if ( ! empty( $suggestions ) ) : ?>
				<div class="wpshadow-feature-search-section">
					<div class="wpshadow-feature-search-section-heading">
						<span class="wpshadow-feature-search-section-title"><?php esc_html_e( 'Suggested', 'wpshadow' ); ?></span>
						<span class="wpshadow-feature-search-section-hint"><?php esc_html_e( 'Based on your recent searches', 'wpshadow' ); ?></span>
					</div>
					<div class="wpshadow-feature-search-chips" role="list">
						<?php foreach ( $suggestions as $feature ) : ?>
							<button type="button" class="wpshadow-feature-chip" role="listitem" data-feature-id="<?php echo esc_attr( $feature['id'] ); ?>" data-url="<?php echo esc_url( $feature['url'] ); ?>">
								<span class="dashicons <?php echo esc_attr( $feature['icon'] ); ?>" aria-hidden="true"></span>
								<span class="wpshadow-feature-chip-label"><?php echo esc_html( $feature['name'] ); ?></span>
							</button>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endif; ?>

			<?php if ( ! empty( $accessed ) ) : ?>
				<div class="wpshadow-feature-search-section">
					<div class="wpshadow-feature-search-section-heading">
						<span class="wpshadow-feature-search-section-title"><?php esc_html_e( 'Commonly accessed', 'wpshadow' ); ?></span>
						<button type="button" class="wpshadow-feature-search-clear-accessed" aria-label="<?php esc_attr_e( 'Clear commonly accessed list', 'wpshadow' ); ?>">
							<span class="dashicons dashicons-no-alt"></span>
						</button>
					</div>
					<ul class="wpshadow-feature-search-accessed" role="list">
						<?php foreach ( $accessed as $feature ) : ?>
							<li class="wpshadow-feature-search-accessed-item" role="listitem">
								<a href="<?php echo esc_url( $feature['url'] ); ?>" class="wpshadow-feature-search-accessed-link" data-feature-id="<?php echo esc_attr( $feature['id'] ); ?>" data-url="<?php echo esc_url( $feature['url'] ); ?>">
									<span class="dashicons <?php echo esc_attr( $feature['icon'] ); ?>" aria-hidden="true"></span>
									<span class="wpshadow-feature-search-accessed-name"><?php echo esc_html( $feature['name'] ); ?></span>
									<span class="wpshadow-feature-search-accessed-count"><?php echo esc_html( (string) ( $feature['count'] ?? 0 ) ); ?></span>
								</a>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Build data for suggestions based on history or defaults.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	private static function get_suggestion_payload(): array {
		$all_features = WPSHADOW_Feature_Registry::get_features();
		$ids          = self::get_search_suggestions();
		$payload      = array();

		foreach ( $ids as $feature_id ) {
			if ( ! isset( $all_features[ $feature_id ] ) ) {
				continue;
			}

			$payload[] = self::normalize_feature_data( $feature_id, $all_features[ $feature_id ] );
		}

		// Fallback: seed with the first few known features when history is empty.
		if ( empty( $payload ) ) {
			foreach ( $all_features as $feature_id => $feature ) {
				$payload[] = self::normalize_feature_data( $feature_id, $feature );
				if ( count( $payload ) >= self::MAX_HISTORY_ITEMS ) {
					break;
				}
			}
		}

		return array_slice( $payload, 0, self::MAX_HISTORY_ITEMS );
	}

	/**
	 * Build data for commonly accessed features.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	private static function get_accessed_payload(): array {
		$accessed     = self::get_commonly_accessed_pages();
		$payload      = array();
		$all_features = WPSHADOW_Feature_Registry::get_features();

		foreach ( $accessed as $feature_id => $count ) {
			if ( ! isset( $all_features[ $feature_id ] ) ) {
				continue;
			}

			$data          = self::normalize_feature_data( $feature_id, $all_features[ $feature_id ] );
			$data['count'] = (int) $count;
			$payload[]     = $data;
		}

		return $payload;
	}

	/**
	 * Normalize feature data to a consistent array structure.
	 *
	 * @param string                                   $feature_id Feature identifier.
	 * @param array<string, mixed>|object|null         $feature    Feature array or object.
	 * @return array{id:string,name:string,description:string,icon:string,url:string}
	 */
	private static function normalize_feature_data( string $feature_id, $feature ): array {
		if ( is_object( $feature ) ) {
			return array(
				'id'          => $feature_id,
				'name'        => method_exists( $feature, 'get_name' ) ? (string) $feature->get_name() : $feature_id,
				'description' => method_exists( $feature, 'get_description' ) ? (string) $feature->get_description() : '',
				'icon'        => method_exists( $feature, 'get_icon' ) ? (string) $feature->get_icon() : 'dashicons-admin-generic',
				'url'         => method_exists( $feature, 'get_details_url' ) ? (string) $feature->get_details_url() : self::build_feature_url( $feature_id ),
			);
		}

		$name        = isset( $feature['name'] ) ? (string) $feature['name'] : ucfirst( str_replace( '-', ' ', $feature_id ) );
		$description = isset( $feature['description'] ) ? (string) $feature['description'] : '';
		$icon        = isset( $feature['icon'] ) ? (string) $feature['icon'] : 'dashicons-admin-generic';
		$url         = isset( $feature['details_url'] ) ? (string) $feature['details_url'] : '';

		if ( '' === $url ) {
			$url = self::build_feature_url( $feature_id );
		}

		return array(
			'id'          => $feature_id,
			'name'        => $name,
			'description' => $description,
			'icon'        => $icon,
			'url'         => $url,
		);
	}

	/**
	 * Build a feature details URL.
	 *
	 * @param string $feature_id Feature identifier.
	 * @return string
	 */
	private static function build_feature_url( string $feature_id ): string {
		return admin_url( 'admin.php?page=wpshadow&wpshadow_tab=features&feature=' . rawurlencode( $feature_id ) );
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
		$results = array();

		if ( '' === $query ) {
			$results = self::get_suggestion_payload();
		} else {
			$results = self::search_features( $query );
		}

		wp_send_json_success(
			array(
				'features'    => $results, // Backward compatibility for existing UI.
				'results'     => $results,
				'suggestions' => self::get_suggestion_payload(),
				'accessed'    => self::get_accessed_payload(),
			)
		);
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

		$all_features = WPSHADOW_Feature_Registry::get_features();
		$results      = array();

		foreach ( $all_features as $feature_id => $feature ) {
			$score         = 0;
			$matched_alias = '';
			$feature_key   = $feature['id'] ?? $feature_id;
			$feature_data  = self::normalize_feature_data( $feature_key, $feature );

			// Check feature name (highest priority)
			$name = strtolower( $feature['name'] ?? $feature_data['name'] );
			if ( strpos( $name, $query ) !== false ) {
				$score         += 100;
				$matched_alias  = 'Feature name';
			}

			// Check description
			$description = strtolower( $feature['description'] ?? '' );
			if ( strpos( $description, $query ) !== false ) {
				$score += 50;
				if ( empty( $matched_alias ) ) {
					$matched_alias = 'Description';
				}
			}

			// Check aliases
			$aliases = $feature['aliases'] ?? array();
			foreach ( $aliases as $alias ) {
				$alias_lower = strtolower( $alias );
				if ( strpos( $alias_lower, $query ) !== false ) {
					$score         += 80;
					$matched_alias  = $alias;
					break;
				}
			}

			// Check sub-features
			$sub_features = $feature['sub_features'] ?? array();
			foreach ( $sub_features as $sub_feature_key => $sub_feature_name ) {
				// Handle both string values and array values (with 'name' key)
				$sub_feature_text = is_array( $sub_feature_name ) ? ( $sub_feature_name['name'] ?? '' ) : $sub_feature_name;
				$sub_feature_lower = strtolower( $sub_feature_text );
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
					'id'            => $feature_data['id'],
					'name'          => $feature_data['name'],
					'description'   => $feature_data['description'],
					'url'           => $feature_data['url'],
					'icon'          => $feature_data['icon'],
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

		$name        = strtolower( $feature['name'] ?? '' );
		$description = strtolower( $feature['description'] ?? '' );
		$aliases     = array_map( 'strtolower', $feature['aliases'] ?? array() );

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
	 * Get suggested feature identifiers for the current user.
	 *
	 * @return array<int, string>
	 */
	public static function get_search_suggestions(): array {
		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			return self::DEFAULT_SUGGESTIONS;
		}

		$history = get_user_meta( $user_id, self::SEARCH_HISTORY_META, true );
		if ( ! empty( $history ) && is_array( $history ) ) {
			return array_values( $history );
		}

		$all_features  = WPSHADOW_Feature_Registry::get_features();
		$fallback_ids  = array_keys( $all_features );
		$combined_list = array_unique( array_merge( self::DEFAULT_SUGGESTIONS, $fallback_ids ) );

		return array_slice( $combined_list, 0, self::MAX_HISTORY_ITEMS );
	}

	/**
	 * Get per-user commonly accessed feature counts.
	 *
	 * @return array<string, int>
	 */
	public static function get_commonly_accessed_pages(): array {
		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			return array();
		}

		$accessed = get_user_meta( $user_id, self::ACCESSED_PAGES_META, true );
		if ( empty( $accessed ) || ! is_array( $accessed ) ) {
			return array();
		}

		arsort( $accessed );

		return array_slice( $accessed, 0, self::MAX_ACCESSED_ITEMS, true );
	}

	/**
	 * Track a feature access for the current user.
	 *
	 * @param string $feature_id Feature identifier.
	 * @return void
	 */
	public static function track_feature_access( string $feature_id ): void {
		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			return;
		}

		$accessed = get_user_meta( $user_id, self::ACCESSED_PAGES_META, true );
		if ( ! is_array( $accessed ) ) {
			$accessed = array();
		}

		$accessed[ $feature_id ] = isset( $accessed[ $feature_id ] ) ? ( (int) $accessed[ $feature_id ] + 1 ) : 1;

		update_user_meta( $user_id, self::ACCESSED_PAGES_META, $accessed );
	}

	/**
	 * Add a feature to the user's search history.
	 *
	 * @param string $feature_id Feature identifier.
	 * @return void
	 */
	public static function add_to_search_history( string $feature_id ): void {
		$user_id = get_current_user_id();
		if ( ! $user_id ) {
			return;
		}

		$history = get_user_meta( $user_id, self::SEARCH_HISTORY_META, true );
		if ( ! is_array( $history ) ) {
			$history = array();
		}

		$history = array_diff( $history, array( $feature_id ) );
		array_unshift( $history, $feature_id );
		$history = array_slice( $history, 0, self::MAX_HISTORY_ITEMS );

		update_user_meta( $user_id, self::SEARCH_HISTORY_META, $history );
	}

	/**
	 * AJAX: track a feature access event.
	 *
	 * @return void
	 */
	public static function ajax_track_feature_access(): void {
		check_ajax_referer( 'wpshadow_search_features', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'wpshadow' ) ) );
		}

		$feature_id = isset( $_POST['feature_id'] ) ? sanitize_key( (string) $_POST['feature_id'] ) : '';

		if ( '' === $feature_id ) {
			wp_send_json_error( array( 'message' => __( 'That feature does not exist.', 'wpshadow' ) ) );
		}

		self::track_feature_access( $feature_id );
		self::add_to_search_history( $feature_id );

		wp_send_json_success( array( 'accessed' => self::get_accessed_payload() ) );
	}

	/**
	 * AJAX: clear the commonly accessed list for the current user.
	 *
	 * @return void
	 */
	public static function ajax_clear_accessed_pages(): void {
		check_ajax_referer( 'wpshadow_search_features', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'wpshadow' ) ) );
		}

		$user_id = get_current_user_id();

		if ( $user_id ) {
			delete_user_meta( $user_id, self::ACCESSED_PAGES_META );
		}

		wp_send_json_success( array( 'accessed' => array() ) );
	}

	/**
	 * Enqueue command palette integration assets.
	 *
	 * @return void
	 */
	public static function enqueue_admin_assets(): void {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

		if ( ! $screen || false === strpos( $screen->id, 'wpshadow' ) ) {
			return;
		}

		wp_enqueue_style(
			'wpshadow-feature-search',
			plugins_url( 'assets/css/feature-search.css', WPSHADOW_FILE ),
			array(),
			WPSHADOW_VERSION
		);

		wp_enqueue_script(
			'wpshadow-feature-search',
			plugins_url( 'assets/js/feature-search.js', WPSHADOW_FILE ),
			array( 'jquery' ),
			WPSHADOW_VERSION,
			true
		);

		wp_localize_script(
			'wpshadow-feature-search',
			'wpshadowFeatureSearch',
			array(
				'ajaxUrl'     => admin_url( 'admin-ajax.php' ),
				'nonce'       => wp_create_nonce( 'wpshadow_search_features' ),
				'suggestions' => self::get_suggestion_payload(),
				'accessed'    => self::get_accessed_payload(),
				'strings'     => array(
					'noResults'   => __( 'No features found', 'wpshadow' ),
					'suggested'   => __( 'Suggested', 'wpshadow' ),
					'common'      => __( 'Commonly accessed', 'wpshadow' ),
					'clear'       => __( 'Clear list', 'wpshadow' ),
					'placeholder' => __( 'Search features...', 'wpshadow' ),
				),
			)
		);

		self::enqueue_command_palette_assets();
	}

	/**
	 * Enqueue command palette integration assets.
	 *
	 * @return void
	 */
	private static function enqueue_command_palette_assets(): void {
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
		$all_features = WPSHADOW_Feature_Registry::get_features();
		$commands     = array();

		foreach ( $all_features as $feature_id => $feature ) {
			$commands[] = array(
				'id'          => $feature['id'] ?? $feature_id,
				'name'        => $feature['name'] ?? '',
				'description' => $feature['description'] ?? '',
				'aliases'     => $feature['aliases'] ?? array(),
				'url'         => self::build_feature_url( $feature['id'] ?? $feature_id ),
				'category'    => $feature['widget_label'] ?? '',
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

		$all_features = WPSHADOW_Feature_Registry::get_features();

		foreach ( $all_features as $feature_id => $feature ) {
			$name        = $feature['name'] ?? $feature_id;
			$description = $feature['description'] ?? '';
			$aliases     = $feature['aliases'] ?? array();
			$keywords    = array_merge( array( $name ), $aliases );
			$target_url  = self::build_feature_url( $feature_id );

			wp_command_palette_register_command(
				array(
					'name'        => sprintf( 'WPShadow: %s', $name ),
					'description' => $description,
					'category'    => 'WPShadow Features',
					'keywords'    => $keywords,
					'callback'    => function () use ( $target_url ): void {
						wp_safe_redirect( $target_url );
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
