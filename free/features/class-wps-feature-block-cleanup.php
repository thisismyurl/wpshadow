<?php
/**
 * Feature: Gutenberg/Block Editor Asset Cleanup
 *
 * Remove unused Gutenberg and Block Editor CSS/JS for sites not using
 * the block editor or using page builders.
 *
 * @package WPShadow\Features
 * @since 1.2601.73001
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport;

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
				'id'                 => 'block-cleanup',
				'name'               => __( 'Gutenberg/Block Editor Asset Cleanup', 'plugin-wpshadow' ),
				'description'        => __( 'Stop loading block editor stuff on pages that don\'t use it - speed up your site.', 'plugin-wpshadow' ),
				'scope'              => 'core',
				'default_enabled'    => false,
				'version'            => '1.0.0',
				'widget_group'       => 'performance',
			)
		);
		
		if ( method_exists( $this, 'register_sub_features' ) ) {
			$this->register_sub_features(
				array(
					'remove_block_library'   => __( 'Remove Block Library Assets', 'plugin-wpshadow' ),
					'remove_global_styles'   => __( 'Remove Global Styles', 'plugin-wpshadow' ),
					'remove_classic_styles'  => __( 'Remove Classic Theme Styles', 'plugin-wpshadow' ),
					'remove_wc_blocks'       => __( 'Remove WooCommerce Block Styles', 'plugin-wpshadow' ),
					'disable_svg_filters'    => __( 'Disable SVG Filters', 'plugin-wpshadow' ),
					'separate_block_assets'  => __( 'Disable Separate Block Assets', 'plugin-wpshadow' ),
				)
			);
			if ( method_exists( $this, 'set_default_sub_features' ) ) {
				$this->set_default_sub_features(
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
		}
		
		$this->log_activity( 'feature_initialized', 'Block Cleanup feature initialized', 'info' );
	}

	/**
	 * Indicate this feature has a details page.
	 *
	 * @return bool
	 */
	public function has_details_page(): bool {
		return true;
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

		// Disable separate core block assets if enabled.
		if ( get_option( 'wpshadow_block-cleanup_separate_block_assets', true ) ) {
			add_filter( 'should_load_separate_core_block_assets', '__return_false' );
		}

		// Remove SVG filters if enabled.
		if ( get_option( 'wpshadow_block-cleanup_disable_svg_filters', true ) ) {
			remove_action( 'wp_body_open', 'wp_global_styles_render_svg_filters' );
			remove_action( 'in_admin_header', 'wp_global_styles_render_svg_filters' );
		}
		
		// Add Site Health tests.
		add_filter( 'site_status_tests', array( $this, 'register_site_health_test' ) );
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

	/**
	 * Register Site Health test.
	 *
	 * @param array $tests Array of Site Health tests.
	 * @return array Modified tests array.
	 */
	public function register_site_health_test( array $tests ): array {
		$tests['direct']['block_cleanup'] = array(
			'label' => __( 'Block Editor Cleanup', 'plugin-wpshadow' ),
			'test'  => array( $this, 'test_block_cleanup' ),
		);

		return $tests;
	}

	/**
	 * Site Health test for block cleanup.
	 *
	 * @return array Test result.
	 */
	public function test_block_cleanup(): array {
		$enabled_features = 0;
		
		if ( get_option( 'wpshadow_block-cleanup_remove_block_library', true ) ) {
			$enabled_features++;
		}
		if ( get_option( 'wpshadow_block-cleanup_remove_global_styles', true ) ) {
			$enabled_features++;
		}
		if ( get_option( 'wpshadow_block-cleanup_remove_classic_styles', true ) ) {
			$enabled_features++;
		}
		if ( get_option( 'wpshadow_block-cleanup_remove_wc_blocks', false ) ) {
			$enabled_features++;
		}
		if ( get_option( 'wpshadow_block-cleanup_disable_svg_filters', true ) ) {
			$enabled_features++;
		}
		if ( get_option( 'wpshadow_block-cleanup_separate_block_assets', true ) ) {
			$enabled_features++;
		}

		$status = $enabled_features >= 4 ? 'good' : 'recommended';
		$label  = $enabled_features >= 4 ?
			__( 'Block editor cleanup is optimized', 'plugin-wpshadow' ) :
			__( 'Block editor cleanup could be improved', 'plugin-wpshadow' );

		return array(
			'label'       => $label,
			'status'      => $status,
			'badge'       => array(
				'label' => __( 'Performance', 'plugin-wpshadow' ),
				'color' => 'blue',
			),
			'description' => sprintf(
				'<p>%s</p>',
				sprintf(
					/* translators: %d: number of enabled cleanup features */
					__( '%d block editor cleanup features are enabled, reducing frontend assets.', 'plugin-wpshadow' ),
					$enabled_features
				)
			),
			'actions'     => '',
			'test'        => 'block_cleanup',
		);
	}
}
