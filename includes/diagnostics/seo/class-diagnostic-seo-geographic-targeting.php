<?php
declare(strict_types=1);
/**
 * Geographic Targeting Diagnostic
 *
 * Philosophy: SEO local - claim your territory
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for geo-targeting meta tags.
 */
class Diagnostic_SEO_Geographic_Targeting extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		return array(
			'id'          => 'seo-geographic-targeting',
			'title'       => 'Add Geographic Targeting Meta',
			'description' => 'For local businesses, add geo meta tags: <meta name="geo.region" content="US-CA"> and <meta name="geo.placename" content="San Francisco">. Improves local search visibility.',
			'severity'    => 'low',
			'category'    => 'seo',
			'kb_link'     => 'https://wpshadow.com/kb/geo-targeting-meta/',
			'training_link' => 'https://wpshadow.com/training/local-targeting/',
			'auto_fixable' => false,
			'threat_level' => 45,
		);
	}
}
