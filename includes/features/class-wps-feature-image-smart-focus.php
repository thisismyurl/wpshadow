<?php
/**
 * Smart Focus-Point feature definition.
 *
 * @package wpshadow_SUPPORT
 */

declare(strict_types=1);

namespace WPS\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPSHADOW_Feature_Image_Smart_Focus extends WPSHADOW_Abstract_Feature {
	public function __construct() {
		parent::__construct(
			array(
				'id'                 => 'wpshadow_image_smart_focus',
				'name'               => __( 'Smart Focus-Point', 'plugin-wpshadow' ),
				'description'        => __( 'Enables entropy-aware focus regions for mobile crops across image spokes.', 'plugin-wpshadow' ),
				'scope'              => 'spoke',
				'hub'                => 'media',
				'spoke'              => 'image',
				'version'            => '1.0.0',
				'default_enabled'    => false,
				'widget_group'       => 'advanced',
				'widget_label'       => __( 'Advanced Features', 'plugin-wpshadow' ),
				'widget_description' => __( 'Powerful features for diagnostics and specialized functionality', 'plugin-wpshadow' ),
			)
		);
	}
}
