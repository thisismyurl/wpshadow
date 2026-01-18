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

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WPSHADOW_Feature_Block_Cleanup
 */
final class WPSHADOW_Feature_Block_Cleanup extends WPSHADOW_Abstract_Feature {

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'id'                 => 'block-cleanup',
				'name'               => __( 'Gutenberg/Block Editor Asset Cleanup', 'wpshadow' ),
				'description'        => __( 'Stop loading block editor stuff on pages that don\'t use it - speed up your site.', 'wpshadow' ),
				'scope'              => 'core',
				'default_enabled'    => false,
				'version'            => '1.0.0',
				'widget_group'       => 'performance',
				'sub_features'       => array(
					'remove_block_library'   => __( 'Remove Block Library Assets', 'wpshadow' ),
					'remove_global_styles'   => __( 'Remove Global Styles', 'wpshadow' ),
					'remove_classic_styles'  => __( 'Remove Classic Theme Styles', 'wpshadow' ),
					'remove_wc_blocks'       => __( 'Remove WooCommerce Block Styles', 'wpshadow' ),
					'disable_svg_filters'    => __( 'Disable SVG Filters', 'wpshadow' ),
					'separate_block_assets'  => __( 'Disable Separate Block Assets', 'wpshadow' ),
				),
			)
		);

		$this->seed_default_sub_feature_options();
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
	 * @return array
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
	 * @return array
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
			__( 'Block editor cleanup is optimized', 'wpshadow' ) :
			__( 'Block editor cleanup could be improved', 'wpshadow' );

		return array(
			'label'       => $label,
			'status'      => $status,
			'badge'       => array(
				'label' => __( 'Performance', 'wpshadow' ),
				'color' => 'blue',
			),
			'description' => sprintf(
				'<p>%s</p>',
				sprintf(
					/* translators: %d: number of enabled cleanup features */
					__( '%d block editor cleanup features are enabled, reducing frontend assets.', 'wpshadow' ),
					$enabled_features
				)
			),
			'actions'     => '',
			'test'        => 'block_cleanup',
		);
	}

	/**
	 * Seed default sub-feature options when absent.
	 *
	 * @return void
	 */
	private function seed_default_sub_feature_options(): void {
		$defaults = array(
			'remove_block_library'   => true,
			'remove_global_styles'   => true,
			'remove_classic_styles'  => true,
			'remove_wc_blocks'       => false,
			'disable_svg_filters'    => true,
			'separate_block_assets'  => true,
		);

		foreach ( $defaults as $key => $default_value ) {
			$option_name   = 'wpshadow_block-cleanup_' . $key;
			$current_value = get_option( $option_name, null );

			if ( null === $current_value ) {
				update_option( $option_name, $default_value, false );
			}
		}
	}
}
