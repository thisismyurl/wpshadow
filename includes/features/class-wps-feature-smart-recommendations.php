<?php
/**
 * Smart Recommendations Engine feature definition.
 *
 * Personalized optimization recommendations based on site profile and heuristic analysis.
 * Extensible to support industry benchmarking when data becomes available.
 *
 * @package wpshadow_SUPPORT
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPSHADOW_Feature_Smart_Recommendations extends WPSHADOW_Abstract_Feature {
	/**
	 * Site classification cache.
	 *
	 * @var array|null
	 */
	private static ?array $site_classification = null;

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'id'                 => 'wpshadow_smart_recommendations',
			'name'               => __( 'Personalized Improvement Suggestions', 'plugin-wpshadow' ),
			'description'        => __( 'Analyzes your site type, traffic patterns, and configuration to suggest practical improvements for speed, security, and reliability. Prioritizes the most helpful actions first, explains why they matter, and links to the right tools so you can make progress without digging through settings. Updates over time as your site changes.', 'plugin-wpshadow' ),
				'scope'              => 'core',
				'version'            => '1.0.0',
				'default_enabled'    => true,
				'widget_group'       => 'analytics-features',
				'widget_label'       => __( 'Analytics & Recommendations', 'plugin-wpshadow' ),
				'widget_description' => __( 'Intelligent site analysis and recommendation features', 'plugin-wpshadow' ),
			)
		);
	}

	/**
	 * Register hooks when feature is enabled.
	 *
	 * @return void
	 */
	public function register(): void {
		if ( ! $this->is_enabled() ) {
			return;
		}

		// Schedule daily recommendation refresh.
		if ( ! wp_next_scheduled( 'wpshadow_recommendations_refresh' ) ) {
			wp_schedule_event( time(), 'daily', 'wpshadow_recommendations_refresh' );
		}
		add_action( 'wpshadow_recommendations_refresh', array( __CLASS__, 'refresh_recommendations' ) );

		// Weekly recommendation digest email.
		if ( ! wp_next_scheduled( 'wpshadow_recommendations_digest' ) ) {
			wp_schedule_event( strtotime( 'next Monday 9:00' ), 'weekly', 'wpshadow_recommendations_digest' );
		}
		add_action( 'wpshadow_recommendations_digest', array( __CLASS__, 'send_weekly_digest' ) );

		// Dashboard widget registration.
		add_action( 'wp_dashboard_setup', array( __CLASS__, 'register_dashboard_widget' ) );

		// Admin page for detailed recommendations view.
		add_action( 'admin_menu', array( __CLASS__, 'register_admin_page' ) );

		// AJAX handler for dismissing recommendations.
		add_action( 'wp_ajax_WPSHADOW_dismiss_recommendation', array( __CLASS__, 'ajax_dismiss_recommendation' ) );
	}

	/**
	 * Classify the site type and characteristics.
	 *
	 * @return array Site classification data.
	 */
	public static function classify_site(): array {
		if ( null !== self::$site_classification ) {
			return self::$site_classification;
		}

		// Check cache first.
		$cached = get_transient( 'wpshadow_site_classification' );
		if ( is_array( $cached ) ) {
			self::$site_classification = $cached;
			return $cached;
		}

		$classification = array(
			'type'           => 'blog', // Default.
			'plugins'        => array(),
			'features'       => array(),
			'traffic_level'  => 'low',
			'growth_rate'    => 0,
			'complexity'     => 'simple',
			'security_score' => 0,
		);

		// Detect site type based on active plugins.
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		// WooCommerce detection.
		if ( is_plugin_active( 'woocommerce/woocommerce.php' ) || class_exists( 'WooCommerce' ) ) {
			$classification['type']       = 'ecommerce';
			$classification['plugins'][]  = 'woocommerce';
			$classification['complexity'] = 'complex';
		}

		// Membership site detection.
		$membership_plugins = array(
			'memberpress/memberpress.php',
			'paid-memberships-pro/paid-memberships-pro.php',
			'restrict-content-pro/restrict-content-pro.php',
		);
		foreach ( $membership_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$classification['type']       = 'membership';
				$classification['plugins'][]  = basename( dirname( $plugin ) );
				$classification['complexity'] = 'moderate';
				break;
			}
		}

		// LMS detection.
		$lms_plugins = array(
			'learndash/learndash.php',
			'lifter-lms/lifterlms.php',
			'tutor/tutor.php',
		);
		foreach ( $lms_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$classification['type']       = 'lms';
				$classification['plugins'][]  = basename( dirname( $plugin ) );
				$classification['complexity'] = 'complex';
				break;
			}
		}

		// Detect features.
		if ( has_action( 'wp_enqueue_scripts' ) ) {
			$classification['features'][] = 'custom_scripts';
		}
		if ( get_option( 'permalink_structure' ) ) {
			$classification['features'][] = 'pretty_permalinks';
		}

		// Estimate traffic level based on post count and comments.
		$post_count    = wp_count_posts()->publish ?? 0;
		$comment_count = wp_count_comments()->approved ?? 0;

		if ( $post_count > 1000 || $comment_count > 5000 ) {
			$classification['traffic_level'] = 'high';
		} elseif ( $post_count > 100 || $comment_count > 500 ) {
			$classification['traffic_level'] = 'medium';
		}

		// Calculate basic security score.
		$security_score = 50; // Base score.

		// Check SSL.
		if ( is_ssl() ) {
			$security_score += 10;
		}

		// Check if using security plugin.
		$security_plugins = array(
			'wordfence/wordfence.php',
			'sucuri-scanner/sucuri.php',
			'all-in-one-wp-security-and-firewall/wp-security.php',
		);
		foreach ( $security_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$security_score += 15;
				break;
			}
		}

		// Check WordPress version.
		global $wp_version;
		$latest_wp = get_transient( 'wpshadow_latest_wp_version' );
		if ( ! $latest_wp ) {
			$latest_wp = $wp_version; // Fallback.
		}
		if ( version_compare( $wp_version, $latest_wp, '=' ) ) {
			$security_score += 10;
		}

		$classification['security_score'] = min( 100, $security_score );

		// Cache for 12 hours.
		set_transient( 'wpshadow_site_classification', $classification, 12 * HOUR_IN_SECONDS );
		self::$site_classification = $classification;

		return $classification;
	}

	/**
	 * Generate recommendations for the site.
	 *
	 * @return array Array of recommendation objects.
	 */
	public static function generate_recommendations(): array {
		$classification  = self::classify_site();
		$recommendations = array();

		// Performance recommendations.
		$performance_recs = self::generate_performance_recommendations( $classification );
		$recommendations  = array_merge( $recommendations, $performance_recs );

		// Security recommendations.
		$security_recs   = self::generate_security_recommendations( $classification );
		$recommendations = array_merge( $recommendations, $security_recs );

		// Scalability recommendations.
		$scalability_recs = self::generate_scalability_recommendations( $classification );
		$recommendations  = array_merge( $recommendations, $scalability_recs );

		// Sort by priority score (highest first).
		usort(
			$recommendations,
			function ( $a, $b ) {
				return ( $b['priority_score'] ?? 0 ) <=> ( $a['priority_score'] ?? 0 );
			}
		);

		// Apply filter to allow extensions.
		return apply_filters( 'wpshadow_smart_recommendations', $recommendations, $classification );
	}

	/**
	 * Generate performance recommendations.
	 *
	 * @param array $classification Site classification.
	 * @return array Performance recommendations.
	 */
	private static function generate_performance_recommendations( array $classification ): array {
		$recommendations = array();

		// Check if object caching is available.
		if ( ! wp_using_ext_object_cache() ) {
			$recommendations[] = array(
				'id'             => 'enable_object_caching',
				'type'           => 'performance',
				'title'          => __( 'Enable Object Caching', 'plugin-wpshadow' ),
				'description'    => __( 'Object caching (Redis/Memcached) can significantly improve database query performance.', 'plugin-wpshadow' ),
				'impact'         => 'high',
				'estimated_gain' => '-500ms to -800ms load time',
				'difficulty'     => 'moderate',
				'cost'           => 'free',
				'priority_score' => 90,
				'action_label'   => __( 'Learn More', 'plugin-wpshadow' ),
				'action_url'     => 'https://wordpress.org/support/article/optimization/#caching',
			);
		}

		// Check for image optimization.
		if ( ! is_plugin_active( 'image-wpshadow/image-wpshadow.php' ) ) {
			$recommendations[] = array(
				'id'             => 'optimize_images',
				'type'           => 'performance',
				'title'          => __( 'Optimize Images', 'plugin-wpshadow' ),
				'description'    => __( 'Install Image Hub to enable WebP, AVIF conversion and smart compression.', 'plugin-wpshadow' ),
				'impact'         => 'high',
				'estimated_gain' => '-300ms to -600ms load time',
				'difficulty'     => 'easy',
				'cost'           => 'free',
				'priority_score' => 85,
				'action_label'   => __( 'Install Image Hub', 'plugin-wpshadow' ),
				'action_url'     => admin_url( 'admin.php?page=wpshadow&tab=modules' ),
			);
		}

		// Check for script optimization.
		$script_deferral_enabled = class_exists( '\\WPShadow\\Features\\WPSHADOW_Feature_Script_Deferral' )
			&& \WPShadow\Features\WPSHADOW_Feature_Registry::is_feature_enabled( 'wpshadow_script_deferral', false );

		if ( ! $script_deferral_enabled ) {
			$recommendations[] = array(
				'id'             => 'enable_script_deferral',
				'type'           => 'performance',
				'title'          => __( 'Enable Script Deferral', 'plugin-wpshadow' ),
				'description'    => __( 'Defer non-critical JavaScript to improve initial page load time.', 'plugin-wpshadow' ),
				'impact'         => 'medium',
				'estimated_gain' => '-200ms to -400ms load time',
				'difficulty'     => 'easy',
				'cost'           => 'free',
				'priority_score' => 75,
				'action_label'   => __( 'Enable Feature', 'plugin-wpshadow' ),
				'action_url'     => admin_url( 'admin.php?page=wpshadow&tab=features' ),
			);
		}

		// WooCommerce specific recommendations.
		if ( 'ecommerce' === $classification['type'] ) {
			$recommendations[] = array(
				'id'             => 'woocommerce_optimization',
				'type'           => 'performance',
				'title'          => __( 'WooCommerce Performance Optimization', 'plugin-wpshadow' ),
				'description'    => __( 'Optimize WooCommerce for better performance: Enable cart fragments caching, disable unused features, optimize product images.', 'plugin-wpshadow' ),
				'impact'         => 'high',
				'estimated_gain' => '-400ms to -700ms load time',
				'difficulty'     => 'moderate',
				'cost'           => 'free',
				'priority_score' => 88,
				'action_label'   => __( 'View Guide', 'plugin-wpshadow' ),
				'action_url'     => 'https://woocommerce.com/document/woocommerce-performance/',
			);
		}

		return $recommendations;
	}

	/**
	 * Generate security recommendations.
	 *
	 * @param array $classification Site classification.
	 * @return array Security recommendations.
	 */
	private static function generate_security_recommendations( array $classification ): array {
		$recommendations = array();
		$security_score  = $classification['security_score'] ?? 50;

		// SSL recommendation.
		if ( ! is_ssl() ) {
			$recommendations[] = array(
				'id'             => 'enable_ssl',
				'type'           => 'security',
				'title'          => __( 'Enable HTTPS/SSL', 'plugin-wpshadow' ),
				'description'    => __( 'Secure your site with HTTPS to protect user data and improve SEO.', 'plugin-wpshadow' ),
				'impact'         => 'critical',
				'estimated_gain' => 'Enhanced security, improved SEO',
				'difficulty'     => 'moderate',
				'cost'           => 'free with Let\'s Encrypt',
				'priority_score' => 95,
				'action_label'   => __( 'Learn More', 'plugin-wpshadow' ),
				'action_url'     => 'https://wordpress.org/support/article/https-for-wordpress/',
			);
		}

		// Security plugin recommendation.
		if ( $security_score < 70 ) {
			$recommendations[] = array(
				'id'             => 'install_security_plugin',
				'type'           => 'security',
				'title'          => __( 'Install Security Plugin', 'plugin-wpshadow' ),
				'description'    => __( 'Add a security plugin like Wordfence or Sucuri to protect against threats.', 'plugin-wpshadow' ),
				'impact'         => 'high',
				'estimated_gain' => 'Protection against common attacks',
				'difficulty'     => 'easy',
				'cost'           => 'free tier available',
				'priority_score' => 85,
				'action_label'   => __( 'Browse Security Plugins', 'plugin-wpshadow' ),
				'action_url'     => admin_url( 'plugin-install.php?s=security&tab=search' ),
			);
		}

		// Plugin updates recommendation.
		$update_count = 0;
		if ( ! function_exists( 'get_plugin_updates' ) ) {
			require_once ABSPATH . 'wp-admin/includes/update.php';
		}
		$plugin_updates = get_plugin_updates();
		$update_count   = is_array( $plugin_updates ) ? count( $plugin_updates ) : 0;

		if ( $update_count > 0 ) {
			$recommendations[] = array(
				'id'             => 'update_plugins',
				'type'           => 'security',
				'title'          => sprintf(
					/* translators: %d: number of plugin updates */
					_n( 'Update %d Plugin', 'Update %d Plugins', $update_count, 'plugin-wpshadow' ),
					$update_count
				),
				'description'    => __( 'Outdated plugins may contain security vulnerabilities. Keep all plugins up to date.', 'plugin-wpshadow' ),
				'impact'         => 'high',
				'estimated_gain' => 'Reduced vulnerability risk',
				'difficulty'     => 'easy',
				'cost'           => 'free',
				'priority_score' => 90,
				'action_label'   => __( 'View Updates', 'plugin-wpshadow' ),
				'action_url'     => admin_url( 'plugins.php' ),
			);
		}

		// Backup recommendation.
		if ( ! self::has_backup_solution() ) {
			$recommendations[] = array(
				'id'             => 'setup_backups',
				'type'           => 'security',
				'title'          => __( 'Setup Automated Backups', 'plugin-wpshadow' ),
				'description'    => __( 'Regular backups protect against data loss from security incidents or errors.', 'plugin-wpshadow' ),
				'impact'         => 'high',
				'estimated_gain' => 'Data protection and recovery capability',
				'difficulty'     => 'easy',
				'cost'           => 'free tier available',
				'priority_score' => 88,
				'action_label'   => __( 'Browse Backup Plugins', 'plugin-wpshadow' ),
				'action_url'     => admin_url( 'plugin-install.php?s=backup&tab=search' ),
			);
		}

		return $recommendations;
	}

	/**
	 * Generate scalability recommendations.
	 *
	 * @param array $classification Site classification.
	 * @return array Scalability recommendations.
	 */
	private static function generate_scalability_recommendations( array $classification ): array {
		$recommendations = array();
		$traffic_level   = $classification['traffic_level'] ?? 'low';
		$complexity      = $classification['complexity'] ?? 'simple';

		// CDN recommendation for high traffic sites.
		if ( 'high' === $traffic_level || 'medium' === $traffic_level ) {
			if ( ! self::has_cdn_configured() ) {
				$recommendations[] = array(
					'id'             => 'enable_cdn',
					'type'           => 'scalability',
					'title'          => __( 'Enable CDN', 'plugin-wpshadow' ),
					'description'    => __( 'Content Delivery Network distributes your static assets globally for faster loading.', 'plugin-wpshadow' ),
					'impact'         => 'high',
					'estimated_gain' => 'Can handle 2-3x traffic, -200ms to -500ms for global users',
					'difficulty'     => 'moderate',
					'cost'           => '$5-10/month',
					'priority_score' => 80,
					'action_label'   => __( 'Learn More', 'plugin-wpshadow' ),
					'action_url'     => 'https://www.cloudflare.com/',
				);
			}
		}

		// Database optimization for complex sites.
		if ( 'complex' === $complexity ) {
			$recommendations[] = array(
				'id'             => 'optimize_database',
				'type'           => 'scalability',
				'title'          => __( 'Optimize Database', 'plugin-wpshadow' ),
				'description'    => __( 'Clean up database overhead, optimize tables, and remove old revisions to improve query performance.', 'plugin-wpshadow' ),
				'impact'         => 'medium',
				'estimated_gain' => 'Faster queries, reduced server load',
				'difficulty'     => 'easy',
				'cost'           => 'free',
				'priority_score' => 70,
				'action_label'   => __( 'Enable Database Cleanup', 'plugin-wpshadow' ),
				'action_url'     => admin_url( 'admin.php?page=wpshadow&tab=features' ),
			);
		}

		// Consultation recommendation for growing sites.
		if ( 'high' === $traffic_level && 'complex' === $complexity ) {
			$recommendations[] = array(
				'id'             => 'scaling_consultation',
				'type'           => 'scalability',
				'title'          => __( 'Schedule Scaling Consultation', 'plugin-wpshadow' ),
				'description'    => __( 'Your site is growing. Get expert advice on architecture, hosting, and optimization strategies.', 'plugin-wpshadow' ),
				'impact'         => 'high',
				'estimated_gain' => 'Custom optimization strategy',
				'difficulty'     => 'professional',
				'cost'           => '$500',
				'priority_score' => 65,
				'action_label'   => __( 'Book Consultation', 'plugin-wpshadow' ),
				'action_url'     => 'https://wpshadow.com/support',
			);
		}

		return $recommendations;
	}

	/**
	 * Check if site has backup solution configured.
	 *
	 * @return bool True if backup solution detected.
	 */
	private static function has_backup_solution(): bool {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$backup_plugins = array(
			'updraftplus/updraftplus.php',
			'backwpup/backwpup.php',
			'duplicator/duplicator.php',
			'backup-backup/backup-backup.php',
		);

		foreach ( $backup_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if site has CDN configured.
	 *
	 * @return bool True if CDN detected.
	 */
	private static function has_cdn_configured(): bool {
		// Check for common CDN plugins.
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$cdn_plugins = array(
			'cloudflare/cloudflare.php',
			'w3-total-cache/w3-total-cache.php',
			'wp-super-cache/wp-cache.php',
		);

		foreach ( $cdn_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Refresh recommendations and cache them.
	 *
	 * @return void
	 */
	public static function refresh_recommendations(): void {
		// Clear classification cache to force refresh.
		delete_transient( 'wpshadow_site_classification' );
		self::$site_classification = null;

		$recommendations = self::generate_recommendations();

		// Filter out dismissed recommendations.
		$dismissed = $this->get_setting( 'wpshadow_dismissed_recommendations', array( ) );
		if ( is_array( $dismissed ) ) {
			$recommendations = array_filter(
				$recommendations,
				function ( $rec ) use ( $dismissed ) {
					return ! in_array( $rec['id'] ?? '', $dismissed, true );
				}
			);
		}

		// Cache recommendations for 24 hours.
		set_transient( 'wpshadow_recommendations_cache', $recommendations, DAY_IN_SECONDS );

		// Log refresh.
		if ( class_exists( '\\WPShadow\\WPSHADOW_Activity_Logger' ) ) {
			\WPShadow\WPSHADOW_Activity_Logger::log(
				'system',
				sprintf(
					/* translators: %d: number of recommendations */
					__( 'Smart Recommendations refreshed: %d recommendations generated', 'plugin-wpshadow' ),
					count( $recommendations )
				),
				array( 'count' => count( $recommendations ) )
			);
		}
	}

	/**
	 * Get cached recommendations.
	 *
	 * @param bool $refresh Force refresh if true.
	 * @return array Array of recommendations.
	 */
	public static function get_recommendations( bool $refresh = false ): array {
		if ( $refresh ) {
			self::refresh_recommendations();
		}

		$recommendations = get_transient( 'wpshadow_recommendations_cache' );

		if ( ! is_array( $recommendations ) ) {
			self::refresh_recommendations();
			$recommendations = get_transient( 'wpshadow_recommendations_cache' );
		}

		return is_array( $recommendations ) ? $recommendations : array();
	}

	/**
	 * Send weekly recommendation digest email.
	 *
	 * @return void
	 */
	public static function send_weekly_digest(): void {
		$recommendations = self::get_recommendations( true );

		if ( empty( $recommendations ) ) {
			return;
		}

		// Get top 5 recommendations.
		$top_recommendations = array_slice( $recommendations, 0, 5 );

		// Get admin email.
		$admin_email = $this->get_setting( 'admin_email' );
		$site_name   = get_bloginfo( 'name' );

		// Build email content.
		$subject = sprintf(
			/* translators: %s: site name */
			__( 'Weekly Recommendations for %s', 'plugin-wpshadow'  ),
			$site_name
		);

		$message = sprintf(
			/* translators: %s: site name */
			__( 'Here are this week\'s top recommendations for %s:', 'plugin-wpshadow' ),
			$site_name
		) . "\n\n";

		foreach ( $top_recommendations as $index => $rec ) {
			$message .= sprintf(
				"%d. %s\n",
				$index + 1,
				$rec['title']
			);
			$message .= sprintf( "   %s\n", $rec['description'] );
			$message .= sprintf( "   Impact: %s | Difficulty: %s | Cost: %s\n", $rec['impact'], $rec['difficulty'], $rec['cost'] );
			$message .= sprintf( "   %s\n\n", $rec['action_url'] ?? '' );
		}

		$message .= sprintf(
			__( 'View all recommendations: %s', 'plugin-wpshadow' ),
			admin_url( 'admin.php?page=wps-recommendations' )
		);

		// Send email.
		wp_mail( $admin_email, $subject, $message );

		// Log email sent.
		if ( class_exists( '\\WPShadow\\WPSHADOW_Activity_Logger' ) ) {
			\WPShadow\WPSHADOW_Activity_Logger::log(
				'system',
				__( 'Weekly recommendations digest email sent', 'plugin-wpshadow' ),
				array(
					'recipient' => $admin_email,
					'count'     => count( $top_recommendations ),
				)
			);
		}
	}

	/**
	 * Register dashboard widget.
	 *
	 * @return void
	 */
	public static function register_dashboard_widget(): void {
		wp_add_dashboard_widget(
			'wpshadow_smart_recommendations',
			__( 'Smart Recommendations', 'plugin-wpshadow' ),
			array( __CLASS__, 'render_dashboard_widget' )
		);
	}

	/**
	 * Render dashboard widget.
	 *
	 * @return void
	 */
	public static function render_dashboard_widget(): void {
		$recommendations = self::get_recommendations();

		if ( empty( $recommendations ) ) {
			echo '<p>' . esc_html__( 'No recommendations at this time. Your site is well optimized!', 'plugin-wpshadow' ) . '</p>';
			return;
		}

		// Display top 3 recommendations.
		$top_recs = array_slice( $recommendations, 0, 3 );

		echo '<style>
			.wps-recommendation { margin-bottom: 15px; padding: 10px; border-left: 3px solid #2271b1; background: #f6f7f7; }
			.wps-recommendation h4 { margin: 0 0 5px 0; }
			.wps-recommendation .impact-high { color: #d63638; }
			.wps-recommendation .impact-medium { color: #dba617; }
			.wps-recommendation .impact-low { color: #2271b1; }
			.wps-recommendation .impact-critical { color: #d63638; font-weight: bold; }
			.wps-recommendation-meta { font-size: 12px; color: #646970; margin: 5px 0; }
			.wps-recommendation-actions { margin-top: 8px; }
		</style>';

		foreach ( $top_recs as $rec ) {
			$impact_class = 'impact-' . sanitize_html_class( $rec['impact'] ?? 'medium' );
			?>
			<div class="wps-recommendation" data-rec-id="<?php echo esc_attr( $rec['id'] ?? '' ); ?>">
				<h4>
					<span class="<?php echo esc_attr( $impact_class ); ?>">●</span>
					<?php echo esc_html( $rec['title'] ?? '' ); ?>
				</h4>
				<p><?php echo esc_html( $rec['description'] ?? '' ); ?></p>
				<div class="wps-recommendation-meta">
					<strong><?php esc_html_e( 'Impact:', 'plugin-wpshadow' ); ?></strong> <?php echo esc_html( ucfirst( $rec['impact'] ?? 'medium' ) ); ?> |
					<strong><?php esc_html_e( 'Difficulty:', 'plugin-wpshadow' ); ?></strong> <?php echo esc_html( ucfirst( $rec['difficulty'] ?? 'moderate' ) ); ?> |
					<strong><?php esc_html_e( 'Cost:', 'plugin-wpshadow' ); ?></strong> <?php echo esc_html( $rec['cost'] ?? 'unknown' ); ?>
				</div>
				<?php if ( ! empty( $rec['estimated_gain'] ) ) : ?>
					<div class="wps-recommendation-meta">
						<strong><?php esc_html_e( 'Expected Result:', 'plugin-wpshadow' ); ?></strong> <?php echo esc_html( $rec['estimated_gain'] ); ?>
					</div>
				<?php endif; ?>
				<div class="wps-recommendation-actions">
					<?php if ( ! empty( $rec['action_url'] ) ) : ?>
						<a href="<?php echo esc_url( $rec['action_url'] ); ?>" class="button button-small button-primary">
							<?php echo esc_html( $rec['action_label'] ?? __( 'Take Action', 'plugin-wpshadow' ) ); ?>
						</a>
					<?php endif; ?>
					<a href="#" class="button button-small wps-dismiss-recommendation" data-rec-id="<?php echo esc_attr( $rec['id'] ?? '' ); ?>" data-nonce="<?php echo esc_attr( wp_create_nonce( 'wpshadow_dismiss_recommendation' ) ); ?>">
						<?php esc_html_e( 'Dismiss', 'plugin-wpshadow' ); ?>
					</a>
				</div>
			</div>
			<?php
		}

		if ( count( $recommendations ) > 3 ) {
			?>
			<p>
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=wps-recommendations' ) ); ?>">
					<?php
					printf(
						/* translators: %d: number of additional recommendations */
						esc_html__( 'View %d more recommendations →', 'plugin-wpshadow' ),
						count( $recommendations ) - 3
					);
					?>
				</a>
			</p>
			<?php
		}

		// Enqueue dismiss script.
		?>
		<script>
		jQuery(document).ready(function($) {
			$('.wps-dismiss-recommendation').on('click', function(e) {
				e.preventDefault();
				var button = $(this);
				var recId = button.data('rec-id');
				var nonce = button.data('nonce');
				
				$.post(ajaxurl, {
					action: 'wpshadow_dismiss_recommendation',
					rec_id: recId,
					nonce: nonce
				}, function(response) {
					if (response.success) {
						button.closest('.wps-recommendation').fadeOut();
					}
				});
			});
		});
		</script>
		<?php
	}

	/**
	 * AJAX handler for dismissing recommendations.
	 *
	 * @return void
	 */
	public static function ajax_dismiss_recommendation(): void {
		\WPShadow\WPSHADOW_verify_ajax_request( 'wpshadow_recommendations' );

		$rec_id = \WPShadow\WPSHADOW_get_post_key( 'rec_id' );

		if ( empty( $rec_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid recommendation ID', 'plugin-wpshadow' ) ) );
		}

		$dismissed = $this->get_setting( 'wpshadow_dismissed_recommendations', array( ) );
		if ( ! is_array( $dismissed ) ) {
			$dismissed = array();
		}

		if ( ! in_array( $rec_id, $dismissed, true ) ) {
			$dismissed[] = $rec_id;
			$this->update_setting( 'wpshadow_dismissed_recommendations', $dismissed  );
		}

		wp_send_json_success();
	}

	/**
	 * Register admin page for detailed recommendations.
	 *
	 * @return void
	 */
	public static function register_admin_page(): void {
		add_submenu_page(
			'wpshadow',
			__( 'Smart Recommendations', 'plugin-wpshadow' ),
			__( 'Recommendations', 'plugin-wpshadow' ),
			'manage_options',
			'wps-recommendations',
			array( __CLASS__, 'render_admin_page' )
		);
	}

	/**
	 * Render admin page for recommendations.
	 *
	 * @return void
	 */
	public static function render_admin_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'plugin-wpshadow' ) );
		}

		$recommendations = self::get_recommendations();
		$classification  = self::classify_site();

		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Smart Recommendations', 'plugin-wpshadow' ); ?></h1>
			
			<div class="wps-recommendations-header" style="margin-bottom: 20px; padding: 15px; background: #fff; border-left: 4px solid #2271b1;">
				<h2><?php esc_html_e( 'Your Site Profile', 'plugin-wpshadow' ); ?></h2>
				<p>
					<strong><?php esc_html_e( 'Site Type:', 'plugin-wpshadow' ); ?></strong> <?php echo esc_html( ucfirst( $classification['type'] ?? 'blog' ) ); ?><br>
					<strong><?php esc_html_e( 'Complexity:', 'plugin-wpshadow' ); ?></strong> <?php echo esc_html( ucfirst( $classification['complexity'] ?? 'simple' ) ); ?><br>
					<strong><?php esc_html_e( 'Traffic Level:', 'plugin-wpshadow' ); ?></strong> <?php echo esc_html( ucfirst( $classification['traffic_level'] ?? 'low' ) ); ?><br>
					<strong><?php esc_html_e( 'Security Score:', 'plugin-wpshadow' ); ?></strong> <?php echo esc_html( $classification['security_score'] ?? 50 ); ?>/100
				</p>
				<p>
					<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=wps-recommendations&action=refresh' ), 'wpshadow_refresh_recommendations' ) ); ?>" class="button">
						<?php esc_html_e( 'Refresh Recommendations', 'plugin-wpshadow' ); ?>
					</a>
				</p>
			</div>
			
			<?php if ( isset( $_GET['action'] ) && 'refresh' === $_GET['action'] && check_admin_referer( 'wpshadow_refresh_recommendations' ) ) : ?>
				<?php
				self::refresh_recommendations();
				$recommendations = self::get_recommendations();
				?>
				<div class="notice notice-success is-dismissible">
					<p><?php esc_html_e( 'Recommendations refreshed successfully.', 'plugin-wpshadow' ); ?></p>
				</div>
			<?php endif; ?>
			
			<?php if ( empty( $recommendations ) ) : ?>
				<div class="notice notice-info">
					<p><?php esc_html_e( 'No recommendations at this time. Your site is well optimized!', 'plugin-wpshadow' ); ?></p>
				</div>
			<?php else : ?>
				<h2><?php esc_html_e( 'Recommendations (by Priority)', 'plugin-wpshadow' ); ?></h2>
				
				<?php
				// Group by type.
				$grouped = array(
					'performance' => array(),
					'security'    => array(),
					'scalability' => array(),
				);

				foreach ( $recommendations as $rec ) {
					$type = $rec['type'] ?? 'performance';
					if ( isset( $grouped[ $type ] ) ) {
						$grouped[ $type ][] = $rec;
					}
				}

				foreach ( $grouped as $type => $recs ) {
					if ( empty( $recs ) ) {
						continue;
					}
					?>
					<h3><?php echo esc_html( ucfirst( $type ) . ' ' . __( 'Recommendations', 'plugin-wpshadow' ) ); ?></h3>
					
					<?php foreach ( $recs as $rec ) : ?>
						<div class="wps-recommendation" style="margin-bottom: 20px; padding: 15px; background: #fff; border-left: 4px solid #<?php echo 'critical' === $rec['impact'] ? 'd63638' : ( 'high' === $rec['impact'] ? 'dba617' : '2271b1' ); ?>;">
							<h4><?php echo esc_html( $rec['title'] ?? '' ); ?></h4>
							<p><?php echo esc_html( $rec['description'] ?? '' ); ?></p>
							<div style="margin: 10px 0; font-size: 13px; color: #646970;">
								<strong><?php esc_html_e( 'Impact:', 'plugin-wpshadow' ); ?></strong> <?php echo esc_html( ucfirst( $rec['impact'] ?? 'medium' ) ); ?> |
								<strong><?php esc_html_e( 'Difficulty:', 'plugin-wpshadow' ); ?></strong> <?php echo esc_html( ucfirst( $rec['difficulty'] ?? 'moderate' ) ); ?> |
								<strong><?php esc_html_e( 'Cost:', 'plugin-wpshadow' ); ?></strong> <?php echo esc_html( $rec['cost'] ?? 'unknown' ); ?> |
								<strong><?php esc_html_e( 'Priority Score:', 'plugin-wpshadow' ); ?></strong> <?php echo esc_html( $rec['priority_score'] ?? 0 ); ?>/100
							</div>
							<?php if ( ! empty( $rec['estimated_gain'] ) ) : ?>
								<div style="margin: 10px 0; padding: 10px; background: #f0f6fc; border-radius: 3px;">
									<strong><?php esc_html_e( 'Expected Result:', 'plugin-wpshadow' ); ?></strong> <?php echo esc_html( $rec['estimated_gain'] ); ?>
								</div>
							<?php endif; ?>
							<div style="margin-top: 10px;">
								<?php if ( ! empty( $rec['action_url'] ) ) : ?>
									<a href="<?php echo esc_url( $rec['action_url'] ); ?>" class="button button-primary">
										<?php echo esc_html( $rec['action_label'] ?? __( 'Take Action', 'plugin-wpshadow' ) ); ?>
									</a>
								<?php endif; ?>
							</div>
						</div>
					<?php endforeach; ?>
					<?php
				}
				?>
			<?php endif; ?>
		</div>
		<?php
	}
}
