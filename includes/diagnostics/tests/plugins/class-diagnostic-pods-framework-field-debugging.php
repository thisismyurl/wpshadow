<?php
/**
 * Pods Framework Field Debugging Diagnostic
 *
 * Pods Framework Field Debugging issue detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1053.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Pods Framework Field Debugging Diagnostic Class
 *
 * @since 1.1053.0000
 */
class Diagnostic_PodsFrameworkFieldDebugging extends Diagnostic_Base {

	protected static $slug = 'pods-framework-field-debugging';
	protected static $title = 'Pods Framework Field Debugging';
	protected static $description = 'Pods Framework Field Debugging issue detected';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! function_exists( 'pods' ) && ! defined( 'PODS_VERSION' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Debug mode.
		if ( defined( 'PODS_DEBUG' ) && PODS_DEBUG ) {
			$issues[] = 'debug mode enabled';
		}
		
		// Check 2: Field caching.
		$field_cache = get_option( 'pods_cache_fields', '1' );
		if ( '0' === $field_cache ) {
			$issues[] = 'field caching disabled';
		}
		
		// Check 3: Query monitoring.
		$query_monitor = get_option( 'pods_query_monitor', '0' );
		if ( '1' === $query_monitor ) {
			$issues[] = 'query monitoring enabled (performance hit)';
		}
		
		// Check 4: Developer mode.
		$dev_mode = get_option( 'pods_developer_mode', '0' );
		if ( '1' === $dev_mode ) {
			$issues[] = 'developer mode enabled';
		}
		
		// Check 5: Field validation.
		$validation = get_option( 'pods_field_validation', '1' );
		if ( '0' === $validation ) {
			$issues[] = 'field validation disabled';
		}
		
		// Check 6: Error logging.
		$error_log = get_option( 'pods_error_logging', '1' );
		if ( '0' === $error_log ) {
			$issues[] = 'error logging disabled';
		}
		
		if ( ! empty( $issues ) ) {
			$threat_level = min( 65, 50 + ( count( $issues ) * 3 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'Pods Framework issues: ' . implode( ', ', $issues ),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/pods-framework-field-debugging',
			);
		}
		
		return null;
	}
}
