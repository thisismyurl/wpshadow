<?php
/**
 * Feature: Third-Party Plugin Asset Cleanup
 *
 * Selectively dequeue unnecessary CSS/JS from common plugins
 * (Jetpack, RankMath, etc.) that load globally but aren't used on every page.
 *
 * @package WPS\CoreSupport\Features
 * @since 1.2601.73001
 */

declare(strict_types=1);

namespace WPS\CoreSupport\Features;


/**
 * WPS_Feature_Plugin_Cleanup
 *
 * Removes unnecessary plugin assets.
 */
final class WPS_Feature_Plugin_Cleanup extends WPS_Abstract_Feature {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'id'                  => 'plugin-cleanup',
				'name'                => __( 'Third-Party Plugin Asset Cleanup', 'plugin-wp-support-thisismyurl' ),
				'description'         => __( 'Remove unused CSS/JS from Jetpack, RankMath, and other plugins', 'plugin-wp-support-thisismyurl' ),
				'scope'               => 'core',
				'default_enabled'     => false,
				'version'             => '1.0.0',
				'widget_group'        => 'admin-management',
				'widget_label'        => __( 'Admin Management', 'plugin-wp-support-thisismyurl' ),
				'widget_description'  => __( 'Clean up admin interface and plugin management', 'plugin-wp-support-thisismyurl' ),
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

		add_action( 'wp_enqueue_scripts', array( $this, 'cleanup_plugin_assets' ), 10 );
	}

	/**
	 * Cleanup plugin assets.
	 *
	 * @return void
	 */
	public function cleanup_plugin_assets(): void {
		if ( is_admin() || wp_doing_ajax() ) {
			return;
		}

		$cleanup_options = (array) get_option( 'wps_plugin_cleanup_options', $this->get_default_options() );

		// Jetpack cleanup.
		if ( $cleanup_options['jetpack'] ?? false ) {
			wp_dequeue_style( 'jetpack_related-posts' );
			wp_dequeue_style( 'sharedaddy' );
			wp_dequeue_style( 'social-logos' );
			wp_dequeue_style( 'jetpack-sharing-buttons-style' );
		}

		// RankMath cleanup.
		if ( $cleanup_options['rankmath'] ?? false ) {
			wp_dequeue_style( 'rank-math-toc-block-css' );
			wp_dequeue_style( 'rank-math-faq-block-css' );
			wp_dequeue_script( 'rank-math-json' );
			wp_deregister_script( 'rank-math-json' );

			// Admin bar styles (only if logged in).
			if ( is_user_logged_in() ) {
				wp_dequeue_style( 'rank-math-analytics-stats-css' );
				wp_dequeue_style( 'rank-math-analytics-pro-stats-css' );
			}
		}

		// RankMath credit notice.
		add_filter( 'rank_math/frontend/remove_credit_notice', '__return_true' );

		// Theme-specific cleanup.
		if ( $cleanup_options['theme_cleanup'] ?? false ) {
			wp_dequeue_style( 'mediaelement' );
			wp_dequeue_style( 'wp-mediaelement' );
		}
	}

	/**
	 * Get default cleanup options.
	 *
	 * @return array Default options.
	 */
	private function get_default_options(): array {
		return array(
			'jetpack'       => false,
			'rankmath'      => false,
			'theme_cleanup' => false,
		);
	}
}

