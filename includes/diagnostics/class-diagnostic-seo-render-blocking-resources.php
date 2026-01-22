<?php declare(strict_types=1);
/**
 * Render-Blocking Resources Diagnostic
 *
 * Philosophy: SEO performance - defer non-critical CSS/JS
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

/**
 * Check for render-blocking resources.
 */
class Diagnostic_SEO_Render_Blocking_Resources {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check() {
		return array(
			'id'          => 'seo-render-blocking-resources',
			'title'       => 'Render-Blocking Resources',
			'description' => 'Check for render-blocking CSS/JS in PageSpeed Insights. Defer non-critical CSS/JS, inline critical CSS, use async/defer attributes. Improves FCP and LCP.',
			'severity'    => 'medium',
			'category'    => 'seo',
			'kb_link'     => 'https://wpshadow.com/kb/eliminate-render-blocking/',
			'training_link' => 'https://wpshadow.com/training/critical-rendering-path/',
			'auto_fixable' => false,
			'threat_level' => 60,
		);
	}
}
