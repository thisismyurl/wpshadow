<?php
declare(strict_types=1);
/**
 * No Conversion Tracking Diagnostic
 *
 * Philosophy: SEO ROI - measure business impact
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for conversion tracking setup.
 */
class Diagnostic_SEO_No_Conversion_Tracking extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		return array(
			'id'          => 'seo-no-conversion-tracking',
			'title'       => 'Conversion Tracking Not Set Up',
			'description' => 'Set up conversion tracking in GA4. Track form submissions, purchases, downloads, phone clicks. Measure SEO ROI and optimize for conversions.',
			'severity'    => 'medium',
			'category'    => 'seo',
			'kb_link'     => 'https://wpshadow.com/kb/setup-conversion-tracking/',
			'training_link' => 'https://wpshadow.com/training/conversion-optimization/',
			'auto_fixable' => false,
			'threat_level' => 55,
		);
	}

}