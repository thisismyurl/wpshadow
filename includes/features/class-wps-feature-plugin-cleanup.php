<?php declare(strict_types=1);
/**
 * Feature: Third-Party Plugin Asset Cleanup
 *
 * Selectively dequeue unnecessary CSS/JS from common plugins.
 *
 * @package WPShadow\CoreSupport
 * @since 1.2601.75001
 */

namespace WPShadow\CoreSupport;

final class WPSHADOW_Feature_Plugin_Cleanup extends WPSHADOW_Abstract_Feature {

	public function __construct() {
		parent::__construct( array(
			'id'          => 'plugin-cleanup',
			'name'        => __( 'Stop Unused Plugin Code', 'wpshadow' ),
			'description' => __( 'Remove extra code from popular plugins that loads on every page but isn\'t always needed.', 'wpshadow' ),
			'aliases'     => array( 'plugin optimization', 'jetpack cleanup', 'rankmath cleanup', 'yoast cleanup', 'contact form 7', 'woocommerce optimization', 'plugin bloat', 'dequeue scripts', 'plugin performance', 'third party cleanup', 'plugin assets', 'script optimization' ),
			'sub_features' => array(
				'jetpack_cleanup'     => __( 'Remove unused Jetpack code', 'wpshadow' ),
				'rankmath_cleanup'    => __( 'Remove unused RankMath code', 'wpshadow' ),
				'cf7_cleanup'         => __( 'Remove unused Contact Form 7 code', 'wpshadow' ),
				'woocommerce_cleanup' => __( 'Remove unused WooCommerce code', 'wpshadow' ),
				'yoast_cleanup'       => __( 'Remove unused Yoast SEO code', 'wpshadow' ),
			),
		) );

		$this->register_default_settings( array(
			'jetpack_cleanup'     => true,
			'rankmath_cleanup'    => true,
			'cf7_cleanup'         => true,
			'woocommerce_cleanup' => false,
			'yoast_cleanup'       => true,
		) );
	}

