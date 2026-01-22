<?php
declare(strict_types=1);
/**
 * Missing Touch Target Sizes Diagnostic
 *
 * Philosophy: SEO mobile - proper touch targets improve UX
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for adequate touch target sizes.
 */
class Diagnostic_SEO_Missing_Touch_Targets extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		return array(
			'id'          => 'seo-missing-touch-targets',
			'title'       => 'Check Touch Target Sizes',
			'description' => 'Verify touch targets are at least 48x48 pixels in Google Mobile-Friendly Test. Small buttons/links frustrate mobile users. Increase padding and button sizes.',
			'severity'    => 'low',
			'category'    => 'seo',
			'kb_link'     => 'https://wpshadow.com/kb/optimize-touch-targets/',
			'training_link' => 'https://wpshadow.com/training/mobile-ux/',
			'auto_fixable' => false,
			'threat_level' => 50,
		);
	}
}
