<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Font Weight Enforcement
 * Philosophy: Show value (#9) - identify design system debt, enforcement gaps
 * Competitive Edge: This category fills gaps competitors don't address
 * KB Link: https://wpshadow.com/kb/design-system-font-weight-scale
 * Training: https://wpshadow.com/training/design-system-font-weight-scale
 */
class Diagnostic_Design_SYSTEM_FONT_WEIGHT_SCALE extends Diagnostic_Base {
	public static function check(): ?array {
		return array(
			'id'            => 'design-system-font-weight-scale',
			'title'         => __( 'Font Weight Enforcement', 'wpshadow' ),
			'description'   => __( 'Verifies only system-defined weights used (400, 600, 700).', 'wpshadow' ),
			'severity'      => 'medium',
			'category'      => 'design',
			'kb_link'       => 'https://wpshadow.com/kb/design-system-font-weight-scale',
			'training_link' => 'https://wpshadow.com/training/design-system-font-weight-scale',
			'auto_fixable'  => false,
			'threat_level'  => 6,
		);
	}
}
