<?php
/**
 * Database Size and Growth Trend Diagnostic
 *
 * Tracks database size and growth over time.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Database Size and Growth Trend Diagnostic Class
 *
 * Monitors database size and alerts on unusual growth.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Database_Size_Growth_Trend extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'database-size-growth-trend';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Database Size Growth Trend';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tracks database size and growth over time';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'database-health';

	/**
	 * Growth threshold percentage
	 *
	 * @var int
	 */
	private const GROWTH_THRESHOLD = 20;

	/**
	 * Run the database growth diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if unusual growth detected, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		$current_size = self::get_database_size();

		// Log current size.
		if ( class_exists( '\WPShadow\Core\Activity_Logger' ) ) {
			\WPShadow\Core\Activity_Logger::log(
				'database_size_check',
				__( 'Database size checked', 'wpshadow' ),
				'database',
				array(
					'size' => $current_size,
				)
			);
		}

		// Get previous size from 30 days ago.
		$previous_size = self::get_previous_database_size( 30 );

		if ( ! $previous_size ) {
			return null; // Not enough data yet.
		}

		$growth_percentage = ( ( $current_size - $previous_size ) / $previous_size ) * 100;

		$result = null;

		if ( $growth_percentage > self::GROWTH_THRESHOLD ) {
			$result = array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: 1: growth percentage, 2: threshold */
					__( 'Database has grown %1$.1f%% in the last month (threshold: %2$d%%). Monitor growth patterns.', 'wpshadow' ),
					$growth_percentage,
					self::GROWTH_THRESHOLD
				),
				'severity'    => 'low',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/database-growth-management?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'meta'        => array(
					'current_size'      => self::format_bytes( $current_size ),
					'previous_size'     => self::format_bytes( $previous_size ),
					'growth_percentage' => round( $growth_percentage, 2 ),
				),
			);
		}

		return $result;
	}

	/**
	 * Get current database size.
	 *
	 * @since 0.6093.1200
	 * @return int Database size in bytes.
	 */
	private static function get_database_size(): int {
		global $wpdb;

		$size = $wpdb->get_var(
			"SELECT ROUND(SUM(data_length + index_length), 0)
			FROM information_schema.TABLES
			WHERE table_schema = '" . DB_NAME . "'"
		);

		return (int) $size;
	}

	/**
	 * Get previous database size from Activity Logger.
	 *
	 * @since 0.6093.1200
	 * @param  int $days Days to look back.
	 * @return int|null Previous database size or null if not found.
	 */
	private static function get_previous_database_size( int $days ): ?int {
		if ( ! class_exists( '\WPShadow\Core\Activity_Logger' ) ) {
			return null;
		}

		$start_time   = time() - ( $days * DAY_IN_SECONDS );
		$activity_log = get_option( \WPShadow\Core\Activity_Logger::OPTION_NAME, array() );
		$oldest_match = null;

		if ( ! is_array( $activity_log ) ) {
			return null;
		}

		foreach ( $activity_log as $entry ) {
			$entry_time = isset( $entry['timestamp'] ) ? (int) $entry['timestamp'] : 0;
			$action     = isset( $entry['action'] ) ? (string) $entry['action'] : '';

			if ( 'database_size_check' !== $action || $entry_time > $start_time ) {
				continue;
			}

			if ( null === $oldest_match || $entry_time < (int) $oldest_match['timestamp'] ) {
				$oldest_match = $entry;
			}
		}

		if ( is_array( $oldest_match ) ) {
			$metadata = isset( $oldest_match['metadata'] ) && is_array( $oldest_match['metadata'] ) ? $oldest_match['metadata'] : array();

			if ( isset( $metadata['size'] ) ) {
				return (int) $metadata['size'];
			}
		}

		return null;
	}

	/**
	 * Format bytes to human readable.
	 *
	 * @since 0.6093.1200
	 * @param  int $bytes Bytes to format.
	 * @return string Formatted bytes.
	 */
	private static function format_bytes( int $bytes ): string {
		$units = array( 'B', 'KB', 'MB', 'GB' );
		$bytes = max( $bytes, 0 );
		$pow = floor( ( $bytes ? log( $bytes ) : 0 ) / log( 1024 ) );
		$pow = min( $pow, count( $units ) - 1 );
		$bytes /= ( 1 << ( 10 * $pow ) );

		return round( $bytes, 2 ) . ' ' . $units[ $pow ];
	}
}
