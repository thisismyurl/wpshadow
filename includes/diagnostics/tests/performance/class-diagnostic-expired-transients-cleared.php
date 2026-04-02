<?php
/**
 * Expired Transients Cleared Diagnostic (Stub)
 *
 * Generated diagnostic stub for post-install hardening checklist item 76.
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
 * Expired Transients Cleared Diagnostic Class (Stub)
 *
 * TODO: Implement robust, production-safe test logic.
 * TODO: Implement companion treatment after validation.
 * TODO: Add KB article and user-facing remediation guidance.
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
	protected static $description = 'Stub diagnostic for Expired Transients Cleared. TODO: implement full test and remediation guidance.';

	/**
	 * Gauge family/category for dashboard placement.
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * TODO Test Plan:
	 * Count expired transient timeout rows in options table.
	 *
	 * TODO Fix Plan:
	 * Fix by running cleanup routine and scheduling it.
	 *
	 * Constraints:
	 * - Must be testable using built-in WordPress functions or PHP checks.
	 * - Must be fixable via hooks/filters/settings/DB/PHP/server setting.
	 * - Must not modify WordPress core files.
	 * - Must improve performance, security, or site success.
	 *
	 * @since  0.6093.1200
	 * @return array|null Return finding array when issue exists, null when healthy.
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
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/expired-transients',
			'details'      => array(
				'expired_transient_count' => $count,
				'note'                    => __( 'Use WP-Optimize, WP Sweep, or a similar database optimisation plugin to clear expired transients.', 'wpshadow' ),
			),
		);
	}
}
