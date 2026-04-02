<?php
/**
 * Expired Transients Cleared Diagnostic
 *
 * Counts expired transient timeout entries in the options table to detect
 * database bloat from uncleaned transients when no external object cache is used.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Diagnostics\Helpers\Diagnostic_Server_Environment_Helper as Server_Env;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Expired Transients Cleared Diagnostic Class
 *
 * @since 0.6093.1200
 */
class Diagnostic_Expired_Transients_Cleared extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'expired-transients-cleared';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Expired Transients Cleared';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'A large number of expired transients are accumulating in the database. This bloats the options table and slows autoload queries.';

	/**
	 * Gauge family/category for dashboard placement.
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * Skips when an external object cache is active. Otherwise counts expired
	 * _transient_timeout_ rows and flags when the total exceeds the threshold.
	 *
	 * @since  0.6093.1200
	 * @return array|null Finding array when expired transients exceed threshold, null when healthy.
	 */
	public static function check() {
		$count = Server_Env::get_expired_transient_count();

		// Fewer than 50 expired transients is not worth flagging.
		if ( $count < 50 ) {
			return null;
		}

		$severity     = $count > 500 ? 'medium' : 'low';
		$threat_level = $count > 500 ? 35 : 20;

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %d: number of expired transients */
				__( '%d expired transients are still stored in your wp_options table. Expired transients have not been cleaned up because WordPress only removes a transient when its key is specifically requested. They accumulate over time, bloating your database and slowing down queries on the options table.', 'wpshadow' ),
				$count
			),
			'severity'     => $severity,
			'threat_level' => $threat_level,
				'auto_fixable' => true,
			'kb_link'      => 'https://wpshadow.com/kb/expired-transients?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			'details'      => array(
				'expired_transient_count' => $count,
				'note'                    => __( 'Use WP-Optimize, WP Sweep, or a similar database optimisation plugin to clear expired transients.', 'wpshadow' ),
			),
		);
	}
}
