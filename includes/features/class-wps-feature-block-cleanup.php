<?php
/**
 * Feature: Gutenberg/Block Editor Asset Cleanup
 *
 * Removes unused Gutenberg and block editor assets.
 *
 * @package WPShadow\CoreSupport
 * @since 1.2601.73001
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport;

/**
 * WPSHADOW_Feature_Block_Cleanup
 *
 * Block editor asset cleanup implementation.
 */
final class WPSHADOW_Feature_Block_Cleanup extends WPSHADOW_Abstract_Feature {

	/**
	 * Constructor.
	 */
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
					'remove_block_library'   => __( 'Remove block styling code', 'wpshadow' ),
					'remove_global_styles'   => __( 'Remove global theme styles', 'wpshadow' ),
					'remove_classic_styles'  => __( 'Remove classic theme styles', 'wpshadow' ),
					'remove_wc_blocks'       => __( 'Remove WooCommerce block styles', 'wpshadow' ),
					'disable_svg_filters'    => __( 'Remove image filter code', 'wpshadow' ),
					'separate_block_assets'  => __( 'Load all block code together', 'wpshadow' ),
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

	/**
	 * Register hooks when enabled.
	 *
	 * @return void
	 */
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

	/**
	 * Remove block editor assets from frontend.
	 *
	 * @return void
	 */
	public function remove_block_assets(): void {
		if ( is_admin() ) {
			return;
		}

		$removed_handles = array();

		// Remove block library CSS.
		if ( $this->is_sub_feature_enabled( 'remove_block_library', true ) ) {
			wp_dequeue_style( 'wp-block-library' );
			wp_dequeue_style( 'wp-block-library-theme' );
			wp_dequeue_style( 'wc-blocks-style' );
			$removed_handles[] = 'wp-block-library';
			$removed_handles[] = 'wp-block-library-theme';
			$removed_handles[] = 'wc-blocks-style';
		}

		// Remove global styles.
		if ( $this->is_sub_feature_enabled( 'remove_global_styles', true ) ) {
			wp_dequeue_style( 'global-styles' );
			wp_dequeue_style( 'wp-global-styles' );
			$removed_handles[] = 'global-styles';
			$removed_handles[] = 'wp-global-styles';
		}

		// Remove classic theme styles.
		if ( $this->is_sub_feature_enabled( 'remove_classic_styles', true ) ) {
			wp_dequeue_style( 'classic-theme-styles' );
			$removed_handles[] = 'classic-theme-styles';
		}

		// Remove WooCommerce block styles.
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

	/**
	 * Disable block-related theme features.
	 *
	 * @return void
	 */
	public function disable_block_features(): void {
		// Disable SVG filters.
		if ( $this->is_sub_feature_enabled( 'disable_svg_filters', true ) ) {
			remove_action( 'wp_body_open', 'wp_global_styles_render_svg_filters' );
			remove_action( 'in_admin_header', 'wp_global_styles_render_svg_filters' );
			do_action( 'wpshadow_block_cleanup_disabled_svg_filters' );
		}

		// Disable separate block assets loading.
		if ( $this->is_sub_feature_enabled( 'separate_block_assets', true ) ) {
			add_filter( 'should_load_separate_core_block_assets', '__return_false' );
			do_action( 'wpshadow_block_cleanup_combined_assets' );
		}
	}

	/**
	 * Handle WP-CLI command for block cleanup.
	 *
	 * @param array $args       Positional arguments.
	 * @param array $assoc_args Associative arguments.
	 *
	 * @return void
	 */
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

	/**
	 * Register Site Health test.
	 *
	 * @param array $tests Site Health tests.
	 * @return array Modified tests array.
	 */
	public function register_site_health_test( array $tests ): array {
		$tests['direct']['block_cleanup'] = array(
			'label' => __( 'Block Editor Cleanup', 'wpshadow' ),
			'test'  => array( $this, 'test_block_cleanup' ),
		);

		return $tests;
	}

	/**
	 * Site Health test callback.
	 *
	 * @return array Test result.
	 */
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
