<?php declare(strict_types=1);
/**
 * Small Font Sizes Mobile Diagnostic
 *
 * Philosophy: SEO mobile - legible text improves readability
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

/**
 * Check for small font sizes on mobile.
 */
class Diagnostic_SEO_Small_Font_Sizes {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check() {
		return array(
			'id'          => 'seo-small-font-sizes',
			'title'       => 'Check Mobile Font Sizes',
			'description' => 'Verify font size is at least 16px for body text on mobile. Smaller fonts require zooming. Use responsive typography with rem/em units.',
			'severity'    => 'low',
			'category'    => 'seo',
			'kb_link'     => 'https://wpshadow.com/kb/optimize-mobile-fonts/',
			'training_link' => 'https://wpshadow.com/training/responsive-typography/',
			'auto_fixable' => false,
			'threat_level' => 45,
		);
	}
}
