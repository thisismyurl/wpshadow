<?php
/**
 * Customization Audit feature definition.
 *
 * @package wpshadow_SUPPORT
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPSHADOW_Feature_Customization_Audit extends WPSHADOW_Abstract_Feature {
	public function __construct() {
		parent::__construct(
			array(
				'id'                 => 'wpshadow_customization_audit',
				'name'               => __( 'Customization Audit & Risk Assessment', 'plugin-wpshadow' ),
				'description'        => __( 'Know what makes your site unique - we list all your customizations so you understand dependencies.', 'plugin-wpshadow' ),
				'scope'              => 'core',
				'version'            => '1.0.0',
				'default_enabled'    => false,
				'widget_group'       => 'advanced-features',
				'license_level'      => 5,
				'minimum_capability' => 'manage_options',
				'icon'               => 'dashicons-admin-tools',
				'category'           => 'diagnostics',
				'priority'           => 40,
			)
		);
	}
}
