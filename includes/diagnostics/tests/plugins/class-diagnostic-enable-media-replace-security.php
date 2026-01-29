<?php
/**
 * Enable Media Replace Security Diagnostic
 *
 * Enable Media Replace Security detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.771.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enable Media Replace Security Diagnostic Class
 *
 * @since 1.771.0000
 */
class Diagnostic_EnableMediaReplaceSecurity extends Diagnostic_Base {

	protected static $slug = 'enable-media-replace-security';
	protected static $title = 'Enable Media Replace Security';
	protected static $description = 'Enable Media Replace Security detected';
	protected static $family = 'security';

	public static function check() {
		if ( ! function_exists( 'emr_load_plugin' ) && ! defined( 'EMR_VERSION' ) ) {
			return null;
		}
		
		$issues = array();
		
		// Check 1: Verify role restrictions are in place
		$allowed_roles = get_option( 'enable_media_replace_allowed_roles', array( 'administrator' ) );
		if ( in_array( 'subscriber', $allowed_roles, true ) || in_array( 'contributor', $allowed_roles, true ) ) {
			$issues[] = 'low_privilege_users_can_replace_media';
		}
		
		// Check 2: Verify file type restrictions
		$allowed_filetypes = get_option( 'enable_media_replace_file_types', 'default' );
		if ( 'all' === $allowed_filetypes ) {
			$issues[] = 'all_file_types_allowed';
		}
		
		// Check 3: Check if dangerous file types are allowed
		$dangerous_types = array( 'php', 'exe', 'sh', 'bat', 'cmd', 'phtml' );
		if ( is_array( $allowed_filetypes ) ) {
			foreach ( $dangerous_types as $type ) {
				if ( in_array( $type, $allowed_filetypes, true ) ) {
					$issues[] = 'dangerous_file_types_allowed';
					break;
				}
			}
		}
		
		// Check 4: Verify media replacements are logged
		$enable_logging = get_option( 'enable_media_replace_logging', 'no' );
		if ( 'no' === $enable_logging ) {
			$issues[] = 'media_replacement_logging_disabled';
		}
		
		// Check 5: Check for recent suspicious replacements
		global $wpdb;
		$recent_replacements = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT COUNT(*) as count, post_author 
				FROM {$wpdb->posts} 
				WHERE post_type = %s 
				AND post_modified > DATE_SUB(NOW(), INTERVAL 24 HOUR)
				GROUP BY post_author
				HAVING count > 50",
				'attachment'
			)
		);
		
		if ( ! empty( $recent_replacements ) ) {
			$issues[] = 'suspicious_media_replacement_activity';
		}
		
		// Check 6: Verify file size limits are configured
		$max_file_size = get_option( 'enable_media_replace_max_file_size', 0 );
		if ( 0 === (int) $max_file_size || $max_file_size > 10 * 1024 * 1024 ) {
			$issues[] = 'no_file_size_limit_configured';
		}
		
		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %s: list of media replace security issues */
				__( 'Enable Media Replace has security issues: %s. Unrestricted media replacement can allow malicious file uploads and content injection.', 'wpshadow' ),
				implode( ', ', array_map( function( $issue ) {
					return ucwords( str_replace( '_', ' ', $issue ) );
				}, $issues ) )
			);
			
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => $description,
				'severity'     => self::calculate_severity( 60 ),
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/enable-media-replace-security',
			);
		}
		
		return null;
	}
}
