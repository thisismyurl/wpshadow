<?php declare(strict_types=1);
/**
 * Poor CLS Score Diagnostic
 *
 * Philosophy: SEO UX - CLS measures visual stability
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

/**
 * Check for poor Cumulative Layout Shift (CLS).
 */
class Diagnostic_SEO_Poor_CLS_Score {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check() {
		return array(
			'id'          => 'seo-poor-cls-score',
			'title'       => 'Core Web Vitals: CLS Check Needed',
			'description' => 'Cumulative Layout Shift (CLS) should be under 0.1. Test with PageSpeed Insights. Add width/height to images, reserve space for ads, avoid inserting content above existing content.',
			'severity'    => 'medium',
			'category'    => 'seo',
			'kb_link'     => 'https://wpshadow.com/kb/improve-cls/',
			'training_link' => 'https://wpshadow.com/training/visual-stability/',
			'auto_fixable' => false,
			'threat_level' => 60,
		);
	}
}
