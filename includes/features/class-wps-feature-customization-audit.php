<?php
/**
 * Customization Audit feature definition.
 *
 * @package wp_support_SUPPORT
 */

declare(strict_types=1);

namespace WPS\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPS_Feature_Customization_Audit extends WPS_Abstract_Feature {
	public function __construct() {
		parent::__construct(
			array(
				'id'                  => 'wps_customization_audit',
				'name'                => __( 'Customization Audit & Risk Assessment', 'plugin-wp-support-thisismyurl' ),
				'description'         => __( 'Identifies all non-standard customizations and unique configurations, helping site owners understand what makes their site unique vs. what\'s built-in WordPress.', 'plugin-wp-support-thisismyurl' ),
				'scope'               => 'core',
				'version'             => '1.0.0',
				'default_enabled'     => false,
				'widget_group'        => 'advanced-features',
				'widget_label'        => __( 'Advanced Features', 'plugin-wp-support-thisismyurl' ),
				'widget_description'  => __( 'Powerful features for diagnostics and specialized functionality', 'plugin-wp-support-thisismyurl' ),
			)
		);
	}
}
