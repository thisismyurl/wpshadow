<?php declare(strict_types=1);
/**
 * Feature: Tips Coach
 *
 * Provides contextual "next best action" cards in the dashboard,
 * personalized by site type (blog, WooCommerce, LMS).
 *
 * @package WPShadow\CoreSupport
 * @since 1.2601.75001
 */

namespace WPShadow\CoreSupport;

final class WPSHADOW_Feature_Tips_Coach extends WPSHADOW_Abstract_Feature {

	private const TYPE_BLOG        = 'blog';
	private const TYPE_WOOCOMMERCE = 'woocommerce';
	private const TYPE_LMS         = 'lms';
	private const TYPE_GENERIC     = 'generic';

	public function __construct() {
		parent::__construct( array(
			'id'          => 'tips-coach',
			'name'        => __( 'Smart Tips Helper', 'wpshadow' ),
			'description' => __( 'Get helpful suggestions customized for your type of website (blog, online store, or course site).', 'wpshadow' ),
			'aliases'     => array( 'tips', 'suggestions', 'best practices', 'recommendations', 'coach', 'help', 'guidance', 'next steps', 'optimization tips', 'site improvement', 'dashboard tips', 'contextual help' ),
			'sub_features' => array(
				'enable_tips'        => __( 'Show helpful tips', 'wpshadow' ),
				'show_site_specific' => __( 'Customize tips for my site type', 'wpshadow' ),
				'auto_dismiss'       => __( 'Hide tips after I complete them', 'wpshadow' ),
				'show_priorities'    => __( 'Show which tips matter most', 'wpshadow' ),
			),
		) );

		$this->register_default_settings( array(
			'enable_tips'        => true,
			'show_site_specific' => true,
			'auto_dismiss'       => true,
			'show_priorities'    => false,
		) );
	}

	public function register(): void {
		if ( ! $this->is_enabled() ) {
			return;
		}

		if ( $this->is_sub_feature_enabled( 'enable_tips', true ) ) {
			add_action( 'wp_ajax_wpshadow_dismiss_tip', array( $this, 'ajax_dismiss_tip' ) );
			add_action( 'wp_ajax_wpshadow_apply_tip_action', array( $this, 'ajax_apply_tip_action' ) );
		}

		add_filter( 'site_status_tests', array( $this, 'register_site_health_test' ) );
	}

	/**
	 * Detect primary site type.
	 */
	private function detect_site_type(): string {
		if ( class_exists( '\WooCommerce' ) || $this->is_plugin_active_safe( 'woocommerce/woocommerce.php' ) ) {
			return self::TYPE_WOOCOMMERCE;
		}

		$lms_plugins = array(
			'sfwd-lms/sfwd_lms.php',
			'lifterlms/lifterlms.php',
			'tutor/tutor.php',
			'learnpress/learnpress.php',
		);

		foreach ( $lms_plugins as $plugin ) {
			if ( $this->is_plugin_active_safe( $plugin ) ) {
				return self::TYPE_LMS;
			}
		}

		$post_count = wp_count_posts( 'post' );
		if ( isset( $post_count->publish ) && $post_count->publish > 5 ) {
			return self::TYPE_BLOG;
		}

		return self::TYPE_GENERIC;
	}

	/**
	 * Get tips for current site.
	 */
	private function get_tips(): array {
		$site_type = $this->detect_site_type();
		$tips = $this->get_common_tips();

		if ( $this->is_sub_feature_enabled( 'show_site_specific', true ) ) {
			switch ( $site_type ) {
				case self::TYPE_BLOG:
					$tips = array_merge( $tips, $this->get_blog_tips() );
					break;
				case self::TYPE_WOOCOMMERCE:
					$tips = array_merge( $tips, $this->get_woocommerce_tips() );
					break;
				case self::TYPE_LMS:
					$tips = array_merge( $tips, $this->get_lms_tips() );
					break;
			}
		}

		// Filter dismissed tips
		$dismissed = get_user_meta( get_current_user_id(), 'wpshadow_dismissed_tips', true );
		if ( is_array( $dismissed ) ) {
			$tips = array_filter(
				$tips,
				function ( $tip ) use ( $dismissed ) {
					return ! in_array( $tip['id'], $dismissed, true );
				}
			);
		}

		// Limit to top 3
		return array_slice( $tips, 0, 3 );
	}

