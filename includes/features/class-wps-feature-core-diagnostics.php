<?php
/**
 * Core Diagnostics feature definition.
 *
 * @package wpshadow_SUPPORT
 */

declare(strict_types=1);

namespace WPS\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPSHADOW_Feature_Core_Diagnostics extends WPSHADOW_Abstract_Feature {
	public function __construct() {
		parent::__construct(
			array(
				'id'                 => 'wpshadow_core_diagnostics',
				'name'               => __( 'Core Diagnostics', 'plugin-wpshadow' ),
				'description'        => __( 'Keep your site healthy with automatic check-ups and recovery tools', 'plugin-wpshadow' ),
				'scope'              => 'core',
				'version'            => '1.0.0',
				'default_enabled'    => true,
				'widget_group'       => 'diagnostics',
				'widget_label'       => __( 'Diagnostics & Monitoring', 'plugin-wpshadow' ),
				'widget_description' => __( 'Health checks and monitoring features', 'plugin-wpshadow' ),
			)
		);
	}
}
