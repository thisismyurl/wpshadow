<?php
/**
 * Core Diagnostics feature definition.
 *
 * @package wp_support_SUPPORT
 */

declare(strict_types=1);

namespace WPS\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPS_Feature_Core_Diagnostics extends WPS_Abstract_Feature {
	public function __construct() {
		parent::__construct(
			array(
				'id'                 => 'wps_core_diagnostics',
				'name'               => __( 'Core Diagnostics', 'plugin-wp-support-thisismyurl' ),
				'description'        => __( 'Keep your site healthy with automatic check-ups and recovery tools', 'plugin-wp-support-thisismyurl' ),
				'scope'              => 'core',
				'version'            => '1.0.0',
				'default_enabled'    => true,
				'widget_group'       => 'diagnostics',
				'widget_label'       => __( 'Diagnostics & Monitoring', 'plugin-wp-support-thisismyurl' ),
				'widget_description' => __( 'Health checks and monitoring features', 'plugin-wp-support-thisismyurl' ),
			)
		);
	}
}
