<?php
/**
 * Tips Coach Feature
 *
 * Provides contextual "next best action" cards in the dashboard,
 * tuned by site type (blog, WooCommerce, LMS), with one-click apply.
 *
 * @package WPShadow
 * @since 1.2601.74000
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Tips Coach Feature Class
 *
 * Detects site type and provides contextual action recommendations.
 */
class WPSHADOW_Feature_Tips_Coach extends WPSHADOW_Abstract_Feature {

	/**
	 * Site type constants
	 */
	private const TYPE_BLOG        = 'blog';
	private const TYPE_WOOCOMMERCE = 'woocommerce';
	private const TYPE_LMS         = 'lms';
	private const TYPE_GENERIC     = 'generic';
	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct(
			array(
				'id'                 => 'wpshadow_tips_coach',
				'name'               => __( 'Tips Coach', 'plugin-wpshadow' ),
			'description'        => __( 'Provides tailored, easy to follow tips for your site type, from blogs to stores to course sites. Surfaces quick wins first, explains benefits in plain language, and tracks what you have completed so you can keep improving steadily without needing deep technical knowledge.', 'plugin-wpshadow' ),
				'scope'              => 'core',
				'version'            => '1.0.0',
				'default_enabled'    => true,
				'widget_group'       => 'diagnostics',
				'license_level'      => 1,
				'minimum_capability' => 'manage_options',
				'icon'               => 'dashicons-welcome-learn-more',
				'category'           => 'diagnostics',
				'priority'           => 40,
			)
		);
	}


	/**
	 * Initialize the Tips Coach feature.
	 *
	 * @return void
	 */
	public static function init(): void {
		add_action( 'wp_ajax_WPSHADOW_apply_tip_action', array( __CLASS__, 'ajax_apply_tip_action' ) );
		add_action( 'wp_ajax_WPSHADOW_dismiss_tip', array( __CLASS__, 'ajax_dismiss_tip' ) );
	}

	/**
	 * Detect the primary site type based on active plugins and content.
	 *
	 * @return string Site type identifier.
	 */
	public static function detect_site_type(): string {
		// Check for WooCommerce
		if ( class_exists( 'WooCommerce' ) || self::is_plugin_active_safe( 'woocommerce/woocommerce.php' ) ) {
			return self::TYPE_WOOCOMMERCE;
		}

		// Check for LMS plugins (LearnDash, LifterLMS, Tutor LMS, LearnPress)
		$lms_plugins = array(
			'sfwd-lms/sfwd_lms.php',           // LearnDash
			'lifterlms/lifterlms.php',          // LifterLMS
			'tutor/tutor.php',                  // Tutor LMS
			'learnpress/learnpress.php',        // LearnPress
		);

		foreach ( $lms_plugins as $plugin ) {
			if ( self::is_plugin_active_safe( $plugin ) ) {
				return self::TYPE_LMS;
			}
		}

		// Check if it's primarily a blog (more posts than pages)
		$post_count = wp_count_posts( 'post' );
		$page_count = wp_count_posts( 'page' );

		if ( isset( $post_count->publish, $page_count->publish ) && $post_count->publish > 5 ) {
			return self::TYPE_BLOG;
		}

		return self::TYPE_GENERIC;
	}

	/**
	 * Get dismissed tips for the current user.
	 *
	 * @return array Array of dismissed tip IDs.
	 */
	private static function get_dismissed_tips(): array {
		$dismissed = get_user_meta( get_current_user_id(), 'wpshadow_dismissed_tips', true );
		return is_array( $dismissed ) ? $dismissed : array();
	}

	/**
	 * Check if a tip has been dismissed.
	 *
	 * @param string $tip_id Tip identifier.
	 * @return bool True if dismissed.
	 */
	private static function is_tip_dismissed( string $tip_id ): bool {
		return in_array( $tip_id, self::get_dismissed_tips(), true );
	}

	/**
	 * Get contextual action cards based on site type.
	 *
	 * @return array Array of tip cards.
	 */
	public static function get_tips(): array {
		$site_type = self::detect_site_type();
		$tips      = array();

		// Get common tips for all sites
		$tips = array_merge( $tips, self::get_common_tips() );

		// Get site-type-specific tips
		switch ( $site_type ) {
			case self::TYPE_BLOG:
				$tips = array_merge( $tips, self::get_blog_tips() );
				break;
			case self::TYPE_WOOCOMMERCE:
				$tips = array_merge( $tips, self::get_woocommerce_tips() );
				break;
			case self::TYPE_LMS:
				$tips = array_merge( $tips, self::get_lms_tips() );
				break;
			default:
				$tips = array_merge( $tips, self::get_generic_tips() );
				break;
		}

		// Filter out dismissed tips
		$tips = array_filter(
			$tips,
			function ( $tip ) {
				return ! self::is_tip_dismissed( $tip['id'] );
			}
		);

		// Limit to top 3 most relevant tips
		return array_slice( $tips, 0, 3 );
	}

	/**
	 * Get common tips applicable to all sites.
	 *
	 * @return array Array of tip cards.
	 */
	private static function get_common_tips(): array {
		$tips = array();

		// Check if site health has issues
		if ( ! self::is_action_completed( 'check_site_health' ) ) {
			$health_data = get_transient( 'health-check-site-status-result' );
			if ( false === $health_data || ( isset( $health_data['critical'] ) && $health_data['critical'] > 0 ) ) {
				$tips[] = array(
					'id'           => 'check_site_health',
					'title'        => __( 'Check Site Health', 'plugin-wpshadow' ),
					'description'  => __( 'Your site has critical health issues that need attention.', 'plugin-wpshadow' ),
					'action'       => 'open_site_health',
					'action_label' => __( 'View Issues', 'plugin-wpshadow' ),
					'icon'         => 'dashicons-heart',
					'priority'     => 100,
				);
			}
		}

		// Check if backups are configured
		if ( ! self::is_action_completed( 'setup_backups' ) && ! self::has_backup_plugin() ) {
			$tips[] = array(
				'id'           => 'setup_backups',
				'title'        => __( 'Set Up Backups', 'plugin-wpshadow' ),
				'description'  => __( 'Protect your site with automated backups.', 'plugin-wpshadow' ),
				'action'       => 'open_backup_info',
				'action_label' => __( 'Learn More', 'plugin-wpshadow' ),
				'icon'         => 'dashicons-backup',
				'priority'     => 90,
			);
		}

		// Check if SSL is enabled
		if ( ! is_ssl() && ! self::is_action_completed( 'enable_ssl' ) ) {
			$tips[] = array(
				'id'           => 'enable_ssl',
				'title'        => __( 'Enable HTTPS', 'plugin-wpshadow' ),
				'description'  => __( 'Secure your site with HTTPS for better security and SEO.', 'plugin-wpshadow' ),
				'action'       => 'open_ssl_info',
				'action_label' => __( 'Learn More', 'plugin-wpshadow' ),
				'icon'         => 'dashicons-lock',
				'priority'     => 85,
			);
		}

		return $tips;
	}

	/**
	 * Get tips specific to blog sites.
	 *
	 * @return array Array of tip cards.
	 */
	private static function get_blog_tips(): array {
		$tips = array();

		// Check if SEO plugin is active
		if ( ! self::has_seo_plugin() && ! self::is_action_completed( 'install_seo_plugin' ) ) {
			$tips[] = array(
				'id'           => 'install_seo_plugin',
				'title'        => __( 'Optimize for Search Engines', 'plugin-wpshadow' ),
				'description'  => __( 'Install an SEO plugin to improve your search rankings.', 'plugin-wpshadow' ),
				'action'       => 'open_seo_plugins',
				'action_label' => __( 'Browse Plugins', 'plugin-wpshadow' ),
				'icon'         => 'dashicons-chart-line',
				'priority'     => 80,
			);
		}

		// Check if caching is enabled
		if ( ! self::has_caching_plugin() && ! self::is_action_completed( 'enable_caching' ) ) {
			$tips[] = array(
				'id'           => 'enable_caching',
				'title'        => __( 'Speed Up Your Site', 'plugin-wpshadow' ),
				'description'  => __( 'Enable caching to improve page load times.', 'plugin-wpshadow' ),
				'action'       => 'open_caching_plugins',
				'action_label' => __( 'Browse Plugins', 'plugin-wpshadow' ),
				'icon'         => 'dashicons-performance',
				'priority'     => 75,
			);
		}

		// Check if comments are moderated
		if ( '1' !== get_option( 'comment_moderation' ) && ! self::is_action_completed( 'enable_comment_moderation' ) ) {
			$tips[] = array(
				'id'           => 'enable_comment_moderation',
				'title'        => __( 'Enable Comment Moderation', 'plugin-wpshadow' ),
				'description'  => __( 'Prevent spam by requiring approval for new comments.', 'plugin-wpshadow' ),
				'action'       => 'enable_comment_moderation',
				'action_label' => __( 'Enable Now', 'plugin-wpshadow' ),
				'icon'         => 'dashicons-admin-comments',
				'priority'     => 70,
			);
		}

		return $tips;
	}

	/**
	 * Get tips specific to WooCommerce sites.
	 *
	 * @return array Array of tip cards.
	 */
	private static function get_woocommerce_tips(): array {
		$tips = array();

		// Check if WooCommerce setup is complete
		if ( function_exists( 'WC' ) ) {
			$wc = WC();

			// Check if payment gateway is configured
			if ( isset( $wc->payment_gateways ) && method_exists( $wc->payment_gateways, 'get_available_payment_gateways' ) ) {
				$gateways = $wc->payment_gateways->get_available_payment_gateways();
				if ( empty( $gateways ) && ! self::is_action_completed( 'setup_payment_gateway' ) ) {
					$tips[] = array(
						'id'           => 'setup_payment_gateway',
						'title'        => __( 'Configure Payment Gateway', 'plugin-wpshadow' ),
						'description'  => __( 'Set up a payment method to start accepting orders.', 'plugin-wpshadow' ),
						'action'       => 'open_woo_payments',
						'action_label' => __( 'Configure Now', 'plugin-wpshadow' ),
						'icon'         => 'dashicons-money-alt',
						'priority'     => 95,
					);
				}
			}

			// Check if shipping is configured
			if ( isset( $wc->shipping ) && method_exists( $wc->shipping, 'get_shipping_methods' ) ) {
				$shipping_methods = $wc->shipping->get_shipping_methods();
				if ( empty( $shipping_methods ) && ! self::is_action_completed( 'setup_shipping' ) ) {
					$tips[] = array(
						'id'           => 'setup_shipping',
						'title'        => __( 'Configure Shipping Methods', 'plugin-wpshadow' ),
						'description'  => __( 'Set up shipping options for your customers.', 'plugin-wpshadow' ),
						'action'       => 'open_woo_shipping',
						'action_label' => __( 'Configure Now', 'plugin-wpshadow' ),
						'icon'         => 'dashicons-cart',
						'priority'     => 90,
					);
				}
			}

			// Check if tax settings are configured
			if ( function_exists( 'wc_tax_enabled' ) ) {
				$tax_enabled = wc_tax_enabled();
				if ( ! $tax_enabled && ! self::is_action_completed( 'setup_taxes' ) ) {
					$tips[] = array(
						'id'           => 'setup_taxes',
						'title'        => __( 'Configure Tax Settings', 'plugin-wpshadow' ),
						'description'  => __( 'Set up tax rates for your products.', 'plugin-wpshadow' ),
						'action'       => 'open_woo_tax',
						'action_label' => __( 'Configure Now', 'plugin-wpshadow' ),
						'icon'         => 'dashicons-calculator',
						'priority'     => 85,
					);
				}
			}
		}

		return $tips;
	}

	/**
	 * Get tips specific to LMS sites.
	 *
	 * @return array Array of tip cards.
	 */
	private static function get_lms_tips(): array {
		$tips = array();

		// Check if membership/enrollment is set up
		if ( ! self::is_action_completed( 'create_first_course' ) ) {
			$tips[] = array(
				'id'           => 'create_first_course',
				'title'        => __( 'Create Your First Course', 'plugin-wpshadow' ),
				'description'  => __( 'Start building content for your students.', 'plugin-wpshadow' ),
				'action'       => 'open_course_creation',
				'action_label' => __( 'Create Course', 'plugin-wpshadow' ),
				'icon'         => 'dashicons-welcome-learn-more',
				'priority'     => 95,
			);
		}

		// Check if email notifications are configured
		if ( ! self::is_action_completed( 'setup_email_notifications' ) ) {
			$tips[] = array(
				'id'           => 'setup_email_notifications',
				'title'        => __( 'Configure Email Notifications', 'plugin-wpshadow' ),
				'description'  => __( 'Set up automated emails for course enrollments and completions.', 'plugin-wpshadow' ),
				'action'       => 'open_email_settings',
				'action_label' => __( 'Configure Now', 'plugin-wpshadow' ),
				'icon'         => 'dashicons-email-alt',
				'priority'     => 85,
			);
		}

		// Check if certificates are enabled
		if ( ! self::is_action_completed( 'enable_certificates' ) ) {
			$tips[] = array(
				'id'           => 'enable_certificates',
				'title'        => __( 'Enable Course Certificates', 'plugin-wpshadow' ),
				'description'  => __( 'Reward students with certificates upon course completion.', 'plugin-wpshadow' ),
				'action'       => 'open_certificate_settings',
				'action_label' => __( 'Learn More', 'plugin-wpshadow' ),
				'icon'         => 'dashicons-awards',
				'priority'     => 80,
			);
		}

		return $tips;
	}

	/**
	 * Get generic tips for sites that don't fit specific categories.
	 *
	 * @return array Array of tip cards.
	 */
	private static function get_generic_tips(): array {
		$tips = array();

		// Check if site tagline is still default
		$tagline = $this->get_setting( 'blogdescription' );
		if ( 'Just another WordPress site' === $tagline && ! self::is_action_completed( 'update_tagline' ) ) {
			$tips[] = array(
				'id'           => 'update_tagline', 'title'        => __( 'Update Site Tagline', 'plugin-wpshadow'  ),
				'description'  => __( 'Customize your site tagline to describe your website.', 'plugin-wpshadow' ),
				'action'       => 'open_general_settings',
				'action_label' => __( 'Update Now', 'plugin-wpshadow' ),
				'icon'         => 'dashicons-admin-settings',
				'priority'     => 75,
			);
		}

		// Check if permalink structure is optimized
		$permalink_structure = $this->get_setting( 'permalink_structure' );
		if ( empty( $permalink_structure ) && ! self::is_action_completed( 'optimize_permalinks' ) ) {
			$tips[] = array(
				'id'           => 'optimize_permalinks', 'title'        => __( 'Optimize Permalinks', 'plugin-wpshadow'  ),
				'description'  => __( 'Use SEO-friendly URLs for better search rankings.', 'plugin-wpshadow' ),
				'action'       => 'open_permalink_settings',
				'action_label' => __( 'Configure Now', 'plugin-wpshadow' ),
				'icon'         => 'dashicons-admin-links',
				'priority'     => 80,
			);
		}

		return $tips;
	}

	/**
	 * Check if a specific action has been marked as completed.
	 *
	 * @param string $action_id Action identifier.
	 * @return bool True if completed.
	 */
	private static function is_action_completed( string $action_id ): bool {
		$completed = $this->get_setting( 'wpshadow_completed_tips', array( ) );
		return is_array( $completed ) && in_array( $action_id, $completed, true );
	}

	/**
	 * Mark an action as completed.
	 *
	 * @param string $action_id Action identifier.
	 * @return bool True on success.
	 */
	private static function mark_action_completed( string $action_id ): bool {
		$completed = $this->get_setting( 'wpshadow_completed_tips', array( ) );
		if ( ! is_array( $completed ) ) {
			$completed = array();
		}
		if ( ! in_array( $action_id, $completed, true ) ) {
			$completed[] = $action_id;
			return $this->update_setting( 'wpshadow_completed_tips', $completed  );
		}
		return true;
	}

	/**
	 * Check if a plugin is active (safe wrapper).
	 *
	 * @param string $plugin Plugin path relative to plugins directory.
	 * @return bool True if plugin is active.
	 */
	private static function is_plugin_active_safe( string $plugin ): bool {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		return is_plugin_active( $plugin );
	}

	/**
	 * Check if site has an SEO plugin active.
	 *
	 * @return bool True if SEO plugin is active.
	 */
	private static function has_seo_plugin(): bool {
		$seo_plugins = array(
			'wordpress-seo/wp-seo.php',           // Yoast SEO
			'all-in-one-seo-pack/all_in_one_seo_pack.php', // All in One SEO
			'seo-by-rank-math/rank-math.php',     // Rank Math
			'autodescription/autodescription.php', // The SEO Framework
		);

		foreach ( $seo_plugins as $plugin ) {
			if ( self::is_plugin_active_safe( $plugin ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if site has a caching plugin active.
	 *
	 * @return bool True if caching plugin is active.
	 */
	private static function has_caching_plugin(): bool {
		$caching_plugins = array(
			'wp-super-cache/wp-cache.php',        // WP Super Cache
			'w3-total-cache/w3-total-cache.php',  // W3 Total Cache
			'wp-fastest-cache/wpFastestCache.php', // WP Fastest Cache
			'litespeed-cache/litespeed-cache.php', // LiteSpeed Cache
		);

		foreach ( $caching_plugins as $plugin ) {
			if ( self::is_plugin_active_safe( $plugin ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if site has a backup plugin active.
	 *
	 * @return bool True if backup plugin is active.
	 */
	private static function has_backup_plugin(): bool {
		$backup_plugins = array(
			'updraftplus/updraftplus.php',        // UpdraftPlus
			'backwpup/backwpup.php',              // BackWPup
			'duplicator/duplicator.php',          // Duplicator
			'all-in-one-wp-migration/all-in-one-wp-migration.php', // All-in-One WP Migration
		);

		foreach ( $backup_plugins as $plugin ) {
			if ( self::is_plugin_active_safe( $plugin ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * AJAX handler: Apply a tip action.
	 *
	 * @return void
	 */
	public static function ajax_apply_tip_action(): void {
		\WPShadow\WPSHADOW_verify_ajax_request( 'wpshadow_tips_coach' );

		$action = \WPShadow\WPSHADOW_get_post_key( 'action_type' );
		$tip_id = \WPShadow\WPSHADOW_get_post_key( 'tip_id' );

		if ( empty( $action ) || empty( $tip_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid action or tip ID', 'plugin-wpshadow' ) ) );
		}

		// Process different action types
		$result = self::process_tip_action( $action, $tip_id );

		if ( $result['success'] ) {
			self::mark_action_completed( $tip_id );
			wp_send_json_success( $result );
		} else {
			wp_send_json_error( $result );
		}
	}

	/**
	 * Process a tip action.
	 *
	 * @param string $action Action type.
	 * @param string $tip_id Tip identifier.
	 * @return array Result array with success status and data.
	 */
	private static function process_tip_action( string $action, string $tip_id ): array {
		switch ( $action ) {
			case 'enable_comment_moderation':
				$this->update_setting( 'comment_moderation', '1'  );
				return array(
					'success'  => true,
					'message'  => __( 'Comment moderation enabled successfully.', 'plugin-wpshadow' ),
					'redirect' => admin_url( 'options-discussion.php' ),
				);

			case 'open_site_health':
				return array(
					'success'  => true,
					'redirect' => admin_url( 'site-health.php' ),
				);

			case 'open_backup_info':
				return array(
					'success'  => true,
					'redirect' => admin_url( 'plugin-install.php?s=backup&tab=search' ),
				);

			case 'open_ssl_info':
				return array(
					'success'  => true,
					'redirect' => admin_url( 'options-general.php' ),
				);

			case 'open_seo_plugins':
				return array(
					'success'  => true,
					'redirect' => admin_url( 'plugin-install.php?s=seo&tab=search' ),
				);

			case 'open_caching_plugins':
				return array(
					'success'  => true,
					'redirect' => admin_url( 'plugin-install.php?s=cache&tab=search' ),
				);

			case 'open_woo_payments':
				return array(
					'success'  => true,
					'redirect' => admin_url( 'admin.php?page=wc-settings&tab=checkout' ),
				);

			case 'open_woo_shipping':
				return array(
					'success'  => true,
					'redirect' => admin_url( 'admin.php?page=wc-settings&tab=shipping' ),
				);

			case 'open_woo_tax':
				return array(
					'success'  => true,
					'redirect' => admin_url( 'admin.php?page=wc-settings&tab=tax' ),
				);

			case 'open_course_creation':
				// Try to determine the LMS plugin and redirect accordingly
				if ( self::is_plugin_active_safe( 'sfwd-lms/sfwd_lms.php' ) ) {
					return array(
						'success'  => true,
						'redirect' => admin_url( 'post-new.php?post_type=sfwd-courses' ),
					);
				} elseif ( self::is_plugin_active_safe( 'lifterlms/lifterlms.php' ) ) {
					return array(
						'success'  => true,
						'redirect' => admin_url( 'post-new.php?post_type=course' ),
					);
				} elseif ( self::is_plugin_active_safe( 'tutor/tutor.php' ) ) {
					return array(
						'success'  => true,
						'redirect' => admin_url( 'post-new.php?post_type=courses' ),
					);
				}
				return array(
					'success'  => true,
					'redirect' => admin_url( 'edit.php?post_type=courses' ),
				);

			case 'open_email_settings':
			case 'open_certificate_settings':
				return array(
					'success'  => true,
					'redirect' => admin_url( 'options-general.php' ),
				);

			case 'open_general_settings':
				return array(
					'success'  => true,
					'redirect' => admin_url( 'options-general.php' ),
				);

			case 'open_permalink_settings':
				return array(
					'success'  => true,
					'redirect' => admin_url( 'options-permalink.php' ),
				);

			default:
				return array(
					'success' => false,
					'message' => __( 'Unknown action type', 'plugin-wpshadow' ),
				);
		}
	}

	/**
	 * AJAX handler: Dismiss a tip.
	 *
	 * @return void
	 */
	public static function ajax_dismiss_tip(): void {
		\WPShadow\WPSHADOW_verify_ajax_request( 'wpshadow_tips_coach' );

		$tip_id = \WPShadow\WPSHADOW_get_post_key( 'tip_id' );

		if ( empty( $tip_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid tip ID', 'plugin-wpshadow' ) ) );
		}

		$dismissed = self::get_dismissed_tips();
		if ( ! in_array( $tip_id, $dismissed, true ) ) {
			$dismissed[] = $tip_id;
			update_user_meta( get_current_user_id(), 'wpshadow_dismissed_tips', $dismissed );
		}

		wp_send_json_success( array( 'message' => __( 'Tip dismissed', 'plugin-wpshadow' ) ) );
	}

	/**
	 * Render the Tips Coach widget.
	 *
	 * @return void
	 */
	public static function render_widget(): void {
		$tips = self::get_tips();

		if ( empty( $tips ) ) {
			return; // Don't render widget if there are no tips
		}

		// Sort tips by priority (highest first)
		usort(
			$tips,
			function ( $a, $b ) {
				return ( $b['priority'] ?? 0 ) - ( $a['priority'] ?? 0 );
			}
		);

		$site_type       = self::detect_site_type();
		$site_type_label = self::get_site_type_label( $site_type );

		wp_enqueue_script( 'jquery' );
		?>
		<div class="wps-widget-content wps-tips-coach">
			<div class="wps-tips-header" style="margin-bottom: 15px; padding-bottom: 10px; border-bottom: 2px solid #e5e5e5;">
				<div style="display: flex; align-items: center; justify-content: space-between;">
					<div>
						<h3 style="margin: 0; font-size: 14px; color: #1d2327;"><?php esc_html_e( 'Recommended Actions', 'plugin-wpshadow' ); ?></h3>
						<p style="margin: 5px 0 0; font-size: 12px; color: #646970;">
							<?php
							/* translators: %s: site type label */
							echo esc_html( sprintf( __( 'Personalized for your %s', 'plugin-wpshadow' ), $site_type_label ) );
							?>
						</p>
					</div>
					<span class="dashicons dashicons-lightbulb" style="font-size: 24px; color: #f0b849;"></span>
				</div>
			</div>

			<div class="wps-tips-list" style="display: flex; flex-direction: column; gap: 12px;">
				<?php foreach ( $tips as $tip ) : ?>
					<div class="wps-tip-card" data-tip-id="<?php echo esc_attr( $tip['id'] ); ?>" style="padding: 12px; background: #f6f7f7; border-radius: 4px; border-left: 3px solid #2271b1;">
						<div style="display: flex; align-items: start; gap: 10px;">
							<span class="dashicons <?php echo esc_attr( $tip['icon'] ); ?>" style="font-size: 20px; color: #2271b1; margin-top: 2px;"></span>
							<div style="flex: 1;">
								<h4 style="margin: 0 0 5px; font-size: 13px; font-weight: 600; color: #1d2327;">
									<?php echo esc_html( $tip['title'] ); ?>
								</h4>
								<p style="margin: 0 0 10px; font-size: 12px; color: #646970; line-height: 1.5;">
									<?php echo esc_html( $tip['description'] ); ?>
								</p>
								<div style="display: flex; gap: 8px; align-items: center;">
									<button 
										class="button button-primary button-small wps-apply-tip" 
										data-tip-id="<?php echo esc_attr( $tip['id'] ); ?>"
										data-action="<?php echo esc_attr( $tip['action'] ); ?>"
										style="font-size: 12px; padding: 2px 10px; height: auto;">
										<?php echo esc_html( $tip['action_label'] ); ?>
									</button>
									<button 
										class="button button-link wps-dismiss-tip" 
										data-tip-id="<?php echo esc_attr( $tip['id'] ); ?>"
										style="font-size: 11px; color: #646970; text-decoration: none;">
										<?php esc_html_e( 'Dismiss', 'plugin-wpshadow' ); ?>
									</button>
								</div>
							</div>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>

		<script type="text/javascript">
		jQuery(document).ready(function($) {
			const ajaxUrl = '<?php echo esc_js( admin_url( 'admin-ajax.php' ) ); ?>';
			const nonce = '<?php echo esc_js( wp_create_nonce( 'wpshadow_tips_coach' ) ); ?>';

			// Handle tip action
			$('.wps-apply-tip').on('click', function(e) {
				e.preventDefault();
				const $btn = $(this);
				const tipId = $btn.data('tip-id');
				const action = $btn.data('action');
				const $card = $btn.closest('.wps-tip-card');

				$btn.prop('disabled', true).text('<?php echo esc_js( __( 'Processing...', 'plugin-wpshadow' ) ); ?>');

				$.ajax({
					url: ajaxUrl,
					type: 'POST',
					data: {
						action: 'wpshadow_apply_tip_action',
						nonce: nonce,
						tip_id: tipId,
						action_type: action
					},
					success: function(response) {
						if (response.success && response.data.redirect) {
							window.location.href = response.data.redirect;
						} else if (response.success) {
							$card.fadeOut(300, function() { $(this).remove(); });
						} else {
							alert(response.data.message || '<?php echo esc_js( __( 'An error occurred', 'plugin-wpshadow' ) ); ?>');
							$btn.prop('disabled', false).text('<?php echo esc_js( __( 'Try Again', 'plugin-wpshadow' ) ); ?>');
						}
					},
					error: function() {
						alert('<?php echo esc_js( __( 'An error occurred. Please try again.', 'plugin-wpshadow' ) ); ?>');
						$btn.prop('disabled', false).text('<?php echo esc_js( __( 'Try Again', 'plugin-wpshadow' ) ); ?>');
					}
				});
			});

			// Handle tip dismissal
			$('.wps-dismiss-tip').on('click', function(e) {
				e.preventDefault();
				const $btn = $(this);
				const tipId = $btn.data('tip-id');
				const $card = $btn.closest('.wps-tip-card');

				$btn.prop('disabled', true);

				$.ajax({
					url: ajaxUrl,
					type: 'POST',
					data: {
						action: 'wpshadow_dismiss_tip',
						nonce: nonce,
						tip_id: tipId
					},
					success: function(response) {
						if (response.success) {
							$card.fadeOut(300, function() { 
								$(this).remove();
								// If no more tips, hide the entire widget
								if ($('.wps-tip-card').length === 0) {
									$('.wps-tips-coach').closest('.postbox').fadeOut();
								}
							});
						} else {
							alert(response.data.message || '<?php echo esc_js( __( 'An error occurred', 'plugin-wpshadow' ) ); ?>');
							$btn.prop('disabled', false);
						}
					},
					error: function() {
						alert('<?php echo esc_js( __( 'An error occurred. Please try again.', 'plugin-wpshadow' ) ); ?>');
						$btn.prop('disabled', false);
					}
				});
			});
		});
		</script>
		<?php
	}

	/**
	 * Get human-readable label for site type.
	 *
	 * @param string $site_type Site type identifier.
	 * @return string Human-readable label.
	 */
	private static function get_site_type_label( string $site_type ): string {
		switch ( $site_type ) {
			case self::TYPE_BLOG:
				return __( 'Blog', 'plugin-wpshadow' );
			case self::TYPE_WOOCOMMERCE:
				return __( 'E-commerce Store', 'plugin-wpshadow' );
			case self::TYPE_LMS:
				return __( 'Learning Management System', 'plugin-wpshadow' );
			default:
				return __( 'Website', 'plugin-wpshadow' );
		}
	}
}
