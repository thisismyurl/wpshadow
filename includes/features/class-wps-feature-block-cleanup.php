<?php
/**
 * Feature: Gutenberg/Block Editor Asset Cleanup
 *
 * Remove unused Gutenberg and Block Editor CSS/JS for sites not using
 * the block editor or using page builders.
 *
 * @package WPS\CoreSupport\Features
 * @since 1.2601.73001
 */

declare(strict_types=1);

namespace WPS\CoreSupport;

/**
 * WPSHADOW_Feature_Block_Cleanup
 *
 * Removes unused block editor assets.
 */
final class WPSHADOW_Feature_Block_Cleanup extends WPSHADOW_Abstract_Feature {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'id'              => 'block-cleanup',
				'name'            => __( 'Gutenberg/Block Editor Asset Cleanup', 'plugin-wpshadow' ),
				'description'     => __( 'Remove editor code your visitors don\'t need and lighten your pages', 'plugin-wpshadow' ),
				'scope'           => 'core',
				'default_enabled' => false,
				'version'         => '1.0.0',
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

		// Only apply on frontend.
		if ( ! is_admin() && ! wp_doing_ajax() ) {
			add_action( 'wp_enqueue_scripts', array( $this, 'cleanup_block_assets' ), 100 );
		}

		// Disable separate core block assets.
		add_filter( 'should_load_separate_core_block_assets', '__return_false' );

		// Remove SVG filters.
		remove_action( 'wp_body_open', 'wp_global_styles_render_svg_filters' );
		remove_action( 'in_admin_header', 'wp_global_styles_render_svg_filters' );
	}

	/**
	 * Cleanup block editor styles.
	 *
	 * @return void
	 */
	public function cleanup_block_assets(): void {
		// Get options for granular control.
		$cleanup_options = (array) $this->get_setting( 'wpshadow_block_cleanup_options', $this->get_default_options( ) );

		// Block library styles.
		if ( $cleanup_options['remove_block_library'] ?? false ) {
			wp_dequeue_style( 'wp-block-library' );
			wp_dequeue_style( 'wp-block-library-theme' );
		}

		// Global styles.
		if ( $cleanup_options['remove_global_styles'] ?? false ) {
			wp_dequeue_style( 'global-styles' );
		}

		// Classic theme styles.
		if ( $cleanup_options['remove_classic_styles'] ?? false ) {
			wp_dequeue_style( 'classic-theme-styles' );
		}

		// WooCommerce block styles.
		if ( $cleanup_options['remove_wc_block_styles'] ?? false ) {
			wp_dequeue_style( 'wc-block-style' );
		}
	}

	/**
	 * Get default cleanup options.
	 *
	 * @return array Default options.
	 */
	protected function get_default_options(): array {
		return array(
			'remove_block_library'   => true,
			'remove_global_styles'   => true,
			'remove_classic_styles'  => true,
			'remove_wc_block_styles' => true,
		);
	}
}