	/**
	 * Get common tips.
	 */
	private function get_common_tips(): array {
		$tips = array();

		if ( ! is_ssl() ) {
			$tips[] = array(
				'id'     => 'enable_ssl',
				'title'  => __( 'Enable HTTPS', 'wpshadow' ),
				'desc'   => __( 'Secure your site with HTTPS for better security and SEO.', 'wpshadow' ),
				'action' => 'open_ssl_info',
				'label'  => __( 'Learn More', 'wpshadow' ),
				'icon'   => 'dashicons-lock',
				'prio'   => 90,
			);
		}

		if ( ! $this->has_backup_plugin() ) {
			$tips[] = array(
				'id'     => 'setup_backups',
				'title'  => __( 'Set Up Backups', 'wpshadow' ),
				'desc'   => __( 'Protect your site with automated backups.', 'wpshadow' ),
				'action' => 'open_backup_info',
				'label'  => __( 'Learn More', 'wpshadow' ),
				'icon'   => 'dashicons-backup',
				'prio'   => 85,
			);
		}

		return $tips;
	}

	/**
	 * Get blog-specific tips.
	 */
	private function get_blog_tips(): array {
		$tips = array();

		if ( ! $this->has_seo_plugin() ) {
			$tips[] = array(
				'id'     => 'install_seo_plugin',
				'title'  => __( 'Optimize for Search Engines', 'wpshadow' ),
				'desc'   => __( 'Install an SEO plugin to improve search rankings.', 'wpshadow' ),
				'action' => 'open_seo_plugins',
				'label'  => __( 'Browse Plugins', 'wpshadow' ),
				'icon'   => 'dashicons-chart-line',
				'prio'   => 80,
			);
		}

		if ( ! $this->has_caching_plugin() ) {
			$tips[] = array(
				'id'     => 'enable_caching',
				'title'  => __( 'Speed Up Your Site', 'wpshadow' ),
				'desc'   => __( 'Enable caching to improve page load times.', 'wpshadow' ),
				'action' => 'open_caching_plugins',
				'label'  => __( 'Browse Plugins', 'wpshadow' ),
				'icon'   => 'dashicons-performance',
				'prio'   => 75,
			);
		}

		return $tips;
	}

	/**
	 * Get WooCommerce-specific tips.
	 */
	private function get_woocommerce_tips(): array {
		$tips = array();

		if ( function_exists( 'WC' ) ) {
			$wc = WC();

			if ( method_exists( $wc->payment_gateways ?? null, 'get_available_payment_gateways' ) ) {
				$gateways = $wc->payment_gateways->get_available_payment_gateways();
				if ( empty( $gateways ) ) {
					$tips[] = array(
						'id'     => 'setup_payment_gateway',
						'title'  => __( 'Configure Payment Gateway', 'wpshadow' ),
						'desc'   => __( 'Set up a payment method to start accepting orders.', 'wpshadow' ),
						'action' => 'open_woo_payments',
						'label'  => __( 'Configure Now', 'wpshadow' ),
						'icon'   => 'dashicons-money-alt',
						'prio'   => 95,
					);
				}
			}
		}

		return $tips;
	}

	/**
	 * Get LMS-specific tips.
	 */
	private function get_lms_tips(): array {
		return array(
			array(
				'id'     => 'create_first_course',
				'title'  => __( 'Create Your First Course', 'wpshadow' ),
				'desc'   => __( 'Start building content for your students.', 'wpshadow' ),
				'action' => 'open_course_creation',
				'label'  => __( 'Create Course', 'wpshadow' ),
				'icon'   => 'dashicons-welcome-learn-more',
				'prio'   => 95,
			),
		);
	}

	/**
	 * Check if plugin is active.
	 */
	private function is_plugin_active_safe( string $plugin ): bool {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			include_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		return is_plugin_active( $plugin );
	}

