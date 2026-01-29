<?php
/**
 * W3 Total Cache Object Cache Diagnostic
 *
 * W3 Total Cache Object Cache not optimized.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.890.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * W3 Total Cache Object Cache Diagnostic Class
 *
 * @since 1.890.0000
 */
class Diagnostic_W3TotalCacheObjectCache extends Diagnostic_Base {

	protected static $slug = 'w3-total-cache-object-cache';
	protected static $title = 'W3 Total Cache Object Cache';
	protected static $description = 'W3 Total Cache Object Cache not optimized';
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
				'kb_link'     => 'https://wpshadow.com/kb/w3-total-cache-object-cache',
			);
		}
		
		return null;
	}
}
