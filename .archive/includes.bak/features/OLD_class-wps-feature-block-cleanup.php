<?php

declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class WPSHADOW_Feature_Block_Cleanup extends WPSHADOW_Abstract_Feature {

	public function __construct() {
		parent::__construct(
			array(
				'id'              => 'block-cleanup',
				'name'            => __( 'Remove Block Editor Code', 'wpshadow' ),
				'description'     => __( 'Stop loading block editor code on pages that don\'t use it. Makes your site faster when you use classic themes.', 'wpshadow' ),
				'scope'           => 'core',
				'default_enabled' => false,
				'version'         => '1.0.0',
				'widget_group'    => 'performance',
				'aliases'         => array( 'gutenberg', 'block editor', 'remove blocks', 'gutenberg cleanup', 'block styles', 'editor cleanup', 'block assets', 'gutenberg optimization', 'classic editor', 'disable gutenberg', 'block library', 'editor performance' ),
				'sub_features'    => array(
					'remove_block_library'   => array(
						'name'        => __( 'Block Library Styles', 'wpshadow' ),
						'description' => __( 'Removes the WordPress block library CSS files if you don\'t use Gutenberg blocks. Saves 2-5KB on every page load.', 'wpshadow' ),
					),
					'remove_global_styles'   => array(
						'name'        => __( 'Global Theme Styles', 'wpshadow' ),
						'description' => __( 'Disables WordPress global theme CSS that only affects the block editor. Saves 3-8KB and reduces style conflicts.', 'wpshadow' ),
					),
					'remove_classic_styles'  => array(
						'name'        => __( 'Classic Theme Styles', 'wpshadow' ),
						'description' => __( 'Removes styles that only apply to the old classic WordPress editor. Not needed if you use modern block editor or custom themes.', 'wpshadow' ),
					),
					'remove_wc_blocks'       => array(
						'name'        => __( 'WooCommerce Block Styles', 'wpshadow' ),
						'description' => __( 'Removes WooCommerce block CSS on non-store pages. Useful if you have WooCommerce but don\'t use its blocks everywhere.', 'wpshadow' ),
					),
					'disable_svg_filters'    => array(
						'name'        => __( 'SVG Filter Code', 'wpshadow' ),
						'description' => __( 'Removes advanced SVG filter effects that WordPress loads automatically. Most sites don\'t need this, saving 1-2KB per page.', 'wpshadow' ),
					),
					'separate_block_assets'  => array(
						'name'        => __( 'Combined Block Assets', 'wpshadow' ),
						'description' => __( 'Bundles all block editor code together instead of loading it separately. Fewer files = faster loading, especially on slow connections.', 'wpshadow' ),
					),
				),
			)
		);

		$this->register_default_settings(
			array(
				'remove_block_library'   => true,
				'remove_global_styles'   => true,
				'remove_classic_styles'  => true,
				'remove_wc_blocks'       => false,
				'disable_svg_filters'    => true,
				'separate_block_assets'  => true,
			)
		);
	}

	public function register(): void {
		if ( ! $this->is_enabled() ) {
			return;
		}

		add_action( 'wp_enqueue_scripts', array( $this, 'remove_block_assets' ), 100 );
		add_action( 'after_setup_theme', array( $this, 'disable_block_features' ) );
		add_filter( 'site_status_tests', array( $this, 'register_site_health_test' ) );

		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			\WP_CLI::add_command( 'wpshadow block-cleanup', array( $this, 'handle_cli_command' ) );
		}
	}

	public function remove_block_assets(): void {
		if ( is_admin() ) {
			return;
		}

		$removed_handles = array();

		if ( $this->is_sub_feature_enabled( 'remove_block_library', true ) ) {
			wp_dequeue_style( 'wp-block-library' );
			wp_dequeue_style( 'wp-block-library-theme' );
			wp_dequeue_style( 'wc-blocks-style' );
			$removed_handles[] = 'wp-block-library';
			$removed_handles[] = 'wp-block-library-theme';
			$removed_handles[] = 'wc-blocks-style';
		}

		if ( $this->is_sub_feature_enabled( 'remove_global_styles', true ) ) {
			wp_dequeue_style( 'global-styles' );
			wp_dequeue_style( 'wp-global-styles' );
			$removed_handles[] = 'global-styles';
			$removed_handles[] = 'wp-global-styles';
		}

		if ( $this->is_sub_feature_enabled( 'remove_classic_styles', true ) ) {
			wp_dequeue_style( 'classic-theme-styles' );
			$removed_handles[] = 'classic-theme-styles';
		}

		if ( $this->is_sub_feature_enabled( 'remove_wc_blocks', false ) ) {
			wp_dequeue_style( 'wc-blocks-style' );
			wp_dequeue_style( 'wc-blocks-style-active-filters' );
			wp_dequeue_style( 'wc-blocks-style-add-to-cart-form' );
			wp_dequeue_style( 'wc-blocks-style-all-products' );
			wp_dequeue_style( 'wc-blocks-style-all-reviews' );
			wp_dequeue_style( 'wc-blocks-style-attribute-filter' );
			wp_dequeue_style( 'wc-blocks-style-breadcrumbs' );
			wp_dequeue_style( 'wc-blocks-style-catalog-sorting' );
			wp_dequeue_style( 'wc-blocks-style-customer-account' );
			wp_dequeue_style( 'wc-blocks-style-featured-category' );
			wp_dequeue_style( 'wc-blocks-style-featured-product' );
			wp_dequeue_style( 'wc-blocks-style-mini-cart' );
			wp_dequeue_style( 'wc-blocks-style-price-filter' );
			wp_dequeue_style( 'wc-blocks-style-product-add-to-cart' );
			wp_dequeue_style( 'wc-blocks-style-product-button' );
			wp_dequeue_style( 'wc-blocks-style-product-categories' );
			wp_dequeue_style( 'wc-blocks-style-product-image' );
			wp_dequeue_style( 'wc-blocks-style-product-image-gallery' );
			wp_dequeue_style( 'wc-blocks-style-product-query' );
			wp_dequeue_style( 'wc-blocks-style-product-results-count' );
			wp_dequeue_style( 'wc-blocks-style-product-reviews' );
			wp_dequeue_style( 'wc-blocks-style-product-sale-badge' );
			wp_dequeue_style( 'wc-blocks-style-product-search' );
			wp_dequeue_style( 'wc-blocks-style-product-sku' );
			wp_dequeue_style( 'wc-blocks-style-product-stock-indicator' );
			wp_dequeue_style( 'wc-blocks-style-product-summary' );
			wp_dequeue_style( 'wc-blocks-style-product-title' );
			wp_dequeue_style( 'wc-blocks-style-rating-filter' );
			wp_dequeue_style( 'wc-blocks-style-reviews-by-category' );
			wp_dequeue_style( 'wc-blocks-style-reviews-by-product' );
			wp_dequeue_style( 'wc-blocks-style-product-details' );
			wp_dequeue_style( 'wc-blocks-style-single-product' );
			wp_dequeue_style( 'wc-blocks-style-stock-filter' );
			wp_dequeue_style( 'wc-blocks-style-cart' );
			wp_dequeue_style( 'wc-blocks-style-checkout' );
			wp_dequeue_style( 'wc-blocks-style-mini-cart-contents' );

			$removed_handles[] = 'wc-blocks-style';
		}

		if ( ! empty( $removed_handles ) ) {
			do_action( 'wpshadow_block_cleanup_removed_handles', array_unique( $removed_handles ) );
		}
	}

	public function disable_block_features(): void {

		if ( $this->is_sub_feature_enabled( 'disable_svg_filters', true ) ) {
			remove_action( 'wp_body_open', 'wp_global_styles_render_svg_filters' );
			remove_action( 'in_admin_header', 'wp_global_styles_render_svg_filters' );
			do_action( 'wpshadow_block_cleanup_disabled_svg_filters' );
		}

		if ( $this->is_sub_feature_enabled( 'separate_block_assets', true ) ) {
			add_filter( 'should_load_separate_core_block_assets', '__return_false' );
			do_action( 'wpshadow_block_cleanup_combined_assets' );
		}
	}

	public function handle_cli_command( array $args, array $assoc_args ): void {
		$action = $args[0] ?? 'status';

		if ( 'status' !== $action ) {
			\WP_CLI::error( __( 'Unknown subcommand. Try: wp wpshadow block-cleanup status', 'wpshadow' ) );
			return;
		}

		\WP_CLI::log( __( 'Block Cleanup status:', 'wpshadow' ) );
		\WP_CLI::log( sprintf( '  %s: %s', __( 'Feature enabled', 'wpshadow' ), $this->is_enabled() ? 'yes' : 'no' ) );

		$subs = array(
			'remove_block_library',
			'remove_global_styles',
			'remove_classic_styles',
			'remove_wc_blocks',
			'disable_svg_filters',
			'separate_block_assets',
		);

		foreach ( $subs as $sub ) {
			$enabled = $this->is_sub_feature_enabled( $sub, false );
			\WP_CLI::log( sprintf( '  - %s: %s', $sub, $enabled ? 'on' : 'off' ) );
		}

		\WP_CLI::success( __( 'Block assets cleanup inspected.', 'wpshadow' ) );
	}

	public function register_site_health_test( array $tests ): array {
		$tests['direct']['block_cleanup'] = array(
			'label' => __( 'Block Editor Cleanup', 'wpshadow' ),
			'test'  => array( $this, 'test_block_cleanup' ),
		);

		return $tests;
	}

	public function test_block_cleanup(): array {
		if ( ! $this->is_enabled() ) {
			return array(
				'label'       => __( 'Block Editor Cleanup', 'wpshadow' ),
				'status'      => 'recommended',
				'badge'       => array(
					'label' => __( 'Performance', 'wpshadow' ),
					'color' => 'blue',
				),
				'description' => __( 'Block cleanup is disabled.', 'wpshadow' ),
				'test'        => 'block_cleanup',
			);
		}

		$enabled_features = 0;

		if ( $this->is_sub_feature_enabled( 'remove_block_library', true ) ) {
			$enabled_features++;
		}
		if ( $this->is_sub_feature_enabled( 'remove_global_styles', true ) ) {
			$enabled_features++;
		}
		if ( $this->is_sub_feature_enabled( 'remove_classic_styles', true ) ) {
			$enabled_features++;
		}
		if ( $this->is_sub_feature_enabled( 'remove_wc_blocks', false ) ) {
			$enabled_features++;
		}
		if ( $this->is_sub_feature_enabled( 'disable_svg_filters', true ) ) {
			$enabled_features++;
		}
		if ( $this->is_sub_feature_enabled( 'separate_block_assets', true ) ) {
			$enabled_features++;
		}

		$status = $enabled_features >= 4 ? 'good' : 'recommended';

		return array(
			'label'       => __( 'Block editor cleanup is optimized', 'wpshadow' ),
			'status'      => $status,
			'badge'       => array(
				'label' => __( 'Performance', 'wpshadow' ),
				'color' => 'blue',
			),
			'description' => sprintf(
				__( '%d block editor cleanup features are enabled, reducing frontend assets.', 'wpshadow' ),
				(int) $enabled_features
			),
			'test'        => 'block_cleanup',
		);
	}
}
