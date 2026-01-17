<?php
/**
 * Feature Search System
 *
 * Provides intelligent feature search with history tracking and predictive suggestions.
 *
 * @package WPShadow\CoreSupport
 * @since 1.2601.78000
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Feature Search Manager
 */
class WPSHADOW_Feature_Search {

	/**
	 * User meta key for search history.
	 */
	const SEARCH_HISTORY_META = 'wpshadow_search_history';

	/**
	 * User meta key for commonly accessed pages.
	 */
	const ACCESSED_PAGES_META = 'wpshadow_accessed_pages';

	/**
	 * Maximum search history items.
	 */
	const MAX_HISTORY_ITEMS = 10;

	/**
	 * Maximum commonly accessed items.
	 */
	const MAX_ACCESSED_ITEMS = 6;

	/**
	 * Default suggested features (shown on first use).
	 */
	const DEFAULT_SUGGESTIONS = array(
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
	 * Initialize the feature search system.
	 *
	 * @return void
	 */
	public static function init(): void {
		add_action( 'wp_ajax_wpshadow_search_features', array( __CLASS__, 'ajax_search_features' ) );
		add_action( 'wp_ajax_wpshadow_track_feature_access', array( __CLASS__, 'ajax_track_feature_access' ) );
		add_action( 'wp_ajax_wpshadow_clear_accessed_pages', array( __CLASS__, 'ajax_clear_accessed_pages' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );
	}

	/**
	 * Normalize feature data to a consistent array structure.
	 *
	 * @param string                                   $feature_id Feature identifier.
	 * @param array<string, mixed>|object|null         $feature    Feature array or object.
	 * @return array{id:string,name:string,description:string,icon:string,url:string}
	 */
	private static function normalize_feature_data( string $feature_id, $feature ): array {
		// Object shape (older callers expected object methods).
		if ( is_object( $feature ) ) {
			return array(
				'id'          => $feature_id,
				'name'        => method_exists( $feature, 'get_name' ) ? (string) $feature->get_name() : $feature_id,
				'description' => method_exists( $feature, 'get_description' ) ? (string) $feature->get_description() : '',
				'icon'        => method_exists( $feature, 'get_icon' ) ? (string) $feature->get_icon() : 'dashicons-admin-generic',
				'url'         => method_exists( $feature, 'get_details_url' ) ? (string) $feature->get_details_url() : '',
			);
		}

		// Array shape (current registry output).
		$name        = isset( $feature['name'] ) ? (string) $feature['name'] : ucfirst( str_replace( '-', ' ', $feature_id ) );
		$description = isset( $feature['description'] ) ? (string) $feature['description'] : '';
		$icon        = isset( $feature['icon'] ) ? (string) $feature['icon'] : 'dashicons-admin-generic';
		$url         = isset( $feature['details_url'] ) ? (string) $feature['details_url'] : '';

		if ( '' === $url && class_exists( '\\WPShadow\\CoreSupport\\WPSHADOW_Feature_Details_Page' ) ) {
			$url = WPSHADOW_Feature_Details_Page::get_feature_url( $feature_id );
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
	 * Enqueue search assets.
	 *
	 * @param string $hook Current admin page hook.
	 * @return void
	 */
	public static function enqueue_assets( string $hook ): void {
		if ( 'toplevel_page_wpshadow' !== $hook && false === strpos( $hook, 'wpshadow' ) ) {
			return;
		}

		wp_enqueue_style(
			'wpshadow-feature-search',
			WPSHADOW_URL . 'assets/css/feature-search.css',
			array(),
			WPSHADOW_VERSION
		);

		wp_enqueue_script(
			'wpshadow-feature-search',
			WPSHADOW_URL . 'assets/js/feature-search.js',
			array( 'jquery' ),
			WPSHADOW_VERSION,
			true
		);

		wp_localize_script(
			'wpshadow-feature-search',
			'wpshadowFeatureSearch',
			array(
				'ajaxUrl'     => admin_url( 'admin-ajax.php' ),
				'nonce'       => wp_create_nonce( 'wpshadow_feature_search' ),
				'searchLabel' => __( 'Search features...', 'plugin-wpshadow' ),
				'noResults'   => __( 'No features found', 'plugin-wpshadow' ),
			)
		);
	}

	/**
	 * Render the feature search component.
	 *
	 * @return void
	 */
	public static function render_search_component(): void {
		$suggestions      = self::get_search_suggestions();
		$accessed_pages   = self::get_commonly_accessed_pages();
		$has_accessed     = ! empty( $accessed_pages );

		?>
		<div class="wpshadow-feature-search-wrapper">
			<div class="wpshadow-feature-search-container">
				<label for="wpshadow-feature-search-input" class="screen-reader-text">
					<?php esc_html_e( 'Search features', 'plugin-wpshadow' ); ?>
				</label>
				<input 
					type="text" 
					id="wpshadow-feature-search-input" 
					class="wpshadow-feature-search-input" 
					placeholder="<?php esc_attr_e( 'Search features...', 'plugin-wpshadow' ); ?>"
					autocomplete="off"
					aria-autocomplete="list"
					aria-controls="wpshadow-search-results"
					aria-expanded="false"
				/>
				<span class="dashicons dashicons-search wpshadow-search-icon"></span>
				
				<div id="wpshadow-search-results" class="wpshadow-search-results" role="listbox" aria-label="<?php esc_attr_e( 'Search results', 'plugin-wpshadow' ); ?>"></div>
			</div>

			<?php if ( $has_accessed ) : ?>
			<div class="wpshadow-commonly-accessed">
				<div class="wpshadow-commonly-accessed-header">
					<h3><?php esc_html_e( 'Commonly Accessed', 'plugin-wpshadow' ); ?></h3>
					<button 
						type="button" 
						class="wpshadow-clear-accessed" 
						aria-label="<?php esc_attr_e( 'Clear commonly accessed list', 'plugin-wpshadow' ); ?>"
						title="<?php esc_attr_e( 'Clear list', 'plugin-wpshadow' ); ?>"
					>
						<span class="dashicons dashicons-no-alt"></span>
					</button>
				</div>
				<ul class="wpshadow-commonly-accessed-list">
					<?php foreach ( $accessed_pages as $feature_id => $count ) : ?>
						<?php
						$feature = WPSHADOW_Feature_Registry::get_feature( $feature_id );
						if ( ! $feature ) {
							continue;
						}
						$feature_data = self::normalize_feature_data( $feature_id, $feature );
						$name         = $feature_data['name'];
						$url          = $feature_data['url'];
						$icon         = $feature_data['icon'];
						?>
						<li>
							<a href="<?php echo esc_url( $url ); ?>" class="wpshadow-accessed-link">
								<span class="dashicons <?php echo esc_attr( $icon ); ?>"></span>
								<?php echo esc_html( $name ); ?>
								<span class="wpshadow-access-count"><?php echo esc_html( $count ); ?></span>
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
	 * Get search suggestions for the user.
	 *
	 * Returns search history if available, otherwise default suggestions.
	 *
	 * @return array Array of feature IDs.
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

		return self::DEFAULT_SUGGESTIONS;
	}

	/**
	 * Get commonly accessed pages for the user.
	 *
	 * @return array Array of feature_id => access_count pairs, sorted by count.
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

		// Sort by count descending.
		arsort( $accessed );

		// Return top 6.
		return array_slice( $accessed, 0, self::MAX_ACCESSED_ITEMS, true );
	}

	/**
	 * Track a feature access.
	 *
	 * @param string $feature_id Feature ID.
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

		if ( isset( $accessed[ $feature_id ] ) ) {
			++$accessed[ $feature_id ];
		} else {
			$accessed[ $feature_id ] = 1;
		}

		update_user_meta( $user_id, self::ACCESSED_PAGES_META, $accessed );
	}

	/**
	 * Add a feature to search history.
	 *
	 * @param string $feature_id Feature ID.
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

		// Remove if already exists.
		$history = array_diff( $history, array( $feature_id ) );

		// Add to beginning.
		array_unshift( $history, $feature_id );

		// Limit to max items.
		$history = array_slice( $history, 0, self::MAX_HISTORY_ITEMS );

		update_user_meta( $user_id, self::SEARCH_HISTORY_META, $history );
	}

	/**
	 * AJAX handler for searching features.
	 *
	 * @return void
	 */
	public static function ajax_search_features(): void {
		check_ajax_referer( 'wpshadow_feature_search', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'plugin-wpshadow' ) ) );
		}

		$query = isset( $_POST['query'] ) ? sanitize_text_field( wp_unslash( $_POST['query'] ) ) : '';
		
		if ( empty( $query ) ) {
			// Return suggestions.
			$suggestions = self::get_search_suggestions();
			$results     = array();

			foreach ( $suggestions as $feature_id ) {
				$feature = WPSHADOW_Feature_Registry::get_feature( $feature_id );
				if ( $feature ) {
					$feature_data = self::normalize_feature_data( $feature_id, $feature );
					$results[]    = $feature_data;
				}
			}

			wp_send_json_success( array( 'results' => $results ) );
		}

		// Search features.
		$all_features = WPSHADOW_Feature_Registry::get_features();
		$results      = array();

		foreach ( $all_features as $feature_id => $feature ) {
			$feature_data = self::normalize_feature_data( $feature_id, $feature );
			$name         = $feature_data['name'];
			$description  = $feature_data['description'];

			// Simple search match.
			if ( false !== stripos( $name, $query ) || false !== stripos( $description, $query ) || false !== stripos( $feature_id, $query ) ) {
				$results[] = array(
					'id'          => $feature_id,
					'name'        => $name,
					'description' => $description,
					'url'         => $feature_data['url'],
					'icon'        => $feature_data['icon'],
					'relevance'   => false !== stripos( $name, $query ) ? 10 : 5, // Name match = higher relevance.
				);
			}
		}

		// Sort by relevance.
		usort(
			$results,
			function ( $a, $b ) {
				return $b['relevance'] - $a['relevance'];
			}
		);

		// Limit to 10 results.
		$results = array_slice( $results, 0, 10 );

		wp_send_json_success( array( 'results' => $results ) );
	}

	/**
	 * AJAX handler for tracking feature access.
	 *
	 * @return void
	 */
	public static function ajax_track_feature_access(): void {
		check_ajax_referer( 'wpshadow_feature_search', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'plugin-wpshadow' ) ) );
		}

		$feature_id = isset( $_POST['feature_id'] ) ? sanitize_key( $_POST['feature_id'] ) : '';

		if ( empty( $feature_id ) ) {
			wp_send_json_error( array( 'message' => __( 'That feature doesn\'t exist.', 'plugin-wpshadow' ) ) );
		}

		self::track_feature_access( $feature_id );
		self::add_to_search_history( $feature_id );

		wp_send_json_success();
	}

	/**
	 * AJAX handler for clearing accessed pages list.
	 *
	 * @return void
	 */
	public static function ajax_clear_accessed_pages(): void {
		check_ajax_referer( 'wpshadow_feature_search', 'nonce' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => __( 'Insufficient permissions.', 'plugin-wpshadow' ) ) );
		}

		$user_id = get_current_user_id();
		if ( $user_id ) {
			delete_user_meta( $user_id, self::ACCESSED_PAGES_META );
		}

		wp_send_json_success();
	}
}
