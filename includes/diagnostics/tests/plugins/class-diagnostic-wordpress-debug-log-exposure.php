<?php
/**
 * Wordpress Debug Log Exposure Diagnostic
 *
 * Wordpress Debug Log Exposure issue detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1273.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Wordpress Debug Log Exposure Diagnostic Class
 *
 * @since 1.1273.0000
 */
class Diagnostic_WordpressDebugLogExposure extends Diagnostic_Base {

	protected static $slug = 'wordpress-debug-log-exposure';
	protected static $title = 'Wordpress Debug Log Exposure';
	protected static $description = 'Wordpress Debug Log Exposure issue detected';
	protected static $family = 'security';

	public static function check() {
		// WordPress core feature - always check
		$issues = array();
		
		// Check 1: Verify if WP_DEBUG is enabled in production
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			$issues[] = 'wp_debug_enabled';
		}
		
		// Check 2: Verify if debug.log file exists and is accessible
		$debug_log_path = WP_CONTENT_DIR . '/debug.log';
		if ( file_exists( $debug_log_path ) ) {
			$issues[] = 'debug_log_file_exists';
			
			// Check 3: Verify file size (if too large, performance issue)
			$file_size = filesize( $debug_log_path );
			if ( $file_size > 10 * 1024 * 1024 ) { // 10MB
				$issues[] = 'debug_log_file_too_large';
			}
			
			// Check 4: Verify if debug.log is publicly accessible via URL
			$debug_log_url = content_url( 'debug.log' );
			$response = wp_remote_get( $debug_log_url, array( 'timeout' => 5 ) );
			
			if ( ! is_wp_error( $response ) ) {
				$status_code = wp_remote_retrieve_response_code( $response );
				if ( 200 === $status_code ) {
					$issues[] = 'debug_log_publicly_accessible';
					// This is CRITICAL - debug logs can contain passwords, API keys, etc.
				}
			}
			
			// Check 5: Check if log contains sensitive data patterns
			if ( $file_size < 1024 * 1024 ) { // Only check if < 1MB
				$log_content = file_get_contents( $debug_log_path, false, null, 0, 10000 );
				$sensitive_patterns = array(
					'/password/i',
					'/api[_-]?key/i',
					'/secret/i',
					'/token/i',
					'/mysql/i',
				);
				
				foreach ( $sensitive_patterns as $pattern ) {
					if ( preg_match( $pattern, $log_content ) ) {
						$issues[] = 'debug_log_contains_sensitive_data';
						break;
					}
				}
			}
		}
		
		// Check 6: Verify if WP_DEBUG_LOG is explicitly set
		if ( defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
			$issues[] = 'wp_debug_log_enabled';
		}
		
		// Check 7: Verify if WP_DEBUG_DISPLAY is enabled (bad in production)
		if ( defined( 'WP_DEBUG_DISPLAY' ) && WP_DEBUG_DISPLAY ) {
			$issues[] = 'wp_debug_display_enabled';
		}
		
		// Check 8: Check if SCRIPT_DEBUG is enabled
		if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) {
			$issues[] = 'script_debug_enabled';
		}
		
		if ( ! empty( $issues ) ) {
			$issues = array_unique( $issues );
			
			// Calculate threat level based on severity
			$threat_level = 70;
			if ( in_array( 'debug_log_publicly_accessible', $issues, true ) ) {
				$threat_level = 85; // CRITICAL - exposed log file
			} elseif ( in_array( 'debug_log_contains_sensitive_data', $issues, true ) ) {
				$threat_level = 80; // HIGH - sensitive data in logs
			}
			
			$description = sprintf(
				/* translators: %s: list of debug log exposure issues */
				__( 'WordPress debug configuration has security issues: %s. Debug logs can expose sensitive information including passwords, API keys, database credentials, and system paths. Debug mode should be disabled in production.', 'wpshadow' ),
				implode( ', ', array_map( function( $issue ) {
					return ucwords( str_replace( '_', ' ', $issue ) );
				}, $issues ) )
			);
			
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => $description,
				'severity'     => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false, // Requires wp-config.php modification
				'kb_link'      => 'https://wpshadow.com/kb/wordpress-debug-log-exposure',
			);
		}
		
		return null;
	}
}
