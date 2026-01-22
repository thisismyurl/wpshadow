<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Image Aspect Ratio Preservation
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-image-aspect-ratio-preservation
 * Training: https://wpshadow.com/training/design-image-aspect-ratio-preservation
 */
class Diagnostic_Design_IMAGE_ASPECT_RATIO_PRESERVATION extends Diagnostic_Base {
	public static function check(): ?array {
		return array(
			'id'            => 'design-image-aspect-ratio-preservation',
			'title'         => __( 'Image Aspect Ratio Preservation', 'wpshadow' ),
			'description'   => __( 'Checks images maintain aspect ratio across breakpoints.', 'wpshadow' ),
			'severity'      => 'medium',
			'category'      => 'design',
			'kb_link'       => 'https://wpshadow.com/kb/design-image-aspect-ratio-preservation',
			'training_link' => 'https://wpshadow.com/training/design-image-aspect-ratio-preservation',
			'auto_fixable'  => false,
			'threat_level'  => 5,
		);
	}
}
