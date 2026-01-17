<?php
/**
 * Block CSS Cleanup - Remove Block Library Styles
 *
 * @package WPShadow\CoreSupport
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class WPSHADOW_Feature_Block_CSS_Cleanup_Remove_Block_Library extends WPSHADOW_Abstract_Feature {

	public function __construct() {
		parent::__construct(
			array(
				'id'                 => 'block-css-cleanup-remove-block-library',
				'name'               => __( 'Remove Block Library Styles', 'plugin-wpshadow' ),
				'description'        => __( 'Dequeue core block library styles to trim CSS payload.', 'plugin-wpshadow' ),
				'scope'              => 'core',
				'default_enabled'    => true,
				'parent'             => 'block-css-cleanup',
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

		if ( ! WPSHADOW_Feature_Registry::is_feature_enabled( 'block-css-cleanup' ) ) {
			return;
		}

		$option_name = 'wpshadow_block-css-cleanup_remove_block_library';
		if ( null === get_option( $option_name, null ) ) {
			update_option( $option_name, true, false );
		}

		if ( ! get_option( $option_name, true ) ) {
			return;
		}

		if ( ! is_admin() && ! wp_doing_ajax() ) {
			add_action( 'wp_enqueue_scripts', array( $this, 'dequeue_block_library' ), 100 );
		}
	}

	public function dequeue_block_library(): void {
		wp_dequeue_style( 'wp-block-library' );
		wp_dequeue_style( 'wp-block-library-theme' );
	}
}
