<?php
/**
 * System Cron Offload Configured Diagnostic
 *
 * Checks whether WordPress WP-Cron has been offloaded to a real system cron
 * job. Using the built-in pseudo-cron causes missed or page-load-delayed tasks
 * on low-traffic sites and adds latency on high-traffic sites.
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
 * Diagnostic_System_Cron_Offload Class
 *
 * Uses the Server_Env helper to check whether DISABLE_WP_CRON is set. Returns
 * null when cron is offloaded to a system scheduler. Returns a low-severity
 * finding with fix guidance when WP-Cron is still running inline.
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
	 * Calls the Server_Env helper to check whether DISABLE_WP_CRON is set.
	 * Returns null when the constant is true, indicating a system or hosting
	 * scheduler is handling cron. Returns a low-severity finding with wp-config
	 * and crontab fix guidance when WP-Cron is still traffic-dependent.
	 *
	 * @since  0.6093.1200
	 * @return array|null Finding array when WP-Cron is not offloaded, null when healthy.
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
			'kb_link'      => 'https://wpshadow.com/kb/system-cron-offload?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			'details'      => array(
				'disable_wp_cron' => false,
				'fix'             => "Add define( 'DISABLE_WP_CRON', true ); to wp-config.php, then add a server cron job: */5 * * * * curl -s https://yoursite.com/wp-cron.php",
			),
		);
	}
}
