<?php
/**
 * Vault Audit Trail feature definition.
 *
 * @package wp_support_SUPPORT
 */

declare(strict_types=1);

namespace WPS\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPS_Feature_Vault_Audit extends WPS_Abstract_Feature {
	public function __construct() {
		parent::__construct(
			array(
				'id'                 => 'wps_vault_audit_trail',
				'name'               => __( 'Vault Audit Trail', 'plugin-wp-support-thisismyurl' ),
				'description'        => __( 'Tracks vault reads, writes, and background queue results for forensic review.', 'plugin-wp-support-thisismyurl' ),
				'scope'              => 'hub',
				'hub'                => 'vault',
				'version'            => '1.0.0',
				'default_enabled'    => true,
				'widget_group'       => 'advanced',
				'widget_label'       => __( 'Advanced Features', 'plugin-wp-support-thisismyurl' ),
				'widget_description' => __( 'Powerful features for diagnostics and specialized functionality', 'plugin-wp-support-thisismyurl' ),
			)
		);
	}
}
