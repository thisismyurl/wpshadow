<?php
/**
 * Autoload Data Bloat
 *
 * Checks wp_options autoload column for excessive data accumulation
 * that can cause performance degradation on every page load.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Database
 * @since      1.6028.1052
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Autoload Data Bloat Diagnostic Class
 *
 * Detects excessive autoload data in wp_options that loads on every
 * WordPress page request, impacting performance.
 *
 * @since 1.6028.1052
 */
class Diagnostic_Autoload_Bloat extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'autoload-bloat';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Autoload Data Bloat';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks wp_options autoload accumulation (performance leak)';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'database';

	/**
	 * Autoload size threshold in KB
	 *
	 * @var int
	 */
	const THRESHOLD_KB = 800;

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6028.1052
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$cache_key = 'wpshadow_autoload_bloat_check';
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		$analysis = self::analyze_autoload();

		if ( $analysis['size_kb'] < self::THRESHOLD_KB ) {
			set_transient( $cache_key, null, 24 * HOUR_IN_SECONDS );
			return null;
		}

		$severity = self::calculate_severity( $analysis['size_kb'] );

		$finding = array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %d: autoload size in KB */
				__( 'Autoload data is %d KB, which loads on every page request and slows performance.', 'wpshadow' ),
				$analysis['size_kb']
			),
			'severity'     => $severity,
			'threat_level' => min( 70, 45 + ( $analysis['size_kb'] / 50 ) ),
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/autoload-bloat',
			'meta'         => array(
				'autoload_size_kb'    => $analysis['size_kb'],
				'autoload_count'      => $analysis['count'],
				'largest_options'     => $analysis['largest_options'],
			),
			'details'      => array(
				__( 'Autoload data is loaded on every WordPress page request', 'wpshadow' ),
				__( 'Large autoload datasets cause memory and CPU overhead', 'wpshadow' ),
				sprintf(
					/* translators: %d: recommended size in KB */
					__( 'Recommended to keep under %d KB', 'wpshadow' ),
					self::THRESHOLD_KB
				),
			),
			'recommendation' => __( 'Review large autoloaded options and disable autoload for infrequently accessed data.', 'wpshadow' ),
		);

		set_transient( $cache_key, $finding, 24 * HOUR_IN_SECONDS );
		return $finding;
	}

	/**
	 * Analyze autoload data.
	 *
	 * @since  1.6028.1052
	 * @return array Analysis results.
	 */
	private static function analyze_autoload() {
		global $wpdb;

		$autoload_data = $wpdb->get_results(
			"SELECT option_name, LENGTH(option_value) as size
			FROM {$wpdb->options}
			WHERE autoload = 'yes'
			ORDER BY size DESC",
			ARRAY_A
		);

		if ( empty( $autoload_data ) ) {
			return array(
				'size_kb' => 0,
				'count'   => 0,
				'largest_options' => array(),
			);
		}

		$total_size = array_sum( array_column( $autoload_data, 'size' ) );
		$size_kb    = (int) round( $total_size / 1024 );

		// Get largest 10 options.
		$largest = array_slice( $autoload_data, 0, 10 );
		$largest_formatted = array();

		foreach ( $largest as $option ) {
			$largest_formatted[] = array(
				'name'    => $option['option_name'],
				'size_kb' => round( $option['size'] / 1024, 2 ),
			);
		}

		return array(
			'size_kb'         => $size_kb,
			'count'           => count( $autoload_data ),
			'largest_options' => $largest_formatted,
		);
	}

	/**
	 * Calculate severity based on autoload size.
	 *
	 * @since  1.6028.1052
	 * @param  int $size_kb Size in KB.
	 * @return string Severity level.
	 */
	private static function calculate_severity( $size_kb ) {
		if ( $size_kb >= 2000 ) {
			return 'high';
		} elseif ( $size_kb >= 1200 ) {
			return 'medium';
		}
		return 'low';
	}
}
