<?php
/**
 * Feature: Block Library CSS Cleanup
 *
 * Remove unnecessary Gutenberg block library stylesheets for sites that don't use Gutenberg blocks
 * or handle block styling with custom CSS. Block CSS files (style.min.css, theme.min.css) can be
 * 50KB+ of unused styles.
 *
 * @package WPS\CoreSupport
 * @since 1.2601.73001
 */

declare(strict_types=1);

namespace WPS\CoreSupport\Features;

/**
 * WPS_Feature_Block_CSS_Cleanup
 *
 * Remove unnecessary Gutenberg block stylesheets.
 */
final class WPS_Feature_Block_CSS_Cleanup extends WPS_Abstract_Feature {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'id'                  => 'block-css-cleanup',
				'name'                => __( 'Aggressive Block Library CSS Cleanup', 'plugin-wp-support-thisismyurl' ),
				'description'         => __( 'Dequeue unnecessary Gutenberg block CSS from front-end', 'plugin-wp-support-thisismyurl' ),
				'scope'               => 'core',
				'default_enabled'     => false,
				'version'             => '1.0.0',
				'widget_group'        => 'performance-security',
				'widget_label'        => __( 'Performance & Security', 'plugin-wp-support-thisismyurl' ),
				'widget_description'  => __( 'Remove bloat and unnecessary scripts that impact security and page speed', 'plugin-wp-support-thisismyurl' ),
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

		add_action( 'wp_enqueue_scripts', array( $this, 'remove_block_library_css' ), 100 );
		add_action( 'wp_enqueue_scripts', array( $this, 'remove_block_theme_css' ), 100 );
	}

	/**
	 * Remove core block library styles.
	 *
	 * @return void
	 */
	public function remove_block_library_css(): void {
		wp_dequeue_style( 'wp-block-library' );
		wp_dequeue_style( 'wp-block-library-theme' );
		wp_dequeue_style( 'wc-block-style' ); // WooCommerce blocks.
	}

	/**
	 * Remove block editor theme styles.
	 *
	 * @return void
	 */
	public function remove_block_theme_css(): void {
		wp_dequeue_style( 'global-styles' );
	}
}

