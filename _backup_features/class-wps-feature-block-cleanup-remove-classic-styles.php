<?php
/**
 * Block Cleanup - Remove Classic Theme Styles
 *
 * @package WPShadow\CoreSupport
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class WPSHADOW_Feature_Block_Cleanup_Remove_Classic_Styles extends WPSHADOW_Abstract_Feature {

	public function __construct() {
		parent::__construct(
			array(
				'id'                 => 'block-cleanup-remove-classic-styles',
				'name'               => __( 'Remove Classic Theme Styles', 'wpshadow' ),
				'description'        => __( 'Remove legacy classic theme block styles on the frontend.', 'wpshadow' ),
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

		$option_name = 'wpshadow_block-cleanup_remove_classic_styles';
		if ( null === get_option( $option_name, null ) ) {
			update_option( $option_name, true, false );
		}

		if ( ! get_option( $option_name, true ) ) {
			return;
		}

		if ( ! is_admin() && ! wp_doing_ajax() ) {
			add_action( 'wp_enqueue_scripts', array( $this, 'dequeue_classic_styles' ), 100 );
		}
	}

	public function dequeue_classic_styles(): void {
		wp_dequeue_style( 'classic-theme-styles' );
	}
}
