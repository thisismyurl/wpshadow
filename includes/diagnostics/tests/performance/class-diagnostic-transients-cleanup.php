<?php
/**
 * Transient Cleanup Diagnostic
 *
 * Detects expired transients and transient bloat accumulating in the options
 * table, which increases database query overhead on every page request.
 *
 * @package WPShadow
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
 * Diagnostic_Transients_Cleanup Class
 *
 * @since 0.6093.1200
 */
class Diagnostic_Transients_Cleanup extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'transients-cleanup';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Transient Cleanup';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks for expired transients and excessive transient counts accumulating in the options table, which slow database queries on every page load.';

	/**
	 * Gauge family/category.
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Confidence level of this diagnostic.
	 *
	 * @var string
	 */
	protected static $confidence = 'standard';

	/**
	 * Run the diagnostic check.
	 *
	 * Skipped when an external object cache is active (transients bypass the DB).
	 * Counts expired transients and total transients; flags when either exceeds
	 * the defined thresholds.
	 *
	 * @since  0.6093.1200
	 * @return array|null Finding array if issue exists, null if healthy.
	 */
	public static function check() {
		// If an external object cache is active, transients are not stored in the DB.
		if ( wp_using_ext_object_cache() ) {
			return null;
		}

		global $wpdb;

		$expired_count = (int) $wpdb->get_var(
			"SELECT COUNT(*)
			 FROM {$wpdb->options}
			 WHERE option_name LIKE '_transient_timeout_%'
			   AND option_value < UNIX_TIMESTAMP()"
		);

		$total_transient_count = (int) $wpdb->get_var(
			"SELECT COUNT(*)
			 FROM {$wpdb->options}
			 WHERE option_name LIKE '_transient_%'
			   AND option_name NOT LIKE '_transient_timeout_%'"
		);

		if ( $expired_count >= 50 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of expired transients */
					__( '%d expired transients are sitting in the options table. WordPress is supposed to delete them when they expire but missed deletions accumulate and bloat the table, slowing every DB query that scans options. Run a cleanup using WP-Optimize or WP-CLI (wp transient delete --expired) to remove them.', 'wpshadow' ),
					$expired_count
				),
				'severity'     => 'medium',
				'threat_level' => 35,
				'kb_link'      => '',
				'details'      => array(
					'expired_transients' => $expired_count,
					'total_transients'   => $total_transient_count,
				),
			);
		}

		if ( $total_transient_count > 300 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: total transient count */
					__( '%d transients are stored in the options table. An unusually high count suggests one or more plugins are generating transients without cleaning them up, causing options-table bloat. Use WP-Optimize or WP-CLI to audit and remove stale transients.', 'wpshadow' ),
					$total_transient_count
				),
				'severity'     => 'low',
				'threat_level' => 20,
				'kb_link'      => '',
				'details'      => array(
					'expired_transients' => $expired_count,
					'total_transients'   => $total_transient_count,
				),
			);
		}

		return null;
	}
}
