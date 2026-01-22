<?php
declare(strict_types=1);
/**
 * Exit Intent Strategy Diagnostic
 *
 * Philosophy: SEO conversion - capture leaving visitors
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for exit intent popups.
 */
class Diagnostic_SEO_Exit_Intent_Strategy extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		return array(
			'id'          => 'seo-exit-intent-strategy',
			'title'       => 'Implement Exit Intent Strategy',
			'description' => 'Use exit intent popups to capture leaving visitors: email signup, related content, special offers. Reduces bounce, increases conversions. Use tools like OptinMonster.',
			'severity'    => 'low',
			'category'    => 'seo',
			'kb_link'     => 'https://wpshadow.com/kb/exit-intent-popups/',
			'training_link' => 'https://wpshadow.com/training/conversion-tactics/',
			'auto_fixable' => false,
			'threat_level' => 40,
		);
	}
}
