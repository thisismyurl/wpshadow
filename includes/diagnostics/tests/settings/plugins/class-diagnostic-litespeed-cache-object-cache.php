<?php
/**
 * Litespeed Cache Object Cache Diagnostic
 *
 * Litespeed Cache Object Cache not optimized.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.903.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Litespeed Cache Object Cache Diagnostic Class
 *
 * @since 1.903.0000
 */
class Diagnostic_LitespeedCacheObjectCache extends Diagnostic_Base {

	protected static $slug = 'litespeed-cache-object-cache';
	protected static $title = 'Litespeed Cache Object Cache';
	protected static $description = 'Litespeed Cache Object Cache not optimized';
	protected static $family = 'performance';

	public static function check() {
		if ( ! defined( 'LSCWP_V' ) ) {
			return null;
		}
		
		$issues = array();
		// Check if feature is configured
		$option_prefix = 'diagnostic_' . str_replace('-', '_', self::$slug);
		$configured = get_option($option_prefix, false);
		if (!$configured) {
			$issues[] = 'feature not configured';
		}
		$has_issue = !empty($issues)
		
		if ( $has_issue ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => self::$description,
				'severity'    => self::calculate_severity( 50 ),
				'threat_level' => 50,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/litespeed-cache-object-cache',
			);
		}
		
		return null;
	}
}
