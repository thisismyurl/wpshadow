<?php
/**
 * KB Article Manager
 *
 * Manages knowledge base articles, tracks article availability,
 * and provides article suggestions based on diagnostic findings.
 *
 * @package    WPShadow
 * @subpackage Content
 * @since      1.2604.0100
 */

declare(strict_types=1);

namespace WPShadow\Content;

use WPShadow\Core\UTM_Link_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * KB Article Manager Class
 *
 * Handles KB article operations and suggestions.
 *
 * @since 1.2604.0100
 */
class KB_Article_Manager {

	/**
	 * Article mapping: diagnostic slug => KB slug
	 *
	 * @var array
	 */
	private static $article_map = array();

	/**
	 * Initialize the manager
	 *
	 * @since  1.2604.0100
	 * @return void
	 */
	public static function init() {
		self::load_article_map();
		add_action( 'admin_notices', array( __CLASS__, 'show_learning_tips' ) );
	}

	/**
	 * Load article mapping from diagnostics
	 *
	 * @since  1.2604.0100
	 * @return void
	 */
	private static function load_article_map() {
		// This will be populated by scanning diagnostics
		// For now, we'll define core mappings
		self::$article_map = array(
			// Admin diagnostics
			'admin-duplicate-plugins'           => 'duplicate-plugins',
			'admin-inactive-plugins'            => 'inactive-plugins',
			'admin-old-plugins'                 => 'outdated-plugins',
			'admin-excessive-plugins'           => 'plugin-performance',
			'admin-theme-updates'               => 'theme-updates',
			'admin-plugin-updates'              => 'plugin-updates',
			'admin-wordpress-updates'           => 'wordpress-updates',
			'admin-unused-themes'               => 'unused-themes',
			'admin-excessive-menu-items'        => 'menu-optimization',
			'admin-deactivated-plugins'         => 'plugin-management',
			
			// Security diagnostics
			'security-file-permissions'         => 'file-permissions',
			'security-wp-config-location'       => 'wp-config-security',
			'security-database-prefix'          => 'database-prefix',
			'security-xmlrpc-enabled'           => 'xmlrpc-security',
			'security-file-editing'             => 'file-editing-security',
			'security-wp-version-exposed'       => 'version-hiding',
			'security-debug-mode'               => 'debug-mode',
			'security-https-required'           => 'https-security',
			'security-ssl-verification'         => 'ssl-certificate',
			'security-outdated-php'             => 'php-version',
			
			// Performance diagnostics
			'performance-memory-limit'          => 'memory-limit',
			'performance-max-execution-time'    => 'execution-time',
			'performance-opcache'               => 'opcache-optimization',
			'performance-gzip'                  => 'gzip-compression',
			'performance-object-cache'          => 'object-caching',
			'performance-page-cache'            => 'page-caching',
			'performance-image-optimization'    => 'image-optimization',
			'performance-cdn'                   => 'cdn-setup',
			'performance-lazy-loading'          => 'lazy-loading',
			'performance-database-optimization' => 'database-optimization',
			
			// SEO diagnostics
			'seo-meta-description'              => 'meta-descriptions',
			'seo-alt-text'                      => 'image-alt-text',
			'seo-xml-sitemap'                   => 'xml-sitemaps',
			'seo-robots-txt'                    => 'robots-txt',
			'seo-permalink-structure'           => 'permalink-structure',
			'seo-404-errors'                    => '404-optimization',
			'seo-broken-links'                  => 'broken-links',
			'seo-duplicate-content'             => 'duplicate-content',
			'seo-schema-markup'                 => 'schema-markup',
			'seo-page-speed'                    => 'page-speed-seo',
		);

		/**
		 * Filter article mapping
		 *
		 * Allows pro modules to add their own mappings
		 *
		 * @since 1.2604.0100
		 *
		 * @param array $article_map Diagnostic slug => KB slug mapping
		 */
		self::$article_map = apply_filters( 'wpshadow_kb_article_map', self::$article_map );
	}

	/**
	 * Get KB link for diagnostic
	 *
	 * @since  1.2604.0100
	 * @param  string $diagnostic_slug Diagnostic slug.
	 * @param  string $campaign        Campaign name for UTM tracking.
	 * @return string|null KB article URL or null if no mapping exists.
	 */
	public static function get_article_link( string $diagnostic_slug, string $campaign = 'diagnostic' ): ?string {
		if ( empty( self::$article_map ) ) {
			self::load_article_map();
		}

		if ( ! isset( self::$article_map[ $diagnostic_slug ] ) ) {
			return null;
		}

		$kb_slug = self::$article_map[ $diagnostic_slug ];
		return UTM_Link_Manager::kb_link( $kb_slug, $campaign );
	}

