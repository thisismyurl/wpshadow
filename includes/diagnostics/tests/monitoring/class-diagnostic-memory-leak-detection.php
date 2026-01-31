<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }
class Diagnostic_Memory_Leak_Detection extends Diagnostic_Base {
	protected static $slug = 'memory-leak-detection';
	protected static $title = 'Memory Leak Detection';
	protected static $description = 'Compares memory at request start/end';
	protected static $family = 'monitoring';
	public static function check() {
		if ( defined( 'WPSHADOW_REQUEST_START_MEMORY' ) ) {
			$start_memory = constant( 'WPSHADOW_REQUEST_START_MEMORY' );
			$end_memory = memory_get_usage( true );
			$memory_increase = $end_memory - $start_memory;
			if ( $memory_increase > 20 * 1024 * 1024 ) {
				return array(
					'id' => self::$slug,
					'title' => self::$title,
					'description' => sprintf( __( 'Significant memory increase detected (+%dMB). Possible memory leak. Profile requests to identify problematic code or plugins.', 'wpshadow' ), round( $memory_increase / 1024 / 1024 ) ),
					'severity' => 'medium',
					'threat_level' => 30,
					'auto_fixable' => false,
					'kb_link' => 'https://wpshadow.com/kb/memory-leak-detection',
					'meta' => array( 'increase_mb' => round( $memory_increase / 1024 / 1024 ) ),
				);
			}
		}

		// Basic WordPress functionality checks
		if ( ! function_exists( 'get_option' ) ) {
			$issues[] = __( 'Options API not available', 'wpshadow' );
		}
		if ( ! function_exists( 'add_action' ) ) {
			$issues[] = __( 'WordPress hooks not available', 'wpshadow' );
		}
		if ( empty( $GLOBALS['wpdb'] ) ) {
			$issues[] = __( 'Database not initialized', 'wpshadow' );
		}
		return null;
	}
}
