<?php
declare(strict_types=1);
/**
 * Back/Forward Cache (bfcache) Diagnostic
 *
 * Philosophy: bfcache enables instant back navigation
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_Bfcache_Compatibility extends Diagnostic_Base {
	public static function check(): ?array {
		return array(
			'id'            => 'seo-bfcache-compatibility',
			'title'         => 'Back/Forward Cache (bfcache) Support',
			'description'   => 'Ensure site is bfcache-compatible for instant back/forward navigation. Avoid unload event listeners.',
			'severity'      => 'low',
			'category'      => 'seo',
			'kb_link'       => 'https://wpshadow.com/kb/bfcache/',
			'training_link' => 'https://wpshadow.com/training/navigation-performance/',
			'auto_fixable'  => false,
			'threat_level'  => 15,
		);
	}

}