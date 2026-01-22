<?php
declare(strict_types=1);
/**
 * Dwell Time Optimization Diagnostic
 *
 * Philosophy: SEO engagement - keep visitors on page
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for dwell time optimization strategies.
 */
class Diagnostic_SEO_Dwell_Time_Optimization extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		return array(
			'id'          => 'seo-dwell-time-optimization',
			'title'       => 'Optimize for Dwell Time',
			'description' => 'Improve dwell time (time on page before returning to SERPs): Use table of contents, add related posts, embed videos, improve readability, answer questions comprehensively.',
			'severity'    => 'low',
			'category'    => 'seo',
			'kb_link'     => 'https://wpshadow.com/kb/improve-dwell-time/',
			'training_link' => 'https://wpshadow.com/training/engagement-metrics/',
			'auto_fixable' => false,
			'threat_level' => 50,
		);
	}
}
