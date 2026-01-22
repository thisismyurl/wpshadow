<?php declare(strict_types=1);
/**
 * High FID/INP Diagnostic
 *
 * Philosophy: SEO interactivity - responsiveness matters
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

/**
 * Check for poor First Input Delay / Interaction to Next Paint.
 */
class Diagnostic_SEO_High_FID_INP {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check() {
		return array(
			'id'          => 'seo-high-fid-inp',
			'title'       => 'Core Web Vitals: FID/INP Check Needed',
			'description' => 'First Input Delay (FID) should be under 100ms, INP under 200ms. Test with PageSpeed Insights. Reduce JavaScript execution time, split long tasks, use web workers.',
			'severity'    => 'medium',
			'category'    => 'seo',
			'kb_link'     => 'https://wpshadow.com/kb/improve-fid-inp/',
			'training_link' => 'https://wpshadow.com/training/interactivity/',
			'auto_fixable' => false,
			'threat_level' => 60,
		);
	}
}
