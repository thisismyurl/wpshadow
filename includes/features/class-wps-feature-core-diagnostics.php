<?php
/**
 * Core Diagnostics feature definition.
 *
 * @package wp_support_SUPPORT
 */

declare(strict_types=1);

namespace WPS\CoreSupport\Features;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPS_Feature_Core_Diagnostics extends WPS_Abstract_Feature {
	public function __construct() {
		parent::__construct(
			array(
				'id'                  => 'wps_core_diagnostics',
				'name'                => __( 'Core Diagnostics', 'plugin-wp-support-thisismyurl' ),
				'description'         => __( 'Keeps the suite health checks, recovery helpers, and layout cache safeguards active.', 'plugin-wp-support-thisismyurl' ),
				'scope'               => 'core',
				'version'             => '1.0.0',
				'default_enabled'     => true,
				'widget_group'        => 'advanced-features',
				'widget_label'        => __( 'Advanced Features', 'plugin-wp-support-thisismyurl' ),
				'widget_description'  => __( 'Powerful features for diagnostics and specialized functionality', 'plugin-wp-support-thisismyurl' ),
			)
		);
	}
}
