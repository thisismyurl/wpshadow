<?php
/**
 * Block Cleanup - Remove Block Library Assets
 *
 * @package WPShadow\CoreSupport
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class WPSHADOW_Feature_Block_Cleanup_Remove_Block_Library extends WPSHADOW_Abstract_Feature {

	public function __construct() {
		parent::__construct(
			array(
				'id'                 => 'block-cleanup-remove-block-library',
				'name'               => __( 'Remove Block Library Assets', 'wpshadow' ),
				'description'        => __( 'Stop loading core block library styles when not needed.', 'wpshadow' ),
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

		$option_name = 'wpshadow_block-cleanup_remove_block_library';
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
