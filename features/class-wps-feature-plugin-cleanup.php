<?php declare(strict_types=1);
/**
 * Feature: Third-Party Plugin Asset Cleanup
 *
 * Selectively dequeue unnecessary CSS/JS from common plugins that load globally but aren't used on every page.
 *
 * @package    WPShadow\CoreSupport
 * @subpackage Features
 * @since      1.2601.73001
 */

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Removes unnecessary plugin assets.
 */
final class WPSHADOW_Feature_Plugin_Cleanup extends WPSHADOW_Abstract_Feature {

	/**
	 * Cache for plugin availability checks.
	 *
	 * @var array<string, bool>
	 */
	private array $plugin_availability;

	public function __construct() {
		$this->plugin_availability = array(
			'contact_form_7' => function_exists( 'wpcf7_contact_form' ),
			'woocommerce'    => function_exists( 'is_woocommerce' ),
		);

		parent::__construct(
			array(
				'id'                 => 'plugin-cleanup',
				'name'               => __( 'Third-Party Plugin Asset Cleanup', 'wpshadow' ),
				'description'        => __( "Stop loading plugin files where they're not needed - speed up pages by removing bloat.", 'wpshadow' ),
				'scope'              => 'core',
				'default_enabled'    => false,
				'version'            => '1.0.0',
				'widget_group'       => 'performance',
				'license_level'      => 1,
				'minimum_capability' => 'manage_options',
				'icon'               => 'dashicons-admin-plugins',
				'category'           => 'performance',
				'priority'           => 20,
				'sub_features'       => array(
					'jetpack_cleanup'     => array(
						'name'            => __( 'Jetpack Asset Cleanup', 'wpshadow' ),
						'description'     => __( 'Remove Jetpack assets like Related Posts and Sharing styles on pages that do not need them.', 'wpshadow' ),
						'default_enabled' => true,
					),
					'rankmath_cleanup'    => array(
						'name'            => __( 'RankMath Asset Cleanup', 'wpshadow' ),
						'description'     => __( 'Skip RankMath front-end assets when the related blocks are not present.', 'wpshadow' ),
						'default_enabled' => true,
					),
					'cf7_cleanup'         => array(
						'name'            => __( 'Contact Form 7 Cleanup', 'wpshadow' ),
						'description'     => __( 'Only load Contact Form 7 scripts and styles on pages with a form.', 'wpshadow' ),
						'default_enabled' => true,
					),
					'woocommerce_cleanup' => array(
						'name'            => __( 'WooCommerce Asset Cleanup', 'wpshadow' ),
						'description'     => __( 'Only load WooCommerce assets on shop, cart, checkout, or account pages.', 'wpshadow' ),
						'default_enabled' => false,
					),
					'yoast_cleanup'       => array(
						'name'            => __( 'Yoast SEO Cleanup', 'wpshadow' ),
						'description'     => __( 'Remove Yoast front-end assets like block CSS when not needed.', 'wpshadow' ),
						'default_enabled' => true,
					),
					'theme_cleanup'       => array(
						'name'            => __( 'Theme MediaElement Cleanup', 'wpshadow' ),
						'description'     => __( 'Remove unused MediaElement styles added by some themes.', 'wpshadow' ),
						'default_enabled' => false,
					),
				),
			)
		);
	}

	public function has_details_page(): bool {
		return true;
	}

	public function register(): void {
		if ( ! $this->is_enabled() ) {
			return;
		}

		add_action( 'wp_enqueue_scripts', array( $this, 'cleanup_plugin_assets' ), 10 );
		add_filter( 'site_status_tests', array( $this, 'register_site_health_test' ) );
	}

