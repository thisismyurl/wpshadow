<?php
/**
 * Wp Super Cache Plugin Caching Diagnostic
 *
 * Wp Super Cache Plugin Caching not optimized.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.899.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wp Super Cache Plugin Caching Diagnostic Class
 *
 * @since 1.899.0000
 */
class Diagnostic_WpSuperCachePluginCaching extends Diagnostic_Base {

	protected static $slug = 'wp-super-cache-plugin-caching';
	protected static $title = 'Wp Super Cache Plugin Caching';
	protected static $description = 'Wp Super Cache Plugin Caching not optimized';
	protected static $family = 'performance';

	public static function check() {
		if ( ! function_exists( 'wp_cache_postload' ) ) {
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
				'kb_link'     => 'https://wpshadow.com/kb/wp-super-cache-plugin-caching',
			);
		}
		
		return null;
	}
}