	public function register(): void {
		if ( ! $this->is_enabled() ) {
			return;
		}

		add_action( 'wp_enqueue_scripts', array( $this, 'cleanup_plugin_assets' ), 10 );
		add_filter( 'site_status_tests', array( $this, 'register_site_health_test' ) );

		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			\WP_CLI::add_command( 'wpshadow plugin-cleanup', array( $this, 'handle_cli_command' ) );
		}
	}

	/**
	 * Cleanup plugin assets.
	 */
	public function cleanup_plugin_assets(): void {
		if ( is_admin() || wp_doing_ajax() ) {
			return;
		}

		$removed = array();

		// Jetpack
		if ( $this->is_sub_feature_enabled( 'jetpack_cleanup', true ) ) {
			wp_dequeue_style( 'jetpack_related-posts' );
			wp_dequeue_style( 'sharedaddy' );
			wp_dequeue_style( 'jetpack-sharing-buttons-style' );
			$removed[] = 'jetpack_related-posts';
			$removed[] = 'sharedaddy';
			$removed[] = 'jetpack-sharing-buttons-style';
		}

		// RankMath
		if ( $this->is_sub_feature_enabled( 'rankmath_cleanup', true ) ) {
			wp_dequeue_style( 'rank-math-toc-block-css' );
			wp_dequeue_style( 'rank-math-faq-block-css' );
			wp_dequeue_script( 'rank-math-json' );
			wp_deregister_script( 'rank-math-json' );
			$removed[] = 'rank-math-toc-block-css';
			$removed[] = 'rank-math-faq-block-css';
			$removed[] = 'rank-math-json';
		}

		// Contact Form 7 - only load on pages with forms
		if ( $this->is_sub_feature_enabled( 'cf7_cleanup', true ) ) {
			$this->cleanup_contact_form_7();
		}

		// WooCommerce - only load on shop/product pages
		if ( $this->is_sub_feature_enabled( 'woocommerce_cleanup', false ) ) {
			$this->cleanup_woocommerce();
		}

		// Yoast SEO - remove frontend assets.
		if ( $this->is_sub_feature_enabled( 'yoast_cleanup', true ) ) {
			$this->cleanup_yoast();
		}

		if ( ! empty( $removed ) ) {
			do_action( 'wpshadow_plugin_cleanup_removed', array_unique( $removed ) );
		}
	}

	/**
	 * Cleanup Contact Form 7 when not needed.
	 */
	private function cleanup_contact_form_7(): void {
		if ( ! function_exists( 'wpcf7_contact_form' ) ) {
			return;
		}

		$post = get_post();
		$has_cf7 = false;

		if ( $post instanceof \WP_Post ) {
			$has_cf7 = has_shortcode( $post->post_content, 'contact-form-7' );
		}

		$has_cf7 = apply_filters( 'wpshadow_has_contact_form_7', $has_cf7 );

		if ( ! $has_cf7 ) {
			wp_dequeue_style( 'contact-form-7' );
			wp_dequeue_script( 'contact-form-7' );
			do_action( 'wpshadow_plugin_cleanup_cf7_removed' );
		}
	}

	/**
	 * Cleanup WooCommerce when not needed.
	 */
	private function cleanup_woocommerce(): void {
		if ( ! function_exists( 'is_woocommerce' ) ) {
			return;
		}

		$is_woo = is_woocommerce() || is_cart() || is_checkout();
		$is_woo = apply_filters( 'wpshadow_is_woocommerce_page', $is_woo );

		if ( ! $is_woo ) {
			wp_dequeue_style( 'woocommerce-general' );
			wp_dequeue_style( 'woocommerce-layout' );
			wp_dequeue_style( 'woocommerce-smallscreen' );
			wp_dequeue_script( 'wc-cart-fragments' );
			wp_dequeue_script( 'woocommerce' );
			do_action( 'wpshadow_plugin_cleanup_woocommerce_removed' );
		}
	}

	/**
	 * Cleanup Yoast SEO frontend assets.
	 */
	private function cleanup_yoast(): void {
		if ( ! defined( 'WPSEO_VERSION' ) ) {
			return;
		}

		// Remove Yoast admin bar styles on frontend.
		wp_dequeue_style( 'yoast-seo-adminbar' );

		// Remove Yoast frontend CSS.
		wp_dequeue_style( 'yoast-seo-frontend' );

		// Remove Yoast SEO scripts.
		wp_dequeue_script( 'yoast-seo-frontend' );
		wp_dequeue_script( 'yoast-seo-analysis' );

		// Remove inline CSS if present.
		wp_dequeue_style( 'yoast-schema-graph' );

		do_action( 'wpshadow_plugin_cleanup_yoast_removed' );
	}

	/**
	 * Handle WP-CLI command for plugin cleanup.
	 *
	 * @param array $args       Positional arguments.
	 * @param array $assoc_args Associative arguments.
	 *
	 * @return void
	 */
	public function handle_cli_command( array $args, array $assoc_args ): void {
		$action = $args[0] ?? 'status';

		if ( 'status' !== $action ) {
			\WP_CLI::error( __( 'Unknown subcommand. Try: wp wpshadow plugin-cleanup status', 'wpshadow' ) );
			return;
		}

		\WP_CLI::log( __( 'Plugin Cleanup status:', 'wpshadow' ) );
		\WP_CLI::log( sprintf( '  %s: %s', __( 'Feature enabled', 'wpshadow' ), $this->is_enabled() ? 'yes' : 'no' ) );

		$subs = array(
			'jetpack_cleanup',
			'rankmath_cleanup',
			'cf7_cleanup',
			'woocommerce_cleanup',
			'yoast_cleanup',
		);

		foreach ( $subs as $sub ) {
			$enabled = $this->is_sub_feature_enabled( $sub, false );
			\WP_CLI::log( sprintf( '  - %s: %s', $sub, $enabled ? 'on' : 'off' ) );
		}

		\WP_CLI::success( __( 'Plugin cleanup inspected.', 'wpshadow' ) );
	}

	public function register_site_health_test( array $tests ): array {
		$tests['direct']['plugin_cleanup'] = array(
			'label'  => __( 'Plugin Asset Cleanup', 'wpshadow' ),
			'test'   => array( $this, 'test_plugin_cleanup' ),
		);

		return $tests;
	}

	public function test_plugin_cleanup(): array {
		if ( ! $this->is_enabled() ) {
			return array(
				'label'       => __( 'Plugin Asset Cleanup', 'wpshadow' ),
				'status'      => 'recommended',
				'badge'       => array( 'label' => __( 'WPShadow', 'wpshadow' ) ),
				'description' => __( 'Enable plugin cleanup for better performance.', 'wpshadow' ),
				'actions'     => '',
				'test'        => 'plugin_cleanup',
			);
		}

		$enabled_count = 0;
		$subs = array( 'jetpack_cleanup', 'rankmath_cleanup', 'cf7_cleanup', 'woocommerce_cleanup', 'yoast_cleanup' );
		foreach ( $subs as $sub ) {
			if ( $this->is_sub_feature_enabled( $sub, false ) ) {
				$enabled_count++;
			}
		}

		return array(
			'label'       => __( 'Plugin Asset Cleanup', 'wpshadow' ),
			'status'      => $enabled_count >= 3 ? 'good' : 'recommended',
			'badge'       => array( 'label' => __( 'WPShadow', 'wpshadow' ) ),
			'description' => sprintf(
				__( '%d of 5 plugin cleanups enabled.', 'wpshadow' ),
				$enabled_count
			),
			'actions'     => '',
			'test'        => 'plugin_cleanup',
		);
	}
}
