<?php
/**
 * W3 Total Cache Browser Cache Diagnostic
 *
 * W3 Total Cache Browser Cache not optimized.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.888.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * W3 Total Cache Browser Cache Diagnostic Class
 *
 * @since 1.888.0000
 */
class Diagnostic_W3TotalCacheBrowserCache extends Diagnostic_Base {

	protected static $slug = 'w3-total-cache-browser-cache';
	protected static $title = 'W3 Total Cache Browser Cache';
	protected static $description = 'W3 Total Cache Browser Cache not optimized';
	protected static $family = 'performance';

	public static function check() {
		if ( ! defined( 'W3TC' ) ) {
			return null;
		}
		
		$has_issue = false;
		
		if ( $has_issue ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => self::$description,
				'severity'    => self::calculate_severity( 50 ),
				'threat_level' => 50,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/w3-total-cache-browser-cache',
			);
		}
		
		return null;
	}
}
