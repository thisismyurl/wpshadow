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

		add_filter( 'site_status_tests', array( $this, 'register_site_health_test' ) );
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
