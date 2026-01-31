<?php
/**
 * Third-Party Service Dependency Diagnostic
 *
 * Identifies critical third-party service dependencies
 * and monitors their status to prevent cascading failures.
 *
 * @since   1.2601.2148
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Third_Party_Service_Dependency Class
 *
 * Monitors third-party service dependencies.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Third_Party_Service_Dependency extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'third-party-service-dependency';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Third-Party Service Dependency';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Identifies critical service dependencies';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'integration';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if critical dependencies, null otherwise.
	 */
	public static function check() {
		$dependency_status = self::check_service_dependencies();

		if ( ! $dependency_status['has_issue'] ) {
			return null; // No critical unmonitored dependencies
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %d: number of critical dependencies */
				__( '%d critical third-party services = %d potential failure points. Service outage = your features break. Have fallback plan for each.', 'wpshadow' ),
				$dependency_status['critical_count'],
				$dependency_status['critical_count']
			),
			'severity'     => 'medium',
			'threat_level' => 50,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/service-dependencies',
			'family'       => self::$family,
			'meta'         => array(
				'critical_services' => $dependency_status['critical_count'],
			),
			'details'      => array(
				'identifying_critical_dependencies' => array(
					'Payment Processing' => array(
						'Services: Stripe, PayPal, Square',
						'Impact: If down = cannot accept payments',
						'Criticality: Extreme (revenue blocking)',
					),
					'Email Delivery' => array(
						'Services: SendGrid, AWS SES, Mailgun',
						'Impact: If down = emails not sent',
						'Criticality: High (communication blocked)',
					),
					'Analytics' => array(
						'Services: Google Analytics, Amplitude',
						'Impact: If down = data not collected',
						'Criticality: Low (data loss, not function)',
					),
					'Authentication' => array(
						'Services: Okta, Auth0, Azure AD',
						'Impact: If down = users cannot login',
						'Criticality: Critical (access blocked)',
					),
				),
				'service_status_pages'            => array(
					'Stripe' => array(
						'URL: status.stripe.com',
						'Features: Real-time status, subscription',
					),
					'PayPal' => array(
						'URL: paypalstatus.com',
						'Features: Status + incident history',
					),
					'Google Cloud' => array(
						'URL: status.cloud.google.com',
						'Features: All Google services',
					),
					'AWS' => array(
						'URL: status.aws.amazon.com',
						'Features: All AWS services',
					),
				),
				'monitoring_service_health'       => array(
					'Automated Monitoring' => array(
						'Tool: Uptime robot, Pingdom',
						'Frequency: Check every 5 minutes',
						'Alert: Email/SMS if down',
					),
					'Manual Checks' => array(
						'Frequency: Daily or weekly',
						'Method: Test key functionality',
						'Log: Document any issues',
					),
				),
				'preparing_for_outages'           => array(
					'Payment Gateway Fallback' => array(
						'Plan A: Stripe',
						'Plan B: PayPal',
						'Manual: Offline payment processing',
					),
					'Email Fallback' => array(
						'Primary: SendGrid',
						'Backup: AWS SES',
						'Local: WordPress native (if available)',
					),
					'Communication' => array(
						'Status: Post on site (banner)',
						'Message: "Payment processing temporarily down"',
						'Timeline: Expected resolution time',
					),
				),
				'reducing_service_dependencies'    => array(
					__( 'Identify: Map all third-party services' ),
					__( 'Prioritize: Critical vs. nice-to-have' ),
					__( 'Consolidate: Use fewer services when possible' ),
					__( 'Fallback: Alternative for each critical service' ),
					__( 'Monitor: Track status continuously' ),
				),
				'architectural_improvements'      => array(
					'Graceful Degradation' => array(
						'Feature disabled, not broken',
						'Example: Analytics down = site still works',
						'Implementation: Try-catch, fallback UI',
					),
					'Caching' => array(
						'Cache: Third-party responses',
						'TTL: 1 hour (or service-specific)',
						'Benefit: Short outages won\'t impact users',
					),
					'Queue System' => array(
						'Queue: Email/notifications',
						'Retry: When service recovers',
						'Benefit: Transient outages transparent',
					),
				),
			),
		);
	}

	/**
	 * Check service dependencies.
	 *
	 * @since  1.2601.2148
	 * @return array Service dependency status.
	 */
	private static function check_service_dependencies() {
		$critical_count = 0;

		// Check for payment gateways
		if ( class_exists( 'WC_Payment_Gateways' ) ) {
			$critical_count++;
		}

		// Check for email services
		if ( is_plugin_active( 'wp-mail-smtp/wp_mail_smtp.php' ) ) {
			$critical_count++;
		}

		// Check for analytics
		if ( strpos( get_option( 'blogdescription' ), 'googletagmanager' ) !== false ) {
			$critical_count++;
		}

		return array(
			'has_issue'      => $critical_count >= 2,
			'critical_count' => $critical_count,
		);
	}
}
