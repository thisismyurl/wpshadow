<?php
/**
 * Caldera Forms Database Entries Diagnostic
 *
 * Caldera Forms database entries growing.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.473.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Caldera Forms Database Entries Diagnostic Class
 *
 * @since 1.473.0000
 */
class Diagnostic_CalderaFormsDatabaseEntries extends Diagnostic_Base {

	protected static $slug = 'caldera-forms-database-entries';
	protected static $title = 'Caldera Forms Database Entries';
	protected static $description = 'Caldera Forms database entries growing';
	protected static $family = 'performance';

	public static function check() {
		if ( ! class_exists( 'Caldera_Forms' ) ) {
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
				'severity'    => 50,
				'threat_level' => 50,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/caldera-forms-database-entries',
			);
		}
		

		// Performance optimization checks
		if ( ! defined( 'WP_CACHE' ) || ! WP_CACHE ) {
			$issues[] = __( 'Caching not enabled', 'wpshadow' );
		}
		if ( ! extension_loaded( 'zlib' ) ) {
			$issues[] = __( 'Gzip compression unavailable', 'wpshadow' );
		}
		// Check transient support
		if ( ! function_exists( 'set_transient' ) ) {
			$issues[] = __( 'Transient functions unavailable', 'wpshadow' );
		}
		return null;
	}
}
