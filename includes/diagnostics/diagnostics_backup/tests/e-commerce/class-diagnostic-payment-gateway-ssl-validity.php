<?php
/**
 * Payment Gateway SSL Certificate Validity Diagnostic
 *
 * Verifies that payment processor SSL certificates are valid and not expired,
 * which could cause payment failures.
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
 * Diagnostic_Payment_Gateway_SSL_Validity Class
 *
 * Checks payment processor SSL certificate validity to prevent transaction failures.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Payment_Gateway_SSL_Validity extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'payment-gateway-ssl-validity';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Payment Gateway SSL Certificate Validity';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks payment processor SSL certificates';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'e-commerce';

	/**
	 * Payment processor domains
	 *
	 * @var array
	 */
	const PAYMENT_PROCESSORS = array(
		'api.stripe.com'       => 'Stripe',
		'api.paypal.com'       => 'PayPal',
		'api.square.com'       => 'Square',
		'api.authorize.net'    => 'Authorize.net',
		'secure.braintreegateway.com' => 'Braintree',
		'checkout.razorpay.com' => 'Razorpay',
	);

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if certificate issues found, null otherwise.
	 */
	public static function check() {
		$certificate_issues = self::check_payment_processor_certificates();

		if ( empty( $certificate_issues ) ) {
			// All certificates valid
			return null;
		}

		$count = count( $certificate_issues );

		return array(
			'id'            => self::$slug,
			'title'         => self::$title,
			'description'   => sprintf(
				/* translators: %d: number of payment processors with issues */
				__( 'Found SSL certificate issues with %d payment processor %s. Transactions may be failing.', 'wpshadow' ),
				$count,
				( $count === 1 ? __( 'connection', 'wpshadow' ) : __( 'connections', 'wpshadow' ) )
			),
			'severity'      => 'critical',
			'threat_level'  => 95,
			'auto_fixable'  => false,
			'kb_link'       => 'https://wpshadow.com/kb/payment-gateway-ssl',
			'family'        => self::$family,
			'meta'          => array(
				'certificate_issue_count' => $count,
				'affected_processors'     => array_slice( $certificate_issues, 0, 5 ),
				'revenue_impact'          => __( 'Transactions are likely failing - immediate revenue loss', 'wpshadow' ),
				'immediate_actions'       => array(
					__( 'Test payment processing immediately' ),
					__( 'Contact payment processor support' ),
					__( 'Check firewall/SSL inspection settings' ),
					__( 'Verify HTTP client certificate configuration' ),
				),
			),
			'details'       => array(
				'issue'            => sprintf(
					/* translators: %d: number of processors */
					__( '%d payment processor %s have SSL certificate issues.', 'wpshadow' ),
					$count,
					( $count === 1 ? __( 'connection', 'wpshadow' ) : __( 'connections', 'wpshadow' ) )
				),
				'revenue_impact'   => __( 'CRITICAL - Customers cannot complete purchases. Every minute of downtime loses revenue.', 'wpshadow' ),
				'common_causes'    => array(
					__( 'Expired certificate on payment processor (rare)' ) => __( 'Usually fixed by payment provider' ),
					__( 'Certificate chain incomplete' ) => __( 'Intermediate certificates missing' ),
					__( 'Firewall SSL inspection' ) => __( 'Some firewalls intercept HTTPS - disable or whitelist' ),
					__( 'Antivirus SSL inspection' ) => __( 'Some security software interferes with SSL' ),
					__( 'Server date/time incorrect' ) => __( 'Certificate validation fails with wrong system time' ),
					__( 'PHP OpenSSL issues' ) => __( 'CA bundle not properly configured' ),
				),
				'troubleshooting'  => array(
					'Step 1: Test payment' => __( 'Attempt a test transaction - see if it fails' ),
					'Step 2: Check server time' => __( 'Confirm server date/time is accurate (NTP sync)' ),
					'Step 3: Test connectivity' => __( 'Use curl to test API endpoint directly' ),
					'Step 4: Check firewall' => __( 'Temporarily disable firewall SSL inspection' ),
					'Step 5: Contact support' => __( 'If still failing, contact payment processor support' ),
				),
			),
		);
	}

	/**
	 * Check payment processor SSL certificates.
	 *
	 * @since  1.2601.2148
	 * @return array List of certificate issues found.
	 */
	private static function check_payment_processor_certificates() {
		$issues = array();

		foreach ( self::PAYMENT_PROCESSORS as $domain => $processor_name ) {
			// Check SSL certificate validity
			$response = wp_remote_get(
				"https://{$domain}",
				array(
					'timeout'   => 5,
					'sslverify' => true,
					'blocking'  => true,
				)
			);

			if ( is_wp_error( $response ) ) {
				$error_message = $response->get_error_message();

				// Check for SSL certificate errors
				if ( stripos( $error_message, 'certificate' ) !== false || 
					 stripos( $error_message, 'ssl' ) !== false ||
					 stripos( $error_message, 'peer' ) !== false ) {
					
					$issues[] = array(
						'processor'      => $processor_name,
						'domain'         => $domain,
						'error'          => $error_message,
						'severity'       => 'critical',
						'revenue_impact' => 'HIGH',
					);
				}
			}
		}

		return $issues;
	}
}