	/**
	 * Cleanup plugin assets.
	 */
	public function cleanup_plugin_assets(): void {
		if ( is_admin() || wp_doing_ajax() ) {
			return;
		}

		// Jetpack cleanup.
		if ( $this->is_sub_feature_enabled( 'jetpack_cleanup', true ) ) {
			wp_dequeue_style( 'jetpack_related-posts' );
			wp_dequeue_style( 'sharedaddy' );
			wp_dequeue_style( 'social-logos' );
			wp_dequeue_style( 'jetpack-sharing-buttons-style' );
		}

		// RankMath cleanup.
		if ( $this->is_sub_feature_enabled( 'rankmath_cleanup', true ) ) {
			wp_dequeue_style( 'rank-math-toc-block-css' );
			wp_dequeue_style( 'rank-math-faq-block-css' );
			wp_dequeue_script( 'rank-math-json' );
			wp_deregister_script( 'rank-math-json' );

			if ( is_user_logged_in() ) {
				wp_dequeue_style( 'rank-math-analytics-stats-css' );
				wp_dequeue_style( 'rank-math-analytics-pro-stats-css' );
			}
		}

		add_filter( 'rank_math/frontend/remove_credit_notice', '__return_true' );

		// Contact Form 7 cleanup - only load on pages with forms.
		if ( $this->is_sub_feature_enabled( 'cf7_cleanup', true ) ) {
			$this->cleanup_contact_form_7();
		}

		// WooCommerce cleanup - only load on shop/product pages.
		if ( $this->is_sub_feature_enabled( 'woocommerce_cleanup', false ) ) {
			$this->cleanup_woocommerce();
		}

		// Yoast cleanup.
		if ( $this->is_sub_feature_enabled( 'yoast_cleanup', true ) ) {
			wp_dequeue_style( 'yoast-seo-blocks' );
		}

		// Theme-specific cleanup.
		if ( $this->is_sub_feature_enabled( 'theme_cleanup', false ) ) {
			wp_dequeue_style( 'mediaelement' );
			wp_dequeue_style( 'wp-mediaelement' );
		}
	}

