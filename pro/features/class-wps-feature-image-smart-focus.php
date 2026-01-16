<?php
/**
 * Smart Focus-Point feature definition.
 *
 * @package wpshadow_SUPPORT
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPSHADOW_Feature_Image_Smart_Focus extends WPSHADOW_Abstract_Feature {
	public function __construct() {
		parent::__construct(
			array(
				'id'                 => 'wpshadow_image_smart_focus',
				'name'               => __( 'Smart Focus-Point', 'plugin-wpshadow' ),
				'description'        => __( 'Smart image cropping - keep faces and details centered in thumbnails automatically.', 'plugin-wpshadow' ),
				'scope'              => 'spoke',
				'hub'                => 'media',
				'spoke'              => 'image',
				'version'            => '1.0.0',
				'default_enabled'    => false,
				'widget_group'       => 'advanced',
				'license_level'      => 5,
				'minimum_capability' => 'upload_files',
				'icon'               => 'dashicons-format-image',
				'category'           => 'media',
				'priority'           => 30,
			)
		);
	}
}
