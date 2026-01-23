<?php
declare(strict_types=1);
/**
 * TTFB Performance Diagnostic
 *
 * Philosophy: Fast server response time foundation
 * @package WPShadow
 */

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

class Diagnostic_SEO_TTFB_Performance extends Diagnostic_Base {
	public static function check(): ?array {
		return array(
			'id'            => 'seo-ttfb-performance',
			'title'         => 'Time To First Byte (TTFB)',
			'description'   => 'Monitor TTFB under 200ms. Slow TTFB indicates server, database, or caching issues. Use field data from Search Console.',
			'severity'      => 'high',
			'category'      => 'seo',
			'kb_link'       => 'https://wpshadow.com/kb/ttfb-optimization/',
			'training_link' => 'https://wpshadow.com/training/server-performance/',
			'auto_fixable'  => false,
			'threat_level'  => 65,
		);
	}

}