<?php
declare(strict_types=1);
/**
 * Bounce Rate Analysis Diagnostic
 *
 * Philosophy: SEO engagement - high bounce rate hurts rankings
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for high bounce rate pages.
 */
class Diagnostic_SEO_Bounce_Rate_Analysis extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		return array(
			'id'          => 'seo-bounce-rate-analysis',
			'title'       => 'Analyze Bounce Rate in GA4',
			'description' => 'Review bounce rate in Analytics. High bounce (>70%) signals poor content match or UX. Improve with: better intro, clear CTAs, faster load, relevant content.',
			'severity'    => 'low',
			'category'    => 'seo',
			'kb_link'     => 'https://wpshadow.com/kb/reduce-bounce-rate/',
			'training_link' => 'https://wpshadow.com/training/engagement-optimization/',
			'auto_fixable' => false,
			'threat_level' => 50,
		);
	}

}