<?php
/**
 * Block Cleanup - Disable SVG Filters
 *
 * @package WPShadow\CoreSupport
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class WPSHADOW_Feature_Block_Cleanup_Disable_SVG_Filters extends WPSHADOW_Abstract_Feature {

	public function __construct() {
		parent::__construct(
			array(
				'id'                 => 'block-cleanup-disable-svg-filters',
				'name'               => __( 'Disable SVG Filters', 'wpshadow' ),
				'description'        => __( 'Stop output of block editor SVG filters.', 'wpshadow' ),
				'scope'              => 'core',
				'default_enabled'    => true,
				'parent'             => 'block-cleanup',
				'category'           => 'performance',
				'license_level'      => 1,
				'minimum_capability' => 'manage_options',
			)
		);
	}

	public function register(): void {
		if ( ! $this->is_enabled() ) {
			return;
		}

		if ( ! WPSHADOW_Feature_Registry::is_feature_enabled( 'block-cleanup' ) ) {
			return;
		}

		$option_name = 'wpshadow_block-cleanup_disable_svg_filters';
		if ( null === get_option( $option_name, null ) ) {
			update_option( $option_name, true, false );
		}

		if ( ! get_option( $option_name, true ) ) {
			return;
		}

		add_action( 'init', array( $this, 'remove_svg_filters' ), 1 );
	}

	public function remove_svg_filters(): void {
		remove_action( 'wp_body_open', 'wp_global_styles_render_svg_filters' );
		remove_action( 'in_admin_header', 'wp_global_styles_render_svg_filters' );
	}
}
