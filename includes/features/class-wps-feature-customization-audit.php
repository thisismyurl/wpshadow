<?php
/**
 * Customization Audit feature definition.
 *
 * @package wpshadow_SUPPORT
 */

declare(strict_types=1);

namespace WPS\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPSHADOW_Feature_Customization_Audit extends WPSHADOW_Abstract_Feature {
	public function __construct() {
		parent::__construct(
			array(
				'id'                 => 'wpshadow_customization_audit',
				'name'               => __( 'Customization Audit & Risk Assessment', 'plugin-wpshadow' ),
				'description'        => __( 'Identifies all non-standard customizations and unique configurations, helping site owners understand what makes their site unique vs. what\'s built-in WordPress.', 'plugin-wpshadow' ),
				'scope'              => 'core',
				'version'            => '1.0.0',
				'default_enabled'    => false,
				'widget_group'       => 'advanced-features',
				'widget_label'       => __( 'Advanced Features', 'plugin-wpshadow' ),
				'widget_description' => __( 'Powerful features for diagnostics and specialized functionality', 'plugin-wpshadow' ),
			)
		);
	}
}
