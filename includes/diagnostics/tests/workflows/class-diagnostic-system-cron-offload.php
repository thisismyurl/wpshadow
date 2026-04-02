<?php
/**
 * System Cron Offload Configured Diagnostic (Stub)
 *
 * Generated diagnostic stub for post-install hardening checklist item 81.
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
 * System Cron Offload Configured Diagnostic Class (Stub)
 *
 * TODO: Implement robust, production-safe test logic.
 * TODO: Implement companion treatment after validation.
 * TODO: Add KB article and user-facing remediation guidance.
 *
 * @since 0.6093.1200
 */
class Diagnostic_System_Cron_Offload extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'system-cron-offload';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'System Cron Offload';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether WordPress WP-Cron has been offloaded to a real system cron job, preventing missed or page-load-delayed scheduled tasks.';

	/**
	 * Gauge family/category for dashboard placement.
	 *
	 * @var string
	 */
	protected static $family = 'workflows';

	/**
	 * Run the diagnostic check.
	 *
	 * TODO Test Plan:
	 * Check DISABLE_WP_CRON and external cron heartbeat signal.
	 *
	 * TODO Fix Plan:
	 * Fix by configuring host/system cron runner.
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
		// If DISABLE_WP_CRON is true, a system/server cron has been configured — pass.
		if ( Server_Env::is_wp_cron_disabled() ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'WordPress is using its built-in pseudo-cron, which fires only when a visitor loads a page. On low-traffic sites scheduled tasks can be delayed or skipped entirely; on high-traffic sites WP-Cron adds latency to page loads. Set DISABLE_WP_CRON to true in wp-config.php and configure a real system cron job to call wp-cron.php on a fixed schedule.', 'wpshadow' ),
			'severity'     => 'low',
			'threat_level' => 20,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/system-cron-offload',
			'details'      => array(
				'disable_wp_cron' => false,
				'fix'             => "Add define( 'DISABLE_WP_CRON', true ); to wp-config.php, then add a server cron job: */5 * * * * curl -s https://yoursite.com/wp-cron.php",
			),
		);
	}
}
