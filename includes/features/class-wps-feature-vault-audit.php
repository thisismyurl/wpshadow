<?php
/**
 * Vault Audit Trail feature definition.
 *
 * @package wpshadow_SUPPORT
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPSHADOW_Feature_Vault_Audit extends WPSHADOW_Abstract_Feature {
	public function __construct() {
		parent::__construct(
			array(
				'id'                 => 'wpshadow_vault_audit_trail',
				'name'               => __( 'Vault Audit Trail', 'plugin-wpshadow' ),
				'description'        => __( 'Know exactly what happened with your files - complete logs of who accessed what and when.', 'plugin-wpshadow' ),
				'scope'              => 'hub',
				'hub'                => 'vault',
				'version'            => '1.0.0',
				'default_enabled'    => true,
				'widget_group'       => 'advanced',
				'license_level'      => 5,
				'minimum_capability' => 'manage_options',
				'icon'               => 'dashicons-backup',
				'category'           => 'diagnostics',
				'priority'           => 30,
			)
		);
	}
}
