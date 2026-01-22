<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: WebP Fallback Strategy
 * Philosophy: Inspire confidence (#8) with excellent UX; Show value (#9) by measuring design quality
 * KB Link: https://wpshadow.com/kb/design-webp-fallback-strategy
 * Training: https://wpshadow.com/training/design-webp-fallback-strategy
 */
class Diagnostic_Design_WEBP_FALLBACK_STRATEGY extends Diagnostic_Base {
	public static function check(): ?array {
		return array(
			'id'            => 'design-webp-fallback-strategy',
			'title'         => __( 'WebP Fallback Strategy', 'wpshadow' ),
			'description'   => __( 'Validates WebP includes fallback.', 'wpshadow' ),
			'severity'      => 'medium',
			'category'      => 'design',
			'kb_link'       => 'https://wpshadow.com/kb/design-webp-fallback-strategy',
			'training_link' => 'https://wpshadow.com/training/design-webp-fallback-strategy',
			'auto_fixable'  => false,
			'threat_level'  => 5,
		);
	}
}