	/**
	 * Get training link for diagnostic
	 *
	 * @since  1.2604.0100
	 * @param  string $diagnostic_slug Diagnostic slug.
	 * @param  string $campaign        Campaign name for UTM tracking.
	 * @return string|null Training URL or null if no mapping exists.
	 */
	public static function get_training_link( string $diagnostic_slug, string $campaign = 'training' ): ?string {
		if ( empty( self::$article_map ) ) {
			self::load_article_map();
		}

		if ( ! isset( self::$article_map[ $diagnostic_slug ] ) ) {
			return null;
		}

		$kb_slug = self::$article_map[ $diagnostic_slug ];
		return UTM_Link_Manager::academy_link( $kb_slug, $campaign );
	}

	/**
	 * Get article suggestions based on findings
	 *
	 * @since  1.2604.0100
	 * @param  array $findings Array of diagnostic findings.
	 * @return array Array of article suggestions with URLs.
	 */
	public static function get_article_suggestions( array $findings ): array {
		$suggestions = array();

		foreach ( $findings as $finding ) {
			$slug = $finding['id'] ?? '';
			if ( empty( $slug ) ) {
				continue;
			}

			$kb_link      = self::get_article_link( $slug, 'suggestion' );
			$training_link = self::get_training_link( $slug, 'suggestion' );

			if ( $kb_link || $training_link ) {
				$suggestions[] = array(
					'diagnostic'    => $slug,
					'title'         => $finding['title'] ?? '',
					'severity'      => $finding['severity'] ?? 'medium',
					'kb_link'       => $kb_link,
					'training_link' => $training_link,
				);
			}
		}

		return $suggestions;
	}

