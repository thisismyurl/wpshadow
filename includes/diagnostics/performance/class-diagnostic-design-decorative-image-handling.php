<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Decorative Image Handling
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-decorative-image-handling
 * Training: https://wpshadow.com/training/design-decorative-image-handling
 */
class Diagnostic_Design_DECORATIVE_IMAGE_HANDLING extends Diagnostic_Base {
	public static function check(): ?array {
		return array(
			'id'            => 'design-decorative-image-handling',
			'title'         => __( 'Decorative Image Handling', 'wpshadow' ),
			'description'   => __( 'Confirms decorative images marked with empty alt.', 'wpshadow' ),
			'severity'      => 'medium',
			'category'      => 'design',
			'kb_link'       => 'https://wpshadow.com/kb/design-decorative-image-handling',
			'training_link' => 'https://wpshadow.com/training/design-decorative-image-handling',
			'auto_fixable'  => false,
			'threat_level'  => 5,
		);
	}
}
