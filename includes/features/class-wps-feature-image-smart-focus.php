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
				'description'        => __( 'Analyzes uploaded images to identify the most interesting areas using intelligent content detection, then automatically crops thumbnails and mobile versions to keep important subjects centered. Prevents awkward crops that cut off faces or key details, works with existing WordPress image sizes, and adapts to portrait or landscape orientations for better visual results across all screen sizes.', 'plugin-wpshadow' ),
				'scope'              => 'spoke',
				'hub'                => 'media',
				'spoke'              => 'image',
				'version'            => '1.0.0',
				'default_enabled'    => false,
				'widget_group'       => 'advanced',
				'license_level'      => 3,
				'minimum_capability' => 'upload_files',
				'icon'               => 'dashicons-format-image',
				'category'           => 'media',
				'priority'           => 30,
			)
		);
	}
}
