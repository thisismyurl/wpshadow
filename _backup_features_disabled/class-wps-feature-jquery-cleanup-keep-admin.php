<?php
/**
 * jQuery Cleanup - Keep jQuery Migrate in Admin
 *
 * @package WPShadow\CoreSupport
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class WPSHADOW_Feature_jQuery_Cleanup_Keep_Admin extends WPSHADOW_Abstract_Feature {

	public function __construct() {
		parent::__construct(
			array(
				'id'                 => 'jquery-cleanup-keep-admin',
				'name'               => __( 'Keep jQuery Migrate in Admin', 'wpshadow' ),
				'description'        => __( 'Retain jQuery Migrate for wp-admin to preserve compatibility.', 'wpshadow' ),
				'scope'              => 'core',
				'default_enabled'    => true,
				'parent'             => 'jquery-cleanup',
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

		if ( ! WPSHADOW_Feature_Registry::is_feature_enabled( 'jquery-cleanup' ) ) {
			return;
		}

		$option_name = 'wpshadow_jquery-cleanup_keep_admin';
		if ( null === get_option( $option_name, null ) ) {
			update_option( $option_name, true, false );
		}
	}
}
