<?php
/**
 * Cpanel Resource Limits Diagnostic
 *
 * Cpanel Resource Limits needs attention.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1037.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Cpanel Resource Limits Diagnostic Class
 *
 * @since 1.1037.0000
 */
class Diagnostic_CpanelResourceLimits extends Diagnostic_Base {

	protected static $slug = 'cpanel-resource-limits';
	protected static $title = 'Cpanel Resource Limits';
	protected static $description = 'Cpanel Resource Limits needs attention';
	protected static $family = 'functionality';

	public static function check() {
		// Check if running on cPanel (common indicators)
		$is_cpanel = file_exists( '/usr/local/cpanel/version' ) ||
		             defined( 'CPANEL' ) ||
		             getenv( 'CPANEL' ) !== false;

		if ( ! $is_cpanel ) {
			return null;
		}

		$issues = array();

		// Check 1: PHP memory limit
		$memory_limit = ini_get( 'memory_limit' );
		$memory_bytes = wp_convert_hr_to_bytes( $memory_limit );

		if ( $memory_bytes < ( 128 * 1024 * 1024 ) ) {
			$issues[] = sprintf( __( 'Low memory limit (%s)', 'wpshadow' ), $memory_limit );
		}

		// Check 2: Max execution time
		$max_execution = ini_get( 'max_execution_time' );
		if ( $max_execution < 60 && $max_execution !== 0 ) {
			$issues[] = sprintf( __( 'Short execution time (%ds)', 'wpshadow' ), $max_execution );
		}

		// Check 3: Max input vars
		$max_input_vars = ini_get( 'max_input_vars' );
		if ( $max_input_vars < 3000 ) {
			$issues[] = sprintf( __( 'Low max_input_vars (%d)', 'wpshadow' ), $max_input_vars );
		}

		// Check 4: Upload max filesize
		$upload_max = ini_get( 'upload_max_filesize' );
		$upload_bytes = wp_convert_hr_to_bytes( $upload_max );

		if ( $upload_bytes < ( 32 * 1024 * 1024 ) ) {
			$issues[] = sprintf( __( 'Small upload limit (%s)', 'wpshadow' ), $upload_max );
		}

		// Check 5: Disk space
		$disk_free = disk_free_space( ABSPATH );
		$disk_total = disk_total_space( ABSPATH );
		$disk_percent = ( $disk_total - $disk_free ) / $disk_total * 100;

		if ( $disk_percent > 90 ) {
			$issues[] = sprintf( __( 'Disk %.0f%% full (cleanup needed)', 'wpshadow' ), $disk_percent );
		}

		// Check 6: Database connection limits
		global $wpdb;
		$max_connections = $wpdb->get_var( "SHOW VARIABLES LIKE 'max_connections'" );
		if ( $max_connections && $max_connections < 50 ) {
			$issues[] = sprintf( __( 'Low DB connections (%d)', 'wpshadow' ), $max_connections );
		}

		if ( empty( $issues ) ) {
			return null;
		}

		$threat_level = 50;
		if ( count( $issues ) >= 4 ) {
			$threat_level = 62;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 56;
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: %s: list of cPanel resource limit issues */
				__( 'cPanel has %d resource limit issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => $threat_level,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/cpanel-resource-limits',
		);
	}
}
