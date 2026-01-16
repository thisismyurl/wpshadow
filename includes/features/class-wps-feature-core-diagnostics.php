<?php
/**
 * Core Diagnostics feature definition.
 *
 * @package wpshadow_SUPPORT
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPSHADOW_Feature_Core_Diagnostics extends WPSHADOW_Abstract_Feature {
	public function __construct() {
		parent::__construct(
			array(
				'id'                 => 'wpshadow_core_diagnostics',
				'name'               => __( 'Core Diagnostics', 'plugin-wpshadow' ),
				'description'        => __( 'Catch problems early - we monitor WordPress health and alert you when something goes wrong.', 'plugin-wpshadow' ),
				'scope'              => 'core',
				'version'            => '1.0.0',
				'default_enabled'    => true,
				'widget_group'       => 'diagnostics',
				'license_level'      => 1,
				'minimum_capability' => 'manage_options',
				'icon'               => 'dashicons-admin-tools',
				'category'           => 'diagnostics',
				'priority'           => 30,
			)
		);
	}
}
