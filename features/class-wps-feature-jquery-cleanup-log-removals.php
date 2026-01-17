<?php
/**
 * jQuery Cleanup - Log Removals
 *
 * @package WPShadow\CoreSupport
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class WPSHADOW_Feature_jQuery_Cleanup_Log_Removals extends WPSHADOW_Abstract_Feature {

	public function __construct() {
		parent::__construct(
			array(
				'id'                 => 'jquery-cleanup-log-removals',
				'name'               => __( 'Log jQuery Migrate Removals', 'plugin-wpshadow' ),
				'description'        => __( 'Record when jQuery Migrate is removed for auditing.', 'plugin-wpshadow' ),
				'scope'              => 'core',
				'default_enabled'    => false,
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

		$option_name = 'wpshadow_jquery-cleanup_log_removals';
		if ( null === get_option( $option_name, null ) ) {
			update_option( $option_name, false, false );
		}
	}
}
