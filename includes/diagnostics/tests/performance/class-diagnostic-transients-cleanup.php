<?php
/**
 * Transient Cleanup Diagnostic
 *
 * Detects expired transients and transient bloat accumulating in the options
 * table, which increases database query overhead on every page request.
 *
 * @package ThisIsMyURL\Shadow
 * @subpackage Diagnostics
 * @since 0.6095
 */

declare(strict_types=1);

namespace ThisIsMyURL\Shadow\Diagnostics;

use ThisIsMyURL\Shadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Transients_Cleanup Class
 *
 * @since 0.6095
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
	 * @since  0.6095
	 * @return array|null Finding array if issue exists, null if healthy.
	 */
	public static function check() {
		// If an external object cache is active, transients are not stored in the DB.
		if ( wp_using_ext_object_cache() ) {
			return null;
		}

		global $wpdb;

		/*
		 * This diagnostic stays on $wpdb because it needs aggregate counts across transient rows in
		 * wp_options. WordPress core can read or delete a known transient, but it does not expose a
		 * count API for "all expired transient timeout rows" or "all transient value rows". COUNT(*)
		 * queries are the correct tool for this kind of health check.
		 */

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
					__( '%d expired transients are sitting in the options table. WordPress is supposed to delete them when they expire but missed deletions accumulate and bloat the table, slowing every DB query that scans options. Run a cleanup using WP-Optimize or WP-CLI (wp transient delete --expired) to remove them.', 'thisismyurl-shadow' ),
					$expired_count
				),
				'severity'     => 'medium',
				'threat_level' => 35,
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
					__( '%d transients are stored in the options table. An unusually high count suggests one or more plugins are generating transients without cleaning them up, causing options-table bloat. Use WP-Optimize or WP-CLI to audit and remove stale transients.', 'thisismyurl-shadow' ),
					$total_transient_count
				),
				'severity'     => 'low',
				'threat_level' => 20,
				'details'      => array(
					'expired_transients' => $expired_count,
					'total_transients'   => $total_transient_count,
				),
			);
		}

		return null;
	}
}
