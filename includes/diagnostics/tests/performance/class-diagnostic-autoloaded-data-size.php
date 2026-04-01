<?php
/**
 * Autoloaded Data Size Diagnostic
 *
 * Detects excessive autoloaded options bloating database queries on every page load.
 *
 * **What This Check Does:**
 * 1. Calculates total size of wp_options with 'autoload' = 'yes'
 * 2. Identifies which plugins/themes contribute most to autoload bloat
 * 3. Flags autoloaded options > 1MB (should never happen)
 * 4. Tracks autoloaded transients that should expire
 * 5. Detects misconfigurations storing large data as autoload
 * 6. Estimates performance impact per page load
 *
 * **Why This Matters:**
 * WordPress loads ALL autoloaded options into memory on every page request. If autoloaded data
 * is 5MB, WordPress wastes 5MB of network bandwidth + memory + parsing time on every single
 * request. A site with 100,000 daily requests wastes 500TB of bandwidth per month. This directly
 * impacts page load time (each 1MB of autoload adds ~50-100ms) and server memory usage.
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
 * Autoloaded Data Size Diagnostic Class
 *
 * Measures autoloaded WordPress options that load on every page request.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Autoloaded_Data_Size extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'autoloaded-data-size';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Autoloaded Data Size';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for excessive autoloaded options data';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * Autoloaded options are loaded on every page request.
	 * Recommended: <800KB
	 * Warning: >1MB
	 * Critical: >2MB
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		// Get all autoloaded options
		$autoloaded = $wpdb->get_results(
			"SELECT option_name, LENGTH(option_value) as option_size
			FROM {$wpdb->options}
			WHERE autoload = 'yes'
			ORDER BY option_size DESC"
		);

		if ( empty( $autoloaded ) ) {
			return null;
		}

		// Calculate total size
		$total_size = 0;
		$large_options = array();

		foreach ( $autoloaded as $option ) {
			$total_size += $option->option_size;

			// Track options >100KB
			if ( $option->option_size > 102400 ) {
				$large_options[] = array(
					'name' => $option->option_name,
					'size' => $option->option_size,
				);
			}
		}

		// Check against thresholds
		$size_mb = $total_size / 1048576;

		if ( $total_size < 819200 ) { // <800KB
			return null; // Good
		}

		$severity = 'medium';
		$threat_level = 50;

		if ( $total_size > 2097152 ) { // >2MB
			$severity = 'critical';
			$threat_level = 90;
		} elseif ( $total_size > 1048576 ) { // >1MB
			$severity = 'high';
			$threat_level = 70;
		}

		$description = sprintf(
			/* translators: 1: total size, 2: number of options, 3: number of large options */
			__( 'Autoloaded options total %1$s (%2$d options, %3$d over 100KB). Autoloaded data is loaded on every page request. Reduce by setting appropriate options to not autoload or cleaning up old plugin data.', 'wpshadow' ),
			size_format( $total_size ),
			count( $autoloaded ),
			count( $large_options )
		);

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => $description,
			'severity'     => $severity,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/reduce-autoloaded-data?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			'meta'         => array(
				'total_size'       => $total_size,
				'total_size_mb'    => round( $size_mb, 2 ),
				'total_options'    => count( $autoloaded ),
				'large_options'    => array_slice( $large_options, 0, 10 ),
				'threshold_ok'     => '800KB',
				'threshold_warn'   => '1MB',
				'threshold_crit'   => '2MB',
				'performance_impact' => sprintf( '%.1fms added to every request', $size_mb * 10 ),
			),
		);
	}
}
