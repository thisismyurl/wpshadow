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
				'id'              => 'block-css-cleanup',
				'name'            => __( 'Aggressive Block Library CSS Cleanup', 'wpshadow' ),
				'description'     => __( 'Stop loading unused block styles - make pages faster by removing CSS you don\'t need.', 'wpshadow' ),
				'scope'           => 'core',
				'default_enabled' => false,
				'version'         => '1.0.0',
				'widget_group'    => 'performance',
				'sub_features'    => array(
					'remove_block_library'      => __( 'Remove Block Library Styles', 'wpshadow' ),
					'remove_block_theme'        => __( 'Remove Block Theme Styles', 'wpshadow' ),
					'remove_global_styles'      => __( 'Remove Global Styles', 'wpshadow' ),
					'remove_woocommerce_blocks' => __( 'Remove WooCommerce Block Styles', 'wpshadow' ),
				),
			)
		);

		$this->register_default_settings(
			array(
				'remove_block_library'      => true,
				'remove_block_theme'        => true,
				'remove_global_styles'      => true,
				'remove_woocommerce_blocks' => true,
			)
		);

		$this->log_activity( 'feature_initialized', 'Block CSS Cleanup feature initialized', 'info' );
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

		add_filter( 'site_status_tests', array( $this, 'register_site_health_test' ) );
	}

	/**
	 * Register Site Health test.
	 *
	 * @param array $tests Array of Site Health tests.
	 * @return array Modified tests array.
	 */
	public function register_site_health_test( array $tests ): array {
		$tests['direct']['block_css_cleanup'] = array(
			'label' => __( 'Block CSS Cleanup', 'wpshadow' ),
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
		if ( ! $this->is_enabled() ) {
			return array(
				'label'       => __( 'Block CSS Cleanup', 'wpshadow' ),
				'status'      => 'recommended',
				'badge'       => array(
					'label' => __( 'Performance', 'wpshadow' ),
					'color' => 'blue',
				),
				'description' => __( 'Block CSS cleanup is disabled.', 'wpshadow' ),
				'test'        => 'block_css_cleanup',
			);
		}

		$enabled_features = 0;

		if ( $this->is_sub_feature_enabled( 'remove_block_library', true ) ) {
			$enabled_features++;
		}
		if ( $this->is_sub_feature_enabled( 'remove_block_theme', true ) ) {
			$enabled_features++;
		}
		if ( $this->is_sub_feature_enabled( 'remove_global_styles', true ) ) {
			$enabled_features++;
		}
		if ( $this->is_sub_feature_enabled( 'remove_woocommerce_blocks', true ) ) {
			$enabled_features++;
		}

		$status = $enabled_features > 0 ? 'good' : 'recommended';

		return array(
			'label'       => __( 'Block CSS cleanup is enabled', 'wpshadow' ),
			'status'      => $status,
			'badge'       => array(
				'label' => __( 'Performance', 'wpshadow' ),
				'color' => 'blue',
			),
			'description' => sprintf(
				__( '%d block CSS cleanup features are enabled, reducing frontend CSS bloat.', 'wpshadow' ),
				(int) $enabled_features
			),
			'test'        => 'block_css_cleanup',
		);
	}
}