	/**
	 * Check for SEO plugin.
	 */
	private function has_seo_plugin(): bool {
		$plugins = array(
			'wordpress-seo/wp-seo.php',
			'all-in-one-seo-pack/all_in_one_seo_pack.php',
			'seo-by-rank-math/rank-math.php',
			'autodescription/autodescription.php',
		);

		foreach ( $plugins as $plugin ) {
			if ( $this->is_plugin_active_safe( $plugin ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check for caching plugin.
	 */
	private function has_caching_plugin(): bool {
		$plugins = array(
			'wp-super-cache/wp-cache.php',
			'w3-total-cache/w3-total-cache.php',
			'wp-fastest-cache/wpFastestCache.php',
			'litespeed-cache/litespeed-cache.php',
		);

		foreach ( $plugins as $plugin ) {
			if ( $this->is_plugin_active_safe( $plugin ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check for backup plugin.
	 */
	private function has_backup_plugin(): bool {
		$plugins = array(
			'updraftplus/updraftplus.php',
			'backwpup/backwpup.php',
			'duplicator/duplicator.php',
			'all-in-one-wp-migration/all-in-one-wp-migration.php',
		);

		foreach ( $plugins as $plugin ) {
			if ( $this->is_plugin_active_safe( $plugin ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * AJAX: Dismiss tip.
	 */
	public function ajax_dismiss_tip(): void {
		check_ajax_referer( 'wpshadow_tips_coach' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'msg' => __( 'Permission denied', 'wpshadow' ) ) );
		}

		$tip_id = sanitize_text_field( $_POST['tip_id'] ?? '' );
		if ( empty( $tip_id ) ) {
			wp_send_json_error( array( 'msg' => __( 'Invalid tip ID', 'wpshadow' ) ) );
		}

		$dismissed = get_user_meta( get_current_user_id(), 'wpshadow_dismissed_tips', true );
		if ( ! is_array( $dismissed ) ) {
			$dismissed = array();
		}

		if ( ! in_array( $tip_id, $dismissed, true ) ) {
			$dismissed[] = $tip_id;
			update_user_meta( get_current_user_id(), 'wpshadow_dismissed_tips', $dismissed );
		}

		wp_send_json_success( array( 'msg' => __( 'Tip dismissed', 'wpshadow' ) ) );
	}

	/**
	 * AJAX: Apply tip action.
	 */
	public function ajax_apply_tip_action(): void {
		check_ajax_referer( 'wpshadow_tips_coach' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'msg' => __( 'Permission denied', 'wpshadow' ) ) );
		}

		$action = sanitize_text_field( $_POST['action_type'] ?? '' );
		$tip_id = sanitize_text_field( $_POST['tip_id'] ?? '' );

		if ( empty( $action ) || empty( $tip_id ) ) {
			wp_send_json_error( array( 'msg' => __( 'Invalid parameters', 'wpshadow' ) ) );
		}

		$redirect = $this->get_action_redirect( $action );
		if ( $redirect ) {
			wp_send_json_success( array( 'redirect' => $redirect ) );
		} else {
			wp_send_json_error( array( 'msg' => __( 'Unknown action', 'wpshadow' ) ) );
		}
	}

	/**
	 * Get redirect URL for action.
	 */
	private function get_action_redirect( string $action ): ?string {
		switch ( $action ) {
			case 'open_ssl_info':
			case 'open_general_settings':
				return admin_url( 'options-general.php' );
			case 'open_backup_info':
				return admin_url( 'plugin-install.php?s=backup&tab=search' );
			case 'open_seo_plugins':
				return admin_url( 'plugin-install.php?s=seo&tab=search' );
			case 'open_caching_plugins':
				return admin_url( 'plugin-install.php?s=cache&tab=search' );
			case 'open_woo_payments':
				return admin_url( 'admin.php?page=wc-settings&tab=checkout' );
			case 'open_course_creation':
				if ( $this->is_plugin_active_safe( 'sfwd-lms/sfwd_lms.php' ) ) {
					return admin_url( 'post-new.php?post_type=sfwd-courses' );
				}
				return admin_url( 'edit.php?post_type=courses' );
			default:
				return null;
		}
	}

	public function register_site_health_test( array $tests ): array {
		$tests['direct']['tips_coach'] = array(
			'label'  => __( 'Tips Coach', 'wpshadow' ),
			'test'   => array( $this, 'test_tips_coach' ),
		);

		return $tests;
	}

	public function test_tips_coach(): array {
		if ( ! $this->is_enabled() ) {
			return array(
				'label'       => __( 'Tips Coach', 'wpshadow' ),
				'status'      => 'recommended',
				'badge'       => array( 'label' => __( 'WPShadow', 'wpshadow' ) ),
				'description' => __( 'Enable Tips Coach for personalized recommendations.', 'wpshadow' ),
				'actions'     => '',
				'test'        => 'tips_coach',
			);
		}

		$tips = $this->get_tips();
		$site_type = $this->detect_site_type();

		return array(
			'label'       => __( 'Tips Coach', 'wpshadow' ),
			'status'      => ! empty( $tips ) ? 'good' : 'recommended',
			'badge'       => array( 'label' => __( 'WPShadow', 'wpshadow' ) ),
			'description' => sprintf(
				__( '%d tips available for your %s', 'wpshadow' ),
				count( $tips ),
				$site_type
			),
			'actions'     => '',
			'test'        => 'tips_coach',
		);
	}
}
