<?php
/**
 * Head Cleanup - Remove Windows Live Writer Manifest
 *
 * @package WPShadow\CoreSupport
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class WPSHADOW_Feature_Head_Cleanup_Remove_WLW extends WPSHADOW_Abstract_Feature {

	public function __construct() {
		parent::__construct(
			array(
				'id'                 => 'head-cleanup-remove-wlw',
				'name'               => __( 'Remove WLW Manifest', 'wpshadow' ),
				'description'        => __( 'Remove the Windows Live Writer manifest link from page headers.', 'wpshadow' ),
				'scope'              => 'core',
				'default_enabled'    => true,
				'parent'             => 'head-cleanup',
				'icon'               => 'dashicons-media-default',
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

		if ( ! WPSHADOW_Feature_Registry::is_feature_enabled( 'head-cleanup' ) ) {
			return;
		}

		$option_name = 'wpshadow_head-cleanup_remove_wlw';
		if ( false === get_option( $option_name ) ) {
			update_option( $option_name, true, false );
		}
		if ( ! get_option( $option_name, true ) ) {
			return;
		}

		add_action( 'init', array( $this, 'remove_wlw' ) );
	}

	public function remove_wlw(): void {
		remove_action( 'wp_head', 'wlwmanifest_link' );
	}
}
