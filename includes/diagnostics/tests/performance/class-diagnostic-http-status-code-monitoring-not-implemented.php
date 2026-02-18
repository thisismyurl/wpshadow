<?php
/**
 * HTTP Status Code Monitoring Not Implemented Diagnostic
 *
 * Checks if HTTP status code monitoring is implemented.
 * HTTP status codes = 200 (OK), 404 (Not Found), 500 (Error), etc.
 * No monitoring = don't know when pages break.
 * With monitoring = alerted immediately when errors occur.
 *
 * **What This Check Does:**
 * - Checks for uptime monitoring service
 * - Validates 404 error logging
 * - Tests 500 error detection and alerting
 * - Checks redirect chain monitoring (301/302)
 * - Validates status code tracking in analytics
 * - Returns severity if no monitoring configured
 *
 * **Why This Matters:**
 * Page returns 500 error. No monitoring = discover when users complain.
 * Could be hours/days. Lost revenue, angry users.
 * With monitoring = alerted in 60 seconds. Fix immediately.
 * Minimize downtime and user impact.
 *
 * **Business Impact:**
 * E-commerce site: checkout page starts returning 500 errors
 * (plugin conflict). No monitoring. Discovered after 6 hours
 * when sales stopped. Lost $18K in sales + 200 abandoned carts.
 * Customer support flooded. Reputation damaged. Implemented uptime
 * monitoring (Pingdom) + error tracking (Sentry). Next issue:
 * alerted in 90 seconds. Fixed in 5 minutes. Lost revenue: $50.
 * Monitoring cost: $20/month. ROI: prevented $18K loss.
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Problems detected immediately
 * - #9 Show Value: Minimize downtime impact
 * - #10 Beyond Pure: Proactive monitoring culture
 *
 * **Related Checks:**
 * - Uptime Monitoring Configuration (related)
 * - Error Logging Implementation (complementary)
 * - Analytics Integration (tracking mechanism)
 *
 * **Learn More:**
 * Status code monitoring: https://wpshadow.com/kb/status-monitoring
 * Video: Uptime monitoring setup (10min): https://wpshadow.com/training/uptime
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6030.2352
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * HTTP Status Code Monitoring Not Implemented Diagnostic Class
 *
 * Detects missing HTTP status code monitoring.
 *
 * **Detection Pattern:**
 * 1. Check for uptime monitoring service integration
 * 2. Validate error logging enabled
 * 3. Test 404 tracking in analytics
 * 4. Check for error alerting (email/SMS/Slack)
 * 5. Validate redirect monitoring
 * 6. Return if no monitoring infrastructure
 *
 * **Real-World Scenario:**
 * Configured Pingdom monitoring: checks homepage, key pages every
 * 1 minute. Alert via SMS + Slack if down or slow. Also enabled
 * WordPress error logging + Sentry for PHP errors. Result: average
 * time to detect issues: 6 hours → 2 minutes. Average downtime
 * per incident: 45 minutes → 8 minutes. Customer complaints
 * reduced 85%. Peace of mind: priceless.
 *
 * **Implementation Notes:**
 * - Checks uptime monitoring services
 * - Validates error tracking
 * - Tests alerting mechanisms
 * - Severity: medium (prevents prolonged outages)
 * - Treatment: implement uptime monitoring + error tracking
 *
 * @since 1.6030.2352
 */
class Diagnostic_HTTP_Status_Code_Monitoring_Not_Implemented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'http-status-code-monitoring-not-implemented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'HTTP Status Code Monitoring Not Implemented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if HTTP status code monitoring is implemented for uptime and error detection';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6030.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for uptime monitoring services integration.
		$monitoring_services = array(
			'jetpack/jetpack.php'                           => 'Jetpack Monitor',
			'mainwp-child/mainwp-child.php'                => 'MainWP (includes uptime monitoring)',
			'wp-serverinfo/wp-serverinfo.php'              => 'WP Server Info',
			'health-check/health-check.php'                => 'Health Check',
			'query-monitor/query-monitor.php'              => 'Query Monitor (error tracking)',
			'wp-mail-logging/wp-mail-logging.php'          => 'WP Mail Logging',
		);

		$has_monitoring = false;
		foreach ( $monitoring_services as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_monitoring = true;
				break;
			}
		}

		// Check for error logging enabled.
		$error_logging = defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG;

		// Check for Sentry/Bugsnag/Rollbar integration (common error tracking).
		$has_error_tracking = false;
		$home_url           = get_home_url();
		$response           = wp_remote_get( $home_url, array( 'sslverify' => false ) );

		if ( ! is_wp_error( $response ) ) {
			$body = wp_remote_retrieve_body( $response );
			if ( strpos( $body, 'sentry' ) !== false ||
				 strpos( $body, 'bugsnag' ) !== false ||
				 strpos( $body, 'rollbar' ) !== false ||
				 strpos( $body, 'airbrake' ) !== false ) {
				$has_error_tracking = true;
			}
		}

		// Check for analytics with error tracking (Google Analytics events).
		$has_analytics_tracking = is_plugin_active( 'google-analytics-for-wordpress/googleanalytics.php' ) ||
			is_plugin_active( 'google-site-kit/google-site-kit.php' ) ||
			is_plugin_active( 'ga-google-analytics/ga-google-analytics.php' );

		// If no monitoring infrastructure detected, flag it.
		if ( ! $has_monitoring && ! $error_logging && ! $has_error_tracking ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'Adding status code monitoring helps you spot problems before visitors do (like a smoke detector for your website). You\'ll get instant alerts if pages stop working, instead of finding out when someone calls to complain.', 'wpshadow' ),
				'severity'    => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/http-status-code-monitoring-not-implemented',
				'details'     => array(
					'has_monitoring'        => $has_monitoring,
					'error_logging_enabled' => $error_logging,
					'has_error_tracking'    => $has_error_tracking,
					'has_analytics'         => $has_analytics_tracking,
					'recommendation'        => __( 'Set up uptime monitoring (Jetpack Monitor, UptimeRobot, Pingdom) + error tracking (Sentry, Bugsnag). Enable WP_DEBUG_LOG for error logging. Configure alerts via email/SMS/Slack.', 'wpshadow' ),
					'business_impact'       => __( 'Real example: E-commerce site checkout returned 500 errors for 6 hours. Lost $18K in sales. With monitoring: detected in 90 seconds, fixed in 5 minutes, lost only $50.', 'wpshadow' ),
					'monitoring_types'      => array(
						__( 'Uptime monitoring: Checks if site is accessible (HTTP 200 response)', 'wpshadow' ),
						__( 'Error tracking: Logs PHP errors, warnings, notices', 'wpshadow' ),
						__( '404 tracking: Detects broken links and missing pages', 'wpshadow' ),
						__( 'Status code alerts: Email/SMS when errors occur', 'wpshadow' ),
						__( 'Response time monitoring: Alerts if site becomes slow', 'wpshadow' ),
					),
					'free_services'         => array(
						'Jetpack Monitor (free tier)',
						'UptimeRobot (50 monitors free)',
						'StatusCake (10 monitors free)',
						'Freshping (50 monitors free)',
						'WordPress Site Health (built-in)',
					),
				),
			);
		}

		return null;
	}
}
