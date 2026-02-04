<?php
/**
 * Resource Limits Check Diagnostic
 *
 * Checks if server resource limits could cause downtime.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6035.1554
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Resource Limits Check Diagnostic Class
 *
 * Verifies server resource limits won't cause downtime.
 *
 * @since 1.6035.1554
 */
class Diagnostic_Resource_Limits_Check extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'resource-limits-check';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Resource Limits Check';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if server resource limits could cause downtime';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'downtime-prevention';

	/**
	 * Run the resource limits diagnostic check.
	 *
	 * @since  1.6035.1554
	 * @return array|null Finding array if resource limit issue detected, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check PHP memory limit.
		$memory_limit = self::get_memory_limit();
		if ( $memory_limit && $memory_limit < 128 * 1024 * 1024 ) { // < 128MB
			$issues[] = sprintf(
				'PHP memory limit too low: %s (recommended: 256MB+)',
				size_format( $memory_limit )
			);
		}

		// Check max execution time.
		$max_time = (int) ini_get( 'max_execution_time' );
		if ( $max_time > 0 && $max_time < 30 ) {
			$issues[] = sprintf(
				'PHP max_execution_time too low: %ds (recommended: 60s+)',
				$max_time
			);
		}

		// Check upload limits.
		$upload_limit = self::get_upload_limit();
		if ( $upload_limit < 64 * 1024 * 1024 ) { // < 64MB
			$issues[] = sprintf(
				'Upload file size limit too low: %s (recommended: 128MB+)',
				size_format( $upload_limit )
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'Resource limit issues detected that could cause downtime:' ) . "\n- " . implode( "\n- ", $issues ),
				'severity'    => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/increase-server-resource-limits',
				'meta'        => array(
					'issues' => $issues,
				),
			);
		}

		return null;
	}

	/**
	 * Get PHP memory limit in bytes.
	 *
	 * @since  1.6035.1554
	 * @return int|null Memory limit in bytes or null if unlimited.
	 */
	private static function get_memory_limit(): ?int {
		$limit = ini_get( 'memory_limit' );

		if ( '-1' === $limit ) {
			return null; // Unlimited.
		}

		return self::parse_size_string( $limit );
	}

	/**
	 * Get upload file size limit in bytes.
	 *
	 * @since  1.6035.1554
	 * @return int Upload limit in bytes.
	 */
	private static function get_upload_limit(): int {
		$max_upload = (int) wp_max_upload_size();
		return $max_upload ? $max_upload : 64 * 1024 * 1024; // Default to 64MB.
	}

	/**
	 * Parse size string to bytes.
	 *
	 * @since  1.6035.1554
	 * @param  string $size_str Size string (e.g., "256M", "1G").
	 * @return int Size in bytes.
	 */
	private static function parse_size_string( string $size_str ): int {
		$size_str = trim( $size_str );
		$last_char = strtoupper( $size_str[ strlen( $size_str ) - 1 ] );
		$size = (int) $size_str;

		switch ( $last_char ) {
			case 'G':
				$size *= 1024;
				// Fall through.
			case 'M':
				$size *= 1024;
				// Fall through.
			case 'K':
				$size *= 1024;
				break;
		}

		return $size;
	}
}
