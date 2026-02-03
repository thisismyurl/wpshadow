<?php
/**
 * Incident Tracking Diagnostic
 *
 * Analyzes incident logging and resolution tracking systems.
 *
 * @since   1.26033.2155
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Incident Tracking Diagnostic
 *
 * Evaluates incident management and post-mortem processes.
 *
 * @since 1.26033.2155
 */
class Diagnostic_Incident_Tracking extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'incident-tracking';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Incident Tracking';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Analyzes incident logging and resolution tracking systems';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'monitoring';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26033.2155
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for incident tracking systems
		$has_activity_logger = class_exists( 'WPShadow\Core\Activity_Logger' );

		// Check for security audit plugins
		$audit_plugins = array(
			'wp-security-audit-log/wp-security-audit-log.php' => 'WP Activity Log',
			'simple-history/index.php'                         => 'Simple History',
			'stream/stream.php'                                => 'Stream',
		);

		$active_audit_plugin = null;
		foreach ( $audit_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$active_audit_plugin = $name;
				break;
			}
		}

		// Check for error monitoring services
		$error_monitoring = array(
			'sentry/sentry.php'     => 'Sentry',
			'raygun4wp/raygun4wp.php' => 'Raygun',
		);

		$active_error_monitoring = null;
		foreach ( $error_monitoring as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$active_error_monitoring = $name;
				break;
			}
		}

		// Check for recent incidents logged
		$incident_count = 0;
		if ( $has_activity_logger && method_exists( 'WPShadow\Core\Activity_Logger', 'get_incident_count' ) ) {
			$incident_count = \WPShadow\Core\Activity_Logger::get_incident_count( 30 );
		}

		// Estimate site complexity
		$post_count = wp_count_posts()->publish ?? 0;
		$is_woocommerce = is_plugin_active( 'woocommerce/woocommerce.php' );
		$is_complex_site = $post_count > 100 || $is_woocommerce;

		// Generate findings if no incident tracking
		if ( ! $has_activity_logger && ! $active_audit_plugin && $is_complex_site ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'No incident tracking system configured. Complex sites need audit logging for troubleshooting and compliance.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/incident-tracking',
				'meta'         => array(
					'has_activity_logger'     => $has_activity_logger,
					'active_audit_plugin'     => $active_audit_plugin,
					'is_complex_site'         => $is_complex_site,
					'recommendation'          => 'Install WP Activity Log or enable WPShadow Activity Logger',
					'incident_types'          => array(
						'Security breaches',
						'Performance degradation',
						'Plugin conflicts',
						'Database errors',
						'Failed updates',
						'User permission changes',
					),
					'tracking_benefits'       => array(
						'Troubleshooting assistance',
						'Security forensics',
						'Compliance auditing',
						'Change management',
						'User accountability',
					),
					'post_mortem_importance'  => 'Document incidents to prevent recurrence',
				),
			);
		}

		// Alert on high incident rate
		if ( $incident_count > 10 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of incidents */
					__( '%d incidents logged in last 30 days. High incident rate - review root causes.', 'wpshadow' ),
					$incident_count
				),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/incident-tracking',
				'meta'         => array(
					'incident_count'  => $incident_count,
					'recommendation'  => 'Conduct post-mortem analysis on recurring incidents',
					'analysis_steps'  => array(
						'1. Identify incident patterns',
						'2. Document root causes',
						'3. Implement preventive measures',
						'4. Update monitoring alerts',
						'5. Share learnings with team',
					),
				),
			);
		}

		// Recommendation for error monitoring
		if ( ! $active_error_monitoring && $is_complex_site ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'No error monitoring service configured. Sentry/Raygun provide real-time error tracking.', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/incident-tracking',
				'meta'         => array(
					'active_error_monitoring' => $active_error_monitoring,
					'recommendation'          => 'Consider Sentry for production error monitoring',
					'service_benefits'        => array(
						'Real-time error notifications',
						'Error grouping and trends',
						'Stack traces and context',
						'Release tracking',
						'User impact metrics',
					),
				),
			);
		}

		return null;
	}
}
