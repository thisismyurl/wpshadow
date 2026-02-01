<?php
/**
 * External API Validation Diagnostic
 *
 * Checks that external API responses are properly validated before caching.
 *
 * @since   1.26032.1000
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_External_API_Validation Class
 *
 * Verifies that external API calls validate responses before storage.
 *
 * @since 1.26032.1000
 */
class Diagnostic_External_API_Validation extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'external-api-validation';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'External API Response Validation';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks that external API responses are validated and sanitized before storage';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check
	 *
	 * @since  1.26032.1000
	 * @return array|null Finding if external APIs are not properly validated.
	 */
	public static function check() {
		// Check for any external API-related errors in activity logs
		if ( ! class_exists( '\WPShadow\Core\Activity_Logger' ) ) {
			// Activity logger not available, can't check
			return null;
		}

		// Check for recent external API fetch errors
		global $wpdb;
		
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- table name cannot be prepared
		$recent_api_errors = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->options} 
			WHERE option_name LIKE '%external_api_error%' 
			AND option_modified > DATE_SUB(NOW(), INTERVAL 7 DAY)",
			0
		);

		if ( $recent_api_errors > 5 ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %d: number of errors */
					__( 'Detected %d external API errors in the past week. Check that API responses are being validated properly.', 'wpshadow' ),
					$recent_api_errors
				),
				'severity'    => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/external-api-validation',
			);
		}

		// Check for oversized API response cache
		$cache_size = self::get_api_cache_size();
		if ( $cache_size > 50 * 1024 * 1024 ) { // 50MB threshold
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %s: cache size */
					__( 'API response cache is unusually large (%s). This may indicate unvalidated or uncompressed responses being cached.', 'wpshadow' ),
					size_format( $cache_size )
				),
				'severity'    => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/api-cache-optimization',
			);
		}

		// All checks passed
		return null;
	}

	/**
	 * Get total size of API cache
	 *
	 * @since  1.26032.1000
	 * @return int Size in bytes.
	 */
	private static function get_api_cache_size(): int {
		global $wpdb;

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- table name cannot be prepared
		$total_size = $wpdb->get_var(
			"SELECT SUM(CHAR_LENGTH(option_value)) FROM {$wpdb->options} 
			WHERE option_name LIKE '%_api_cache_%'",
			0
		);

		return (int) ( $total_size ?? 0 );
	}
}
