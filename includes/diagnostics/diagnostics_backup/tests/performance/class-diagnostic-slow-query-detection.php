<?php
/**
 * Diagnostic: Slow Query Detection
 *
 * Checks if slow query detection is enabled in WordPress.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Performance
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Diagnostic_Slow_Query_Detection
 *
 * Tests if slow query logging is configured.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Slow_Query_Detection extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'slow-query-detection';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Slow Query Detection';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if slow query detection is enabled for performance monitoring';

	/**
	 * Check slow query detection.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		$slow_query_log = defined( 'SAVEQUERIES' ) ? SAVEQUERIES : false;

		if ( ! $slow_query_log ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'Slow query detection is not enabled (SAVEQUERIES = false). Enable in wp-config.php for local development to debug slow queries.', 'wpshadow' ),
				'severity'    => 'info',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/slow_query_detection',
				'meta'        => array(
					'savequeries' => $slow_query_log,
				),
			);
		}

		// If enabled, check query count.
		$query_count = isset( $wpdb->queries ) ? count( $wpdb->queries ) : 0;

		if ( $query_count > 100 ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'High query count detected. Many queries may indicate N+1 problems or inefficient code. Review slow queries and optimize.', 'wpshadow' ),
				'severity'    => 'low',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/slow_query_detection',
				'meta'        => array(
					'query_count' => $query_count,
				),
			);
		}

		return null;
	}
}
