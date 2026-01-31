<?php
/**
 * Wp Super Cache Cdn Support Diagnostic
 *
 * Wp Super Cache Cdn Support not optimized.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.896.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wp Super Cache Cdn Support Diagnostic Class
 *
 * @since 1.896.0000
 */
class Diagnostic_WpSuperCacheCdnSupport extends Diagnostic_Base {

	protected static $slug = 'wp-super-cache-cdn-support';
	protected static $title = 'Wp Super Cache Cdn Support';
	protected static $description = 'Wp Super Cache Cdn Support not optimized';
	protected static $family = 'performance';

	public static function check() {
		if ( ! function_exists( 'wp_cache_postload' ) ) {
			return null;
		}
		
		$issues = array();
		// Check if feature is configured
		$option_prefix = 'diagnostic_' . str_replace('-', '_', self::$slug);
		$configured = get_option($option_prefix, false);
		if (!$configured) {
			$issues[] = 'feature not configured';
		}
		$has_issue = !empty($issues);
		
		if ( $has_issue ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => self::$description,
				'severity'    => self::calculate_severity( 50 ),
				'threat_level' => 50,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/wp-super-cache-cdn-support',
			);
		}
		
		return null;
	}
}
