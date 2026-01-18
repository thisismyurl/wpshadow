<?php
/**
 * Head Cleanup - Remove REST API Link
 *
 * @package WPShadow\CoreSupport
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class WPSHADOW_Feature_Head_Cleanup_Remove_Rest_Link extends WPSHADOW_Abstract_Feature {

	public function __construct() {
		parent::__construct(
			array(
				'id'                 => 'head-cleanup-remove-rest-link',
				'name'               => __( 'Remove REST API Link', 'wpshadow' ),
				'description'        => __( 'Remove the REST API link from page headers (may affect REST clients).', 'wpshadow' ),
				'scope'              => 'core',
				'default_enabled'    => false,
				'parent'             => 'head-cleanup',
				'icon'               => 'dashicons-rest-api',
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

		$option_name = 'wpshadow_head-cleanup_remove_rest_link';
		if ( false === get_option( $option_name ) ) {
			update_option( $option_name, false, false );
		}
		if ( ! get_option( $option_name, false ) ) {
			return;
		}

		add_action( 'init', array( $this, 'remove_rest_link' ) );
	}

	public function remove_rest_link(): void {
		remove_action( 'wp_head', 'rest_output_link_wp_head', 10 );
	}
}
