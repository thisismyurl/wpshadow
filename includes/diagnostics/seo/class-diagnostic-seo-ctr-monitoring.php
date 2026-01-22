<?php
declare(strict_types=1);
/**
 * Click-Through Rate Monitoring Diagnostic
 *
 * Philosophy: SEO performance - monitor CTR in GSC
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check if CTR is monitored.
 */
class Diagnostic_SEO_CTR_Monitoring extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		return array(
			'id'          => 'seo-ctr-monitoring',
			'title'       => 'Monitor Click-Through Rate',
			'description' => 'Review CTR in Google Search Console. Low CTR (<2%) despite good rankings means poor title/meta. Test different titles/descriptions to improve CTR.',
			'severity'    => 'low',
			'category'    => 'seo',
			'kb_link'     => 'https://wpshadow.com/kb/improve-ctr/',
			'training_link' => 'https://wpshadow.com/training/ctr-optimization/',
			'auto_fixable' => false,
			'threat_level' => 45,
		);
	}
}
