<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Image Size Optimization
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-image-size-optimization
 * Training: https://wpshadow.com/training/design-image-size-optimization
 */
class Diagnostic_Design_IMAGE_SIZE_OPTIMIZATION extends Diagnostic_Base {
	public static function check(): ?array {
		return array(
			'id'            => 'design-image-size-optimization',
			'title'         => __( 'Image Size Optimization', 'wpshadow' ),
			'description'   => __( 'Confirms images responsive, optimized.', 'wpshadow' ),
			'severity'      => 'medium',
			'category'      => 'design',
			'kb_link'       => 'https://wpshadow.com/kb/design-image-size-optimization',
			'training_link' => 'https://wpshadow.com/training/design-image-size-optimization',
			'auto_fixable'  => false,
			'threat_level'  => 5,
		);
	}
}