	/**
	 * Show contextual learning tips on admin pages
	 *
	 * @since  1.2604.0100
	 * @return void
	 */
	public static function show_learning_tips() {
		$screen = get_current_screen();
		
		// Only show on WPShadow pages
		if ( ! $screen || strpos( $screen->id, 'wpshadow' ) === false ) {
			return;
		}

		// Check if user has dismissed tips
		$user_id = get_current_user_id();
		if ( get_user_meta( $user_id, 'wpshadow_hide_learning_tips', true ) ) {
			return;
		}

		// Check login frequency (show only on 2nd or 3rd login in a week)
		if ( ! self::should_show_tip_based_on_login_frequency( $user_id ) ) {
			return;
		}

		// Get contextual tip based on current page and user role
		$tip = self::get_contextual_tip( $screen->id, $user_id );
		if ( ! $tip ) {
			return;
		}

		?>
		<div class="notice notice-info is-dismissible wpshadow-learning-tip" data-dismissible="wpshadow-learning-tip">
			<div style="display: flex; align-items: start; gap: 12px;">
				<span class="dashicons dashicons-lightbulb" style="color: #2271b1; font-size: 24px; margin-top: 8px;"></span>
				<div style="flex: 1;">
					<p style="margin: 0.5em 0;">
						<strong><?php esc_html_e( 'Learning Tip:', 'wpshadow' ); ?></strong>
						<?php echo wp_kses_post( $tip['message'] ); ?>
					</p>
					<?php if ( ! empty( $tip['links'] ) ) : ?>
						<p style="margin: 0.5em 0;">
							<?php foreach ( $tip['links'] as $link ) : ?>
								<a href="<?php echo esc_url( $link['url'] ); ?>" target="_blank" class="button button-secondary" style="margin-right: 10px;">
									<span class="dashicons dashicons-<?php echo esc_attr( $link['icon'] ?? 'external' ); ?>" style="margin-top: 3px;"></span>
									<?php echo esc_html( $link['text'] ); ?>
								</a>
							<?php endforeach; ?>
						</p>
					<?php endif; ?>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Check if tip should be shown based on login frequency
	 *
	 * Shows tips only on 2nd or 3rd login in a 7-day period to avoid overwhelming users
	 *
	 * @since  1.2605.1357
	 * @param  int $user_id User ID.
	 * @return bool Whether tip should be shown.
	 */
	private static function should_show_tip_based_on_login_frequency( int $user_id ): bool {
		// Track login count in past 7 days
		$login_count_key = 'wpshadow_login_count_' . gmdate( 'W-Y' ); // Weekly key
		$login_count     = (int) get_user_meta( $user_id, $login_count_key, true );

		// Get when last login count was recorded
		$last_count_date_key = 'wpshadow_last_count_date';
		$last_count_date     = (int) get_user_meta( $user_id, $last_count_date_key, true );

		// Reset if more than 7 days have passed
		if ( time() - $last_count_date > WEEK_IN_SECONDS ) {
			$login_count = 0;
		}

		// Increment login count
		$login_count++;
		update_user_meta( $user_id, $login_count_key, $login_count );
		update_user_meta( $user_id, $last_count_date_key, time() );

		// Show tips on 2nd and 3rd login only
		return $login_count >= 2 && $login_count <= 3;
	}

	/**
	 * Get contextual tip for current screen
	 *
	 * @since  1.2604.0100
	 * @param  string $screen_id Current screen ID.
	 * @param  int    $user_id   User ID.
	 * @return array|null Tip data or null if no tip available.
	 */
	private static function get_contextual_tip( string $screen_id, int $user_id = 0 ): ?array {
		if ( $user_id === 0 ) {
			$user_id = get_current_user_id();
		}

		// Get user roles
		$user = get_userdata( $user_id );
		if ( ! $user ) {
			return null;
		}

		$user_roles = $user->roles;

		// Tips array with role restrictions
		$tips = array(
			'toplevel_page_wpshadow'        => array(
				'message' => __( 'New to WordPress security? Our free 5-minute course covers the essentials of keeping your site safe.', 'wpshadow' ),
				'links'   => array(
					array(
						'text' => __( 'Free Security Course', 'wpshadow' ),
						'url'  => UTM_Link_Manager::academy_link( 'wordpress-security-essentials', 'dashboard-tip' ),
						'icon' => 'video-alt3',
					),
					array(
						'text' => __( 'Read the Guide', 'wpshadow' ),
						'url'  => UTM_Link_Manager::kb_link( 'security-checklist', 'dashboard-tip' ),
						'icon' => 'book-alt',
					),
				),
				// Admin and administrator only
				'allowed_roles' => array( 'administrator' ),
			),
			'wpshadow_page_wpshadow-reports' => array(
				'message' => __( 'Understanding your site\'s health metrics helps you make informed decisions. Learn how to interpret these reports.', 'wpshadow' ),
				'links'   => array(
					array(
						'text' => __( 'Report Analysis Guide', 'wpshadow' ),
						'url'  => UTM_Link_Manager::kb_link( 'understanding-reports', 'reports-tip' ),
						'icon' => 'book-alt',
					),
				),
				'allowed_roles' => array( 'administrator', 'editor' ),
			),
			'wpshadow_page_wpshadow-utilities' => array(
				'message' => __( 'Each utility is designed to solve specific problems. Learn when and how to use each one effectively.', 'wpshadow' ),
				'links'   => array(
					array(
						'text' => __( 'Utility Best Practices', 'wpshadow' ),
						'url'  => UTM_Link_Manager::kb_link( 'utility-guide', 'utilities-tip' ),
						'icon' => 'book-alt',
					),
				),
				'allowed_roles' => array( 'administrator' ),
			),
		);

		$tip = $tips[ $screen_id ] ?? null;
		if ( ! $tip ) {
			return null;
		}

		// Check role-based filtering
		$allowed_roles = $tip['allowed_roles'] ?? array();
		if ( ! empty( $allowed_roles ) ) {
			// Check if user has at least one allowed role
			$has_allowed_role = false;
			foreach ( $user_roles as $role ) {
				if ( in_array( $role, $allowed_roles, true ) ) {
					$has_allowed_role = true;
					break;
				}
			}

			if ( ! $has_allowed_role ) {
				return null; // User doesn't have required role
			}
		}

		// Remove role info before returning
		unset( $tip['allowed_roles'] );

		return $tip;
	}

	/**
	 * Get learning resources for a diagnostic
	 *
	 * @since  1.2604.0100
	 * @param  string $diagnostic_slug Diagnostic slug.
	 * @return array {
	 *     Learning resources.
	 *
	 *     @type string      $kb_link       KB article URL.
	 *     @type string      $training_link Training video URL.
	 *     @type string      $kb_title      KB article title.
	 *     @type string      $training_title Training title.
	 *     @type string|null $quick_tip     Quick tip text.
	 * }
	 */
	public static function get_learning_resources( string $diagnostic_slug ): array {
		$resources = array(
			'kb_link'        => self::get_article_link( $diagnostic_slug ),
			'training_link'  => self::get_training_link( $diagnostic_slug ),
			'kb_title'       => '',
			'training_title' => '',
			'quick_tip'      => null,
		);

		// Get titles from mapping
		if ( ! empty( self::$article_map[ $diagnostic_slug ] ) ) {
			$kb_slug = self::$article_map[ $diagnostic_slug ];
			$resources['kb_title']       = self::generate_title_from_slug( $kb_slug );
			$resources['training_title'] = sprintf(
				/* translators: %s: topic name */
				__( 'How to: %s', 'wpshadow' ),
				$resources['kb_title']
			);
		}

		/**
		 * Filter learning resources for diagnostic
		 *
		 * @since 1.2604.0100
		 *
		 * @param array  $resources       Learning resources array.
		 * @param string $diagnostic_slug Diagnostic slug.
		 */
		return apply_filters( 'wpshadow_learning_resources', $resources, $diagnostic_slug );
	}

	/**
	 * Generate human-readable title from slug
	 *
	 * @since  1.2604.0100
	 * @param  string $slug Article slug.
	 * @return string Human-readable title.
	 */
	private static function generate_title_from_slug( string $slug ): string {
		return ucwords( str_replace( array( '-', '_' ), ' ', $slug ) );
	}
}

// Initialize
KB_Article_Manager::init();
