<?php
/**
 * Block CSS Cleanup - Remove Global Styles
 *
 * @package WPShadow\CoreSupport
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class WPSHADOW_Feature_Block_CSS_Cleanup_Remove_Global_Styles extends WPSHADOW_Abstract_Feature {

	public function __construct() {
		parent::__construct(
			array(
				'id'                 => 'block-css-cleanup-remove-global-styles',
				'name'               => __( 'Remove Global Styles', 'plugin-wpshadow' ),
				'description'        => __( 'Stop the global styles stylesheet from loading.', 'plugin-wpshadow' ),
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

		$option_name = 'wpshadow_block-css-cleanup_remove_global_styles';
		if ( null === get_option( $option_name, null ) ) {
			update_option( $option_name, true, false );
		}

		if ( ! get_option( $option_name, true ) ) {
			return;
		}

		if ( ! is_admin() && ! wp_doing_ajax() ) {
			add_action( 'wp_enqueue_scripts', array( $this, 'dequeue_global_styles' ), 100 );
		}
	}

	public function dequeue_global_styles(): void {
		wp_dequeue_style( 'global-styles' );
	}
}
