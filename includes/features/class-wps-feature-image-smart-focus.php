<?php
/**
 * Smart Focus-Point feature definition.
 *
 * @package wp_support_SUPPORT
 */

declare(strict_types=1);

namespace WPS\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPS_Feature_Image_Smart_Focus extends WPS_Abstract_Feature {
	public function __construct() {
		parent::__construct(
			array(
				'id'                 => 'wps_image_smart_focus',
				'name'               => __( 'Smart Focus-Point', 'plugin-wp-support-thisismyurl' ),
				'description'        => __( 'Enables entropy-aware focus regions for mobile crops across image spokes.', 'plugin-wp-support-thisismyurl' ),
				'scope'              => 'spoke',
				'hub'                => 'media',
				'spoke'              => 'image',
				'version'            => '1.0.0',
				'default_enabled'    => false,
				'widget_group'       => 'advanced',
				'widget_label'       => __( 'Advanced Features', 'plugin-wp-support-thisismyurl' ),
				'widget_description' => __( 'Powerful features for diagnostics and specialized functionality', 'plugin-wp-support-thisismyurl' ),
			)
		);
	}
}
