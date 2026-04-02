<?php
/**
 * WP Cron Traffic Dependence Reviewed Diagnostic (Stub)
 *
 * TODO stub mapped to the workflows gauge.
 *
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Diagnostics\Helpers\Diagnostic_Server_Environment_Helper as Server_Env;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Cron_Traffic_Dependence_Reviewed Class
 *
 * TODO: Implement full test logic and remediation guidance.
 */
class Diagnostic_Cron_Traffic_Dependence extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'cron-traffic-dependence';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'WP Cron Traffic Dependence';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether WP-Cron fires inline during page loads (traffic-dependent), which adds latency for visitors and causes tasks to be missed on low-traffic sites.';

	/**
	 * Gauge family/category.
	 *
	 * @var string
	 */
	protected static $family = 'workflows';

	/**
	 * Run the diagnostic check.
	 *
	 * TODO Test Plan:
	 * - Check DISABLE_WP_CRON and real cron integration signals.
	 *
	 * TODO Fix Plan:
	 * - Use a real server cron when reliability matters more than visit-triggered cron.
	 * - Use WordPress hooks, filters, settings, DB fixes, PHP config, or accessible server settings.
	 * - Do not modify WordPress core files.
	 * - Ensure performance/security/success impact and align with WPShadow commandments.
	 *
	 * @since  0.6093.1200
	 * @return array|null Finding array if issue exists, null if healthy.
	 */
	public static function check() {
		// If cron is offloaded to a system scheduler, there is no traffic dependence.
		if ( Server_Env::is_wp_cron_disabled() ) {
			return null;
		}

		// ALTERNATE_WP_CRON fires cron via a redirect after the response is sent
		// — less disruptive but still requires a visitor to trigger it.
		$alternate = defined( 'ALTERNATE_WP_CRON' ) && ALTERNATE_WP_CRON;

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => $alternate
				? __( 'WP-Cron is running in alternate mode (ALTERNATE_WP_CRON is true). Scheduled tasks fire via a background HTTP redirect after the visitor response is sent, which reduces page-load impact but still requires visitor traffic to trigger execution. Replace with a real system cron job for reliable scheduling.', 'wpshadow' )
				: __( 'WP-Cron is running inline: scheduled tasks execute during a visitor\'s page load request. This adds latency for that visitor and means tasks never run when there is no traffic. Replace with a real system cron job for reliable, traffic-independent scheduling.', 'wpshadow' ),
			'severity'     => $alternate ? 'low' : 'medium',
			'threat_level' => $alternate ? 15 : 30,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/cron-traffic-dependence',
			'details'      => array(
				'disable_wp_cron'   => false,
				'alternate_wp_cron' => $alternate,
				'fix'               => "Add define( 'DISABLE_WP_CRON', true ); to wp-config.php and add a system cron: */5 * * * * curl -s https://yoursite.com/wp-cron.php",
			),
		);
	}
}
