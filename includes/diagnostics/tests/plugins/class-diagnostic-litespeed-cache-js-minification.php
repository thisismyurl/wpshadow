<?php
/**
 * Litespeed Cache Js Minification Diagnostic
 *
 * Litespeed Cache Js Minification not optimized.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.905.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Litespeed Cache Js Minification Diagnostic Class
 *
 * @since 1.905.0000
 */
class Diagnostic_LitespeedCacheJsMinification extends Diagnostic_Base {

	protected static $slug = 'litespeed-cache-js-minification';
	protected static $title = 'Litespeed Cache Js Minification';
	protected static $description = 'Litespeed Cache Js Minification not optimized';
	protected static $family = 'performance';

	public static function check() {
		if ( ! defined( 'LSCWP_V' ) ) {
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
				'kb_link'     => 'https://wpshadow.com/kb/litespeed-cache-js-minification',
			);
		}
		
		return null;
	}
}