	/**
	 * Cleanup Contact Form 7 assets when not needed.
	 */
	private function cleanup_contact_form_7(): void {
		if ( ! ( $this->plugin_availability['contact_form_7'] ?? false ) ) {
			return;
		}

		$has_cf7_form = false;

		$post = get_post();
		if ( $post instanceof \WP_Post ) {
			$has_cf7_form = has_shortcode( $post->post_content, 'contact-form-7' );
		}

		if ( ! $has_cf7_form && is_active_sidebar( 'sidebar-1' ) ) {
			global $wp_registered_sidebars, $wp_registered_widgets;

			$widgets_by_sidebar = wp_get_sidebars_widgets();

			foreach ( (array) $wp_registered_sidebars as $sidebar_id => $sidebar ) {
				if ( empty( $widgets_by_sidebar[ $sidebar_id ] ) || ! is_array( $widgets_by_sidebar[ $sidebar_id ] ) ) {
					continue;
				}

				foreach ( $widgets_by_sidebar[ $sidebar_id ] as $widget_id ) {
					$widget = $wp_registered_widgets[ $widget_id ] ?? null;
					if ( ! is_array( $widget ) ) {
						continue;
					}

					$callback_object = $widget['callback'][0] ?? null;
					if ( ! is_object( $callback_object ) || ! property_exists( $callback_object, 'option_name' ) ) {
						continue;
					}

					$option_name = $callback_object->option_name;
					$option_value = is_string( $option_name ) ? get_option( $option_name ) : null;

					if ( is_array( $option_value ) ) {
						foreach ( $option_value as $instance ) {
							if ( ! is_array( $instance ) ) {
								continue;
							}

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

		$has_cf7_form = apply_filters( 'wpshadow_has_contact_form_7', $has_cf7_form );

		if ( ! $has_cf7_form ) {
			wp_dequeue_style( 'contact-form-7' );
			wp_dequeue_script( 'contact-form-7' );
			wp_deregister_style( 'contact-form-7' );
			wp_deregister_script( 'contact-form-7' );
		}
	}

	/**
	 * Cleanup WooCommerce assets when not needed.
	 */
	private function cleanup_woocommerce(): void {
		if ( ! ( $this->plugin_availability['woocommerce'] ?? false ) ) {
			return;
		}

		$is_woo_page = false;
		if ( function_exists( 'is_woocommerce' ) && function_exists( 'is_cart' ) && function_exists( 'is_checkout' ) && function_exists( 'is_account_page' ) ) {
			$is_woo_page = is_woocommerce() || is_cart() || is_checkout() || is_account_page();
		}

		$is_woo_page = apply_filters( 'wpshadow_is_woocommerce_page', $is_woo_page );

		if ( $is_woo_page ) {
			return;
		}

		wp_dequeue_style( 'woocommerce-general' );
		wp_dequeue_style( 'woocommerce-layout' );
		wp_dequeue_style( 'woocommerce-smallscreen' );
		wp_deregister_style( 'woocommerce-general' );
		wp_deregister_style( 'woocommerce-layout' );
		wp_deregister_style( 'woocommerce-smallscreen' );

		wp_dequeue_script( 'wc-cart-fragments' );
		wp_dequeue_script( 'woocommerce' );
		wp_deregister_script( 'wc-cart-fragments' );
		wp_deregister_script( 'woocommerce' );
	}

	/**
	 * Register Site Health test.
	 *
	 * @param array<string, mixed> $tests Array of Site Health tests.
	 * @return array<string, mixed>
	 */
	public function register_site_health_test( array $tests ): array {
		$tests['direct']['plugin_cleanup'] = array(
			'label' => __( 'Plugin Asset Cleanup', 'wpshadow' ),
			'test'  => array( $this, 'test_plugin_cleanup' ),
		);

		return $tests;
	}

	/**
	 * Site Health test for plugin cleanup.
	 *
	 * @return array<string, mixed>
	 */
	public function test_plugin_cleanup(): array {
		if ( ! $this->is_enabled() ) {
			return array(
				'label'       => __( 'Plugin Asset Cleanup', 'wpshadow' ),
				'status'      => 'recommended',
				'badge'       => array(
					'label' => __( 'Performance', 'wpshadow' ),
					'color' => 'gray',
				),
				'description' => sprintf( '<p>%s</p>', __( 'Plugin asset cleanup is not enabled. Dequeuing unused plugin files can improve performance.', 'wpshadow' ) ),
				'actions'     => '',
				'test'        => 'plugin_cleanup',
			);
		}

		$enabled = 0;
		$enabled += $this->is_sub_feature_enabled( 'jetpack_cleanup', true ) ? 1 : 0;
		$enabled += $this->is_sub_feature_enabled( 'rankmath_cleanup', true ) ? 1 : 0;
		$enabled += $this->is_sub_feature_enabled( 'cf7_cleanup', true ) ? 1 : 0;
		$enabled += $this->is_sub_feature_enabled( 'woocommerce_cleanup', false ) ? 1 : 0;
		$enabled += $this->is_sub_feature_enabled( 'yoast_cleanup', true ) ? 1 : 0;
		$enabled += $this->is_sub_feature_enabled( 'theme_cleanup', false ) ? 1 : 0;

		$status = $enabled >= 4 ? 'good' : 'recommended';

		return array(
			'label'       => __( 'Plugin Asset Cleanup', 'wpshadow' ),
			'status'      => $status,
			'badge'       => array(
				'label' => __( 'Performance', 'wpshadow' ),
				'color' => 'blue',
			),
			'description' => sprintf(
				'<p>%s</p>',
				sprintf(
					/* translators: %d: number of enabled cleanup features */
					__( '%d plugin asset cleanup features are enabled, preventing unnecessary scripts and styles from loading.', 'wpshadow' ),
					$enabled
				)
			),
			'actions'     => '',
			'test'        => 'plugin_cleanup',
		);
	}
}
