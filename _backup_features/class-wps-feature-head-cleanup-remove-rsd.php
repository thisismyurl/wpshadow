<?php
/**
 * Head Cleanup - Remove RSD Link
 *
 * @package WPShadow\CoreSupport
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class WPSHADOW_Feature_Head_Cleanup_Remove_RSD extends WPSHADOW_Abstract_Feature {

	public function __construct() {
		parent::__construct(
			array(
				'id'                 => 'head-cleanup-remove-rsd',
				'name'               => __( 'Remove RSD Link', 'wpshadow' ),
				'description'        => __( 'Remove the Really Simple Discovery (RSD) link from page headers.', 'wpshadow' ),
				'scope'              => 'core',
				'default_enabled'    => true,
				'parent'             => 'head-cleanup',
				'icon'               => 'dashicons-media-code',
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

		$option_name = 'wpshadow_head-cleanup_remove_rsd';
		if ( false === get_option( $option_name ) ) {
			update_option( $option_name, true, false );
		}
		if ( ! get_option( $option_name, true ) ) {
			return;
		}

		add_action( 'init', array( $this, 'remove_rsd' ) );
	}

	public function remove_rsd(): void {
		remove_action( 'wp_head', 'rsd_link' );
	}
}
