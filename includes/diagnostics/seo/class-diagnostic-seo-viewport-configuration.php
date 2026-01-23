<?php
declare(strict_types=1);
/**
 * Viewport Configuration Diagnostic
 *
 * Philosophy: SEO mobile - proper viewport is essential
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Check for proper viewport meta tag.
 */
class Diagnostic_SEO_Viewport_Configuration extends Diagnostic_Base {
	/**
	 * Run the diagnostic check.
	 *
	 * @return array|null Finding data or null if no issue.
	 */
	public static function check(): ?array {
		return array(
			'id'          => 'seo-viewport-configuration',
			'title'       => 'Verify Viewport Meta Tag',
			'description' => 'Ensure viewport meta tag is present: <meta name="viewport" content="width=device-width, initial-scale=1">. Required for mobile responsiveness.',
			'severity'    => 'medium',
			'category'    => 'seo',
			'kb_link'     => 'https://wpshadow.com/kb/add-viewport-tag/',
			'training_link' => 'https://wpshadow.com/training/mobile-optimization/',
			'auto_fixable' => false,
			'threat_level' => 60,
		);
	}

}