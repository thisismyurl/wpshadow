<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_Base;

/**
 * Diagnostic: Memory Limit Issues
 *
 * Detects if WordPress memory limit is too low or exceeding available resources.
 * Low memory limits cause white screen of death and plugin failures.
 *
 * @since 1.2.0
 */
class Test_Memory_Limit_Issues extends Diagnostic_Base {


	/**
	 * Check memory limit configuration
	 *
	 * @return array|null Diagnostic array if issues found, null if all good
	 */
	public static function check(): ?array {
		$memory_analysis = self::analyze_memory_limits();

		if ( $memory_analysis['status'] === 'ok' ) {
			return null;
		}

		$threat = $memory_analysis['threat_level'];

		return array(
			'threat_level'  => $threat,
			'threat_color'  => 'orange',
			'passed'        => false,
			'issue'         => $memory_analysis['issue'],
			'metadata'      => $memory_analysis,
			'kb_link'       => 'https://wpshadow.com/kb/wordpress-memory-limit/',
			'training_link' => 'https://wpshadow.com/training/wordpress-performance-memory/',
		);
	}

	/**
	 * Guardian Sub-Test: WordPress memory limit
	 *
	 * @return array Test result
	 */
	public static function test_wordpress_memory_limit(): array {
		$wp_memory_limit = WP_MEMORY_LIMIT;
		$wp_memory_bytes = wp_convert_hr_to_bytes( $wp_memory_limit );

		$ok = $wp_memory_bytes >= 128 * MB_IN_BYTES;

		return array(
			'test_name'        => 'WordPress Memory Limit',
			'limit_string'     => $wp_memory_limit,
			'limit_bytes'      => $wp_memory_bytes,
			'limit_mb'         => round( $wp_memory_bytes / MB_IN_BYTES ),
			'minimum_required' => 128,
			'passed'           => $ok,
			'description'      => sprintf( 'Memory limit: %s (%d MB)', $wp_memory_limit, round( $wp_memory_bytes / MB_IN_BYTES ) ),
		);
	}

	/**
	 * Guardian Sub-Test: Server available memory
	 *
	 * @return array Test result
	 */
	public static function test_server_available_memory(): array {
		$total_memory = 0;
		$free_memory  = 0;

		// Try to read from /proc/meminfo on Linux
		if ( file_exists( '/proc/meminfo' ) ) {
			$meminfo = file_get_contents( '/proc/meminfo' );

			if ( preg_match( '/MemTotal:\s+(\d+)/', $meminfo, $matches ) ) {
				$total_memory = intval( $matches[1] ) * 1024; // KB to bytes
			}

			if ( preg_match( '/MemAvailable:\s+(\d+)/', $meminfo, $matches ) ) {
				$free_memory = intval( $matches[1] ) * 1024;
			}
		}

		$wp_memory_bytes = wp_convert_hr_to_bytes( WP_MEMORY_LIMIT );
		$ok              = $free_memory === 0 || $free_memory > $wp_memory_bytes;

		return array(
			'test_name'       => 'Server Available Memory',
			'total_memory_mb' => round( $total_memory / MB_IN_BYTES ),
			'free_memory_mb'  => round( $free_memory / MB_IN_BYTES ),
			'wp_memory_mb'    => round( $wp_memory_bytes / MB_IN_BYTES ),
			'passed'          => $ok,
			'description'     => $free_memory === 0 ? 'Could not determine server memory' : sprintf( 'Free: %d MB, WP requires: %d MB', round( $free_memory / MB_IN_BYTES ), round( $wp_memory_bytes / MB_IN_BYTES ) ),
		);
	}

	/**
	 * Guardian Sub-Test: Peak memory usage
	 *
	 * @return array Test result
	 */
	public static function test_peak_memory_usage(): array {
		$peak_usage      = memory_get_peak_usage( true );
		$wp_memory_bytes = wp_convert_hr_to_bytes( WP_MEMORY_LIMIT );
		$usage_percent   = round( ( $peak_usage / $wp_memory_bytes ) * 100, 1 );

		$status = 'normal';
		if ( $usage_percent > 90 ) {
			$status = 'critical';
		} elseif ( $usage_percent > 75 ) {
			$status = 'high';
		}

		return array(
			'test_name'     => 'Peak Memory Usage',
			'peak_usage_mb' => round( $peak_usage / MB_IN_BYTES, 2 ),
			'limit_mb'      => round( $wp_memory_bytes / MB_IN_BYTES ),
			'usage_percent' => $usage_percent,
			'status'        => $status,
			'passed'        => $status === 'normal',
			'description'   => sprintf( 'Peak usage: %d MB (%d%% of limit)', round( $peak_usage / MB_IN_BYTES, 2 ), $usage_percent ),
		);
	}

	/**
	 * Guardian Sub-Test: Multisite memory multiplier
	 *
	 * @return array Test result
	 */
	public static function test_multisite_memory(): array {
		if ( ! is_multisite() ) {
			return array(
				'test_name'   => 'Multisite Memory',
				'multisite'   => false,
				'passed'      => true,
				'description' => 'Not a multisite installation',
			);
		}

		$wp_memory_bytes = wp_convert_hr_to_bytes( WP_MEMORY_LIMIT );
		$blog_count      = get_blog_count();
		$required_mb     = ( $blog_count * 64 );
		$available_mb    = round( $wp_memory_bytes / MB_IN_BYTES );

		$ok = $available_mb >= $required_mb;

		return array(
			'test_name'    => 'Multisite Memory',
			'blog_count'   => $blog_count,
			'required_mb'  => $required_mb,
			'available_mb' => $available_mb,
			'passed'       => $ok,
			'description'  => sprintf( '%d sites need ~%d MB, have %d MB', $blog_count, $required_mb, $available_mb ),
		);
	}

	/**
	 * Analyze memory limits
	 *
	 * @return array Memory analysis
	 */
	private static function analyze_memory_limits(): array {
		$wp_memory_limit = WP_MEMORY_LIMIT;
		$wp_memory_bytes = wp_convert_hr_to_bytes( $wp_memory_limit );

		$minimum_required = 128 * MB_IN_BYTES;

		if ( $wp_memory_bytes < $minimum_required ) {
			return array(
				'status'       => 'warning',
				'threat_level' => 60,
				'issue'        => sprintf( 'Memory limit too low: %s (minimum %d MB recommended)', $wp_memory_limit, 128 ),
				'wp_memory'    => $wp_memory_bytes,
				'minimum'      => $minimum_required,
			);
		}

		$peak_usage    = memory_get_peak_usage( true );
		$usage_percent = ( $peak_usage / $wp_memory_bytes ) * 100;

		if ( $usage_percent > 90 ) {
			return array(
				'status'       => 'critical',
				'threat_level' => 75,
				'issue'        => sprintf( 'Memory usage at %.1f%% of limit', $usage_percent ),
				'peak_usage'   => $peak_usage,
				'limit'        => $wp_memory_bytes,
			);
		}

		return array(
			'status'       => 'ok',
			'threat_level' => 0,
			'issue'        => 'Memory limit is acceptable',
		);
	}

	/**
	 * Get diagnostic name
	 *
	 * @return string
	 */
	public static function get_name(): string {
		return 'Memory Limit Issues';
	}

	/**
	 * Get diagnostic description
	 *
	 * @return string
	 */
	public static function get_description(): string {
		return 'Detects if WordPress memory limit is configured appropriately for site size';
	}

	/**
	 * Get diagnostic category
	 *
	 * @return string
	 */
	public static function get_category(): string {
		return 'Performance';
	}
}
