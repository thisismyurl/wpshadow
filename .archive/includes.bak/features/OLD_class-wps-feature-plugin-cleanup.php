<?php declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class WPSHADOW_Feature_Plugin_Cleanup extends WPSHADOW_Abstract_Feature {

	public function __construct() {
		parent::__construct( array(
			'id'          => 'plugin-cleanup',
			'name'        => __( 'Stop Unused Plugin Code', 'wpshadow' ),
			'description' => __( 'Remove extra code from popular plugins that loads on every page but isn\'t always needed.', 'wpshadow' ),
			'aliases'     => array( 'plugin optimization', 'jetpack cleanup', 'rankmath cleanup', 'yoast cleanup', 'contact form 7', 'woocommerce optimization', 'plugin bloat', 'dequeue scripts', 'plugin performance', 'third party cleanup', 'plugin assets', 'script optimization' ),
			'sub_features' => array(
				'jetpack_cleanup'     => array(
					'name'               => __( 'Remove Jetpack Assets', 'wpshadow' ),
					'description_short'  => __( 'Disable unused Jetpack plugin code', 'wpshadow' ),
					'description_long'   => __( 'Removes CSS and JavaScript from Jetpack that loads on every page. Jetpack is a popular plugin but often loads code even when specific features aren\'t used. This removes related posts CSS, sharing buttons CSS, and other Jetpack assets from pages that don\'t need them. Significantly improves performance on sites with Jetpack installed but unused features.', 'wpshadow' ),
					'description_wizard' => __( 'Jetpack loads lots of code by default. If you don\'t use its features, removing them saves bandwidth and speeds up your site.', 'wpshadow' ),
					'default_enabled'    => true,
				),
				'rankmath_cleanup'    => array(
					'name'               => __( 'Remove RankMath Assets', 'wpshadow' ),
					'description_short'  => __( 'Disable unused RankMath SEO code', 'wpshadow' ),
					'description_long'   => __( 'Removes CSS and JavaScript from RankMath SEO plugin that isn\'t needed on the frontend. RankMath is a powerful SEO tool but can load code for features not in use. This removes block CSS, FAQ block CSS, and JSON-LD scripts that only apply to specific features.', 'wpshadow' ),
					'description_wizard' => __( 'RankMath loads SEO code on every page. Remove it from pages where you don\'t use RankMath features to improve performance.', 'wpshadow' ),
					'default_enabled'    => true,
				),
				'cf7_cleanup'         => array(
					'name'               => __( 'Remove Contact Form 7', 'wpshadow' ),
					'description_short'  => __( 'Disable Contact Form 7 assets on non-form pages', 'wpshadow' ),
					'description_long'   => __( 'Removes Contact Form 7 CSS and JavaScript from pages that don\'t contain contact forms. By default, Contact Form 7 loads its CSS and JavaScript on every page. This optimization only loads it on pages that actually use contact forms, significantly improving performance on form-light sites.', 'wpshadow' ),
					'description_wizard' => __( 'Contact Form 7 loads on every page even if there\'s no form. This removes it from pages without forms to save bandwidth.', 'wpshadow' ),
					'default_enabled'    => true,
				),
				'woocommerce_cleanup' => array(
					'name'               => __( 'Remove WooCommerce Assets', 'wpshadow' ),
					'description_short'  => __( 'Disable WooCommerce code on non-store pages', 'wpshadow' ),
					'description_long'   => __( 'Removes WooCommerce CSS and JavaScript from non-store pages. WooCommerce loads cart and product code on every page by default. This optimization only loads it on product pages and cart/checkout pages. Significant bandwidth savings on content-heavy sites with minimal e-commerce.', 'wpshadow' ),
					'description_wizard' => __( 'WooCommerce loads store code everywhere by default. If you only sell on specific pages, this removes the overhead from blog posts and other content pages.', 'wpshadow' ),
					'default_enabled'    => false,
				),
				'yoast_cleanup'       => array(
					'name'               => __( 'Remove Yoast SEO', 'wpshadow' ),
					'description_short'  => __( 'Disable Yoast SEO frontend code', 'wpshadow' ),
					'description_long'   => __( 'Removes frontend CSS and JavaScript from Yoast SEO plugin. Yoast is primarily an admin tool but loads some frontend code. This optimization removes unnecessary frontend assets while keeping the SEO features working in the admin area where they\'re actually used.', 'wpshadow' ),
					'description_wizard' => __( 'Yoast is an admin tool but loads code on the frontend unnecessarily. Remove it from visitor pages to improve speed.', 'wpshadow' ),
					'default_enabled'    => true,
				),
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

	public function cleanup_plugin_assets(): void {
		if ( is_admin() || wp_doing_ajax() ) {
			return;
		}

		$removed = array();

		if ( $this->is_sub_feature_enabled( 'jetpack_cleanup', true ) ) {
			wp_dequeue_style( 'jetpack_related-posts' );
			wp_dequeue_style( 'sharedaddy' );
			wp_dequeue_style( 'jetpack-sharing-buttons-style' );
			$removed[] = 'jetpack_related-posts';
			$removed[] = 'sharedaddy';
			$removed[] = 'jetpack-sharing-buttons-style';
		}

		if ( $this->is_sub_feature_enabled( 'rankmath_cleanup', true ) ) {
			wp_dequeue_style( 'rank-math-toc-block-css' );
			wp_dequeue_style( 'rank-math-faq-block-css' );
			wp_dequeue_script( 'rank-math-json' );
			wp_deregister_script( 'rank-math-json' );
			$removed[] = 'rank-math-toc-block-css';
			$removed[] = 'rank-math-faq-block-css';
			$removed[] = 'rank-math-json';
		}

		if ( $this->is_sub_feature_enabled( 'cf7_cleanup', true ) ) {
			$this->cleanup_contact_form_7();
		}

		if ( $this->is_sub_feature_enabled( 'woocommerce_cleanup', false ) ) {
			$this->cleanup_woocommerce();
		}

		if ( $this->is_sub_feature_enabled( 'yoast_cleanup', true ) ) {
			$this->cleanup_yoast();
		}

		if ( ! empty( $removed ) ) {
			do_action( 'wpshadow_plugin_cleanup_removed', array_unique( $removed ) );
		}
	}

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

	private function cleanup_yoast(): void {
		if ( ! defined( 'WPSEO_VERSION' ) ) {
			return;
		}

		wp_dequeue_style( 'yoast-seo-adminbar' );

		wp_dequeue_style( 'yoast-seo-frontend' );

		wp_dequeue_script( 'yoast-seo-frontend' );
		wp_dequeue_script( 'yoast-seo-analysis' );

		wp_dequeue_style( 'yoast-schema-graph' );

		do_action( 'wpshadow_plugin_cleanup_yoast_removed' );
	}

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
