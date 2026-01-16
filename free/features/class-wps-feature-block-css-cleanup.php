<?php
/**
 * Feature: Block Library CSS Cleanup
 *
 * Remove unnecessary Gutenberg block library stylesheets for sites that don't use Gutenberg blocks
 * or handle block styling with custom CSS. Block CSS files (style.min.css, theme.min.css) can be
 * 50KB+ of unused styles.
 *
 * @package WPShadow\CoreSupport
 * @since 1.2601.73001
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport;

/**
 * WPSHADOW_Feature_Block_CSS_Cleanup
 *
 * Remove unnecessary Gutenberg block stylesheets.
 */
final class WPSHADOW_Feature_Block_CSS_Cleanup extends WPSHADOW_Abstract_Feature {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'id'                 => 'block-css-cleanup',
				'name'               => __( 'Aggressive Block Library CSS Cleanup', 'plugin-wpshadow' ),
				'description'        => __( 'Stop loading unused block styles - make pages faster by removing CSS you don\'t need.', 'plugin-wpshadow' ),
				'scope'              => 'core',
				'default_enabled'    => false,
				'version'            => '1.0.0',
				'widget_group'       => 'performance',
				'license_level'      => 1,
				'minimum_capability' => 'manage_options',
				'icon'               => 'dashicons-editor-table',
				'category'           => 'performance',
				'priority'           => 20,
			)
		);
		
		if ( method_exists( $this, 'register_sub_features' ) ) {
			$this->register_sub_features(
				array(
					'remove_block_library'       => __( 'Remove Block Library Styles', 'plugin-wpshadow' ),
					'remove_block_theme'         => __( 'Remove Block Theme Styles', 'plugin-wpshadow' ),
					'remove_global_styles'       => __( 'Remove Global Styles', 'plugin-wpshadow' ),
					'remove_woocommerce_blocks'  => __( 'Remove WooCommerce Block Styles', 'plugin-wpshadow' ),
				)
			);
			if ( method_exists( $this, 'set_default_sub_features' ) ) {
				$this->set_default_sub_features(
					array(
						'remove_block_library'       => true,
						'remove_block_theme'         => true,
						'remove_global_styles'       => true,
						'remove_woocommerce_blocks'  => true,
					)
				);
			}
		}
		
		$this->log_activity( 'feature_initialized', 'Block CSS Cleanup feature initialized', 'info' );
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

		add_action( 'wp_enqueue_scripts', array( $this, 'remove_block_library_css' ), 100 );
		
		if ( get_option( 'wpshadow_block-css-cleanup_remove_block_theme', true ) || get_option( 'wpshadow_block-css-cleanup_remove_global_styles', true ) ) {
			add_action( 'wp_enqueue_scripts', array( $this, 'remove_block_theme_css' ), 100 );
		}
		
		// Add Site Health tests.
		add_filter( 'site_status_tests', array( $this, 'register_site_health_test' ) );
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

	/**
	 * Register Site Health test.
	 *
	 * @param array $tests Array of Site Health tests.
	 * @return array Modified tests array.
	 */
	public function register_site_health_test( array $tests ): array {
		$tests['direct']['block_css_cleanup'] = array(
			'label' => __( 'Block CSS Cleanup', 'plugin-wpshadow' ),
			'test'  => array( $this, 'test_block_css_cleanup' ),
		);

		return $tests;
	}

	/**
	 * Site Health test for block CSS cleanup.
	 *
	 * @return array Test result.
	 */
	public function test_block_css_cleanup(): array {
		$enabled_features = 0;
		
		if ( get_option( 'wpshadow_block-css-cleanup_remove_block_library', true ) ) {
			$enabled_features++;
		}
		if ( get_option( 'wpshadow_block-css-cleanup_remove_block_theme', true ) ) {
			$enabled_features++;
		}
		if ( get_option( 'wpshadow_block-css-cleanup_remove_global_styles', true ) ) {
			$enabled_features++;
		}
		if ( get_option( 'wpshadow_block-css-cleanup_remove_woocommerce_blocks', true ) ) {
			$enabled_features++;
		}

		$status = $enabled_features > 0 ? 'good' : 'recommended';
		$label  = $enabled_features > 0 ?
			__( 'Block CSS cleanup is enabled', 'plugin-wpshadow' ) :
			__( 'Block CSS cleanup is not configured', 'plugin-wpshadow' );

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
					__( '%d block CSS cleanup features are enabled, reducing frontend CSS bloat.', 'plugin-wpshadow' ),
					$enabled_features
				)
			),
			'actions'     => '',
			'test'        => 'block_css_cleanup',
		);
	}
}
