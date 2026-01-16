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
				'description'        => __( 'Tracks vault reads, writes, and background queue results for forensic review, creating a permanent log of every file operation performed by the vault system. Records who accessed which files, when changes happened, and what background tasks ran, helping you audit storage activity, debug sync issues, verify backup completion, and trace the source of unexpected file changes or storage consumption.', 'plugin-wpshadow' ),
				'scope'              => 'hub',
				'hub'                => 'vault',
				'version'            => '1.0.0',
				'default_enabled'    => true,
				'widget_group'       => 'advanced',
				'license_level'      => 2,
				'minimum_capability' => 'manage_options',
				'icon'               => 'dashicons-backup',
				'category'           => 'diagnostics',
				'priority'           => 30,
			)
		);
	}
}
