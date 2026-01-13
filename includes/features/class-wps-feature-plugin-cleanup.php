<?php
/**
 * Feature: Third-Party Plugin Asset Cleanup
 *
 * Selectively dequeue unnecessary CSS/JS from common plugins
 * (Contact Form 7, WooCommerce, Jetpack, RankMath, etc.) that load globally but aren't used on every page.
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
	 * Cache for plugin availability checks.
	 *
	 * @var array
	 */
	private array $plugin_availability = array();

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'id'                  => 'plugin-cleanup',
				'name'                => __( 'Third-Party Plugin Asset Cleanup', 'plugin-wp-support-thisismyurl' ),
				'description'         => __( 'Remove unused CSS/JS from Contact Form 7, WooCommerce, Jetpack, RankMath, and other plugins', 'plugin-wp-support-thisismyurl' ),
				'scope'               => 'core',
				'default_enabled'     => false,
				'version'             => '1.0.0',
				'widget_group'        => 'admin-management',
				'widget_label'        => __( 'Admin Management', 'plugin-wp-support-thisismyurl' ),
				'widget_description'  => __( 'Clean up admin interface and plugin management', 'plugin-wp-support-thisismyurl' ),
			)
		);

		// Cache plugin availability checks.
		$this->plugin_availability = array(
			'contact_form_7' => function_exists( 'wpcf7_contact_form' ),
			'woocommerce'    => function_exists( 'is_woocommerce' ),
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

		// Contact Form 7 cleanup - only load on pages with forms.
		if ( $cleanup_options['contact_form_7'] ?? false ) {
			$this->cleanup_contact_form_7();
		}

		// WooCommerce cleanup - only load on shop/product pages.
		if ( $cleanup_options['woocommerce'] ?? false ) {
			$this->cleanup_woocommerce();
		}

		// Theme-specific cleanup.
		if ( $cleanup_options['theme_cleanup'] ?? false ) {
			wp_dequeue_style( 'mediaelement' );
			wp_dequeue_style( 'wp-mediaelement' );
		}
	}

	/**
	 * Cleanup Contact Form 7 assets when not needed.
	 *
	 * @return void
	 */
	private function cleanup_contact_form_7(): void {
		// Check if Contact Form 7 is active.
		if ( ! $this->plugin_availability['contact_form_7'] ) {
			return;
		}

		$has_cf7_form = false;

		// Check main post content.
		$post = get_post();
		if ( is_a( $post, 'WP_Post' ) ) {
			$has_cf7_form = has_shortcode( $post->post_content, 'contact-form-7' );
		}

		// Check widgets and sidebars for CF7 shortcodes if not found in main content.
		if ( ! $has_cf7_form && is_active_sidebar( 'sidebar-1' ) ) {
			global $wp_registered_sidebars, $wp_registered_widgets;

			// Check all active sidebars for CF7 shortcode widgets.
			foreach ( $wp_registered_sidebars as $sidebar_id => $sidebar ) {
				if ( ! is_active_sidebar( $sidebar_id ) ) {
					continue;
				}

				$widgets = wp_get_sidebars_widgets();
				if ( ! isset( $widgets[ $sidebar_id ] ) || ! is_array( $widgets[ $sidebar_id ] ) ) {
					continue;
				}

				foreach ( $widgets[ $sidebar_id ] as $widget_id ) {
					if ( ! isset( $wp_registered_widgets[ $widget_id ] ) ) {
						continue;
					}

					// Check text widgets and custom HTML widgets for CF7 shortcodes.
					$widget = $wp_registered_widgets[ $widget_id ];
					if ( isset( $widget['callback'][0] ) && is_object( $widget['callback'][0] ) ) {
						$widget_instance = get_option( $widget['callback'][0]->option_name );
						if ( is_array( $widget_instance ) ) {
							foreach ( $widget_instance as $instance ) {
								if ( is_array( $instance ) ) {
									$text = $instance['text'] ?? $instance['content'] ?? '';
									if ( is_string( $text ) && has_shortcode( $text, 'contact-form-7' ) ) {
										$has_cf7_form = true;
										break 3;
									}
								}
							}
						}
					}
				}
			}
		}

		// Allow filtering of the check for custom use cases.
		$has_cf7_form = apply_filters( 'wps_has_contact_form_7', $has_cf7_form );

		// Dequeue if no form present.
		if ( ! $has_cf7_form ) {
			wp_dequeue_style( 'contact-form-7' );
			wp_dequeue_script( 'contact-form-7' );
			wp_deregister_style( 'contact-form-7' );
			wp_deregister_script( 'contact-form-7' );
		}
	}

	/**
	 * Cleanup WooCommerce assets when not needed.
	 *
	 * @return void
	 */
	private function cleanup_woocommerce(): void {
		// Check if WooCommerce is active.
		if ( ! $this->plugin_availability['woocommerce'] ) {
			return;
		}

		// Check if we're on a WooCommerce page.
		$is_woo_page = is_woocommerce() || is_cart() || is_checkout() || is_account_page();

		// Allow filtering of the check for custom use cases.
		$is_woo_page = apply_filters( 'wps_is_woocommerce_page', $is_woo_page );

		// Dequeue if not on a WooCommerce page.
		if ( ! $is_woo_page ) {
			// Dequeue WooCommerce styles.
			wp_dequeue_style( 'woocommerce-general' );
			wp_dequeue_style( 'woocommerce-layout' );
			wp_dequeue_style( 'woocommerce-smallscreen' );
			wp_deregister_style( 'woocommerce-general' );
			wp_deregister_style( 'woocommerce-layout' );
			wp_deregister_style( 'woocommerce-smallscreen' );

			// Dequeue WooCommerce scripts.
			wp_dequeue_script( 'wc-cart-fragments' );
			wp_dequeue_script( 'woocommerce' );
			wp_deregister_script( 'wc-cart-fragments' );
			wp_deregister_script( 'woocommerce' );
		}
	}

	/**
	 * Get default cleanup options.
	 *
	 * @return array Default options.
	 */
	private function get_default_options(): array {
		return array(
			'jetpack'         => false,
			'rankmath'        => false,
			'contact_form_7'  => false,
			'woocommerce'     => false,
			'theme_cleanup'   => false,
		);
	}
}

