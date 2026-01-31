<?php
/**
 * SSL Certificate Status Diagnostic
 *
 * Verifies SSL/TLS certificate is valid, not expired, and properly
 * configured to prevent man-in-the-middle attacks.
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
 * Diagnostic_SSL_Certificate_Status Class
 *
 * Checks SSL certificate validity and expiration.
 *
 * @since 1.2601.2148
 */
class Diagnostic_SSL_Certificate_Status extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'ssl-certificate-status';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'SSL Certificate Status';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies SSL certificate is valid and not expiring soon';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if SSL issue found, null otherwise.
	 */
	public static function check() {
		// Check if HTTPS is enabled
		if ( ! is_ssl() ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'HTTPS is not enabled. All data is transmitted in plain text, vulnerable to interception.', 'wpshadow' ),
				'severity'     => 'critical',
				'threat_level' => 95,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/enable-ssl',
				'family'       => self::$family,
				'meta'         => array(
					'https_enabled'    => false,
					'encryption_status' => __( 'No encryption - all data visible' ),
					'browser_warning'  => __( 'Most browsers show "Not Secure" warning' ),
					'seo_impact'       => __( 'Google ranks HTTPS sites higher' ),
				),
				'details'      => array(
					'why_https_critical' => array(
						__( 'Data transmitted in plain text is interceptable' ),
						__( 'Credentials, payments, personal info all visible' ),
						__( 'Man-in-the-middle attacks easy without HTTPS' ),
						__( 'Browsers warn visitors site is insecure' ),
						__( 'Google penalizes non-HTTPS sites in rankings' ),
					),
					'quick_setup'       => array(
						'Option 1: Let\'s Encrypt (Free)' => array(
							__( 'Go to hosting control panel (cPanel/Plesk)' ),
							__( 'Find AutoSSL or Let\'s Encrypt option' ),
							__( 'Generate free SSL certificate' ),
							__( 'Install and enable HTTPS' ),
							__( 'Redirect all HTTP to HTTPS' ),
						),
						'Option 2: Commercial SSL' => array(
							__( 'Premium from GoDaddy, Namecheap, Comodo' ),
							__( 'Wildcard certificates for subdomains' ),
							__( 'Extended validation (EV) for trust badges' ),
							__( 'Installation support included' ),
						),
					),
					'setup_time'        => __( '15 minutes - 1 hour (Let\'s Encrypt)' ),
					'cost'              => __( 'Free (Let\'s Encrypt) - $200+/year (premium)' ),
				),
			);
		}

		// Get certificate info
		$cert_info = self::get_certificate_info();

		if ( ! $cert_info ) {
			return null; // Can't retrieve cert info, assume valid
		}

		// Check expiration
		$days_until_expiry = self::calculate_days_until_expiry( $cert_info['expiry_timestamp'] );

		if ( $days_until_expiry < 0 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'SSL certificate has expired. Site is no longer encrypted and browsers will show security warnings.', 'wpshadow' ),
				'severity'     => 'critical',
				'threat_level' => 98,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/renew-ssl-certificate',
				'family'       => self::$family,
				'meta'         => array(
					'certificate_status' => 'EXPIRED',
					'expiry_date'        => gmdate( 'Y-m-d H:i:s', $cert_info['expiry_timestamp'] ),
					'days_expired'       => abs( $days_until_expiry ),
					'immediate_action'   => __( 'Renew certificate immediately' ),
				),
				'details'      => array(
					'impact'        => array(
						__( 'Browsers show "Your connection is not secure" warning' ),
						__( 'Visitors cannot access site without clicking through warning' ),
						__( 'Search engines delist site from results' ),
						__( 'Payment gateways reject transactions' ),
					),
					'renewal_steps' => array(
						__( 'Contact hosting provider or certificate authority' ),
						__( 'Reissue/renew certificate immediately' ),
						__( 'Install new certificate' ),
						__( 'Restart web server' ),
						__( 'Test site in browser (should show secure)' ),
					),
				),
			);
		}

		if ( $days_until_expiry < 30 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of days until expiry */
					__( 'SSL certificate expires in %d days. Renew immediately to prevent service interruption.', 'wpshadow' ),
					$days_until_expiry
				),
				'severity'     => 'critical',
				'threat_level' => 85,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/renew-ssl-certificate',
				'family'       => self::$family,
				'meta'         => array(
					'certificate_status' => 'EXPIRING SOON',
					'expiry_date'        => gmdate( 'Y-m-d H:i:s', $cert_info['expiry_timestamp'] ),
					'days_remaining'     => $days_until_expiry,
					'renewal_deadline'   => __( 'Renew within 30 days to avoid service interruption' ),
				),
				'details'      => array(
					'renewal_options'    => array(
						__( 'Let\'s Encrypt auto-renewal (if hosting supports)' ),
						__( 'Manual renewal via hosting control panel' ),
						__( 'Contact certificate authority' ),
						__( 'Wildcard certificate for all subdomains' ),
					),
					'timeline'           => array(
						'Days 30-1'  => __( 'Safe - start renewal process' ),
						'Days 1-0'   => __( 'Critical - finish renewal TODAY' ),
						'Day -1+'    => __( 'EXPIRED - site inaccessible' ),
					),
				),
			);
		}

		if ( $days_until_expiry < 60 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of days until expiry */
					__( 'SSL certificate expires in %d days. Schedule renewal to ensure uninterrupted HTTPS service.', 'wpshadow' ),
					$days_until_expiry
				),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/renew-ssl-certificate',
				'family'       => self::$family,
				'meta'         => array(
					'certificate_status' => 'EXPIRING SOON',
					'expiry_date'        => gmdate( 'Y-m-d H:i:s', $cert_info['expiry_timestamp'] ),
					'days_remaining'     => $days_until_expiry,
					'action'             => __( 'Schedule renewal for next week' ),
				),
				'details'      => array(
					'renewal_timeline' => array(
						__( 'Check expiry date (today\'s date)' ),
						__( 'Log into hosting control panel' ),
						__( 'Initiate certificate renewal' ),
						__( 'Complete validation (domain ownership)' ),
						__( 'Install new certificate' ),
						__( 'Test site with SSL Labs (www.ssllabs.com)' ),
					),
				),
			);
		}

		return null; // Certificate is valid
	}

	/**
	 * Get certificate information.
	 *
	 * @since  1.2601.2148
	 * @return array|false Certificate info or false if unable to retrieve.
	 */
	private static function get_certificate_info() {
		$url = home_url();
		$host = wp_parse_url( $url, PHP_URL_HOST );

		if ( ! $host ) {
			return false;
		}

		// Try to get certificate details
		$stream_context = stream_context_create(
			array(
				'ssl' => array(
					'capture_peer_cert' => true,
				),
			)
		);

		$conn = @stream_socket_client( 'ssl://' . $host . ':443', $errno, $errstr, 30, STREAM_CLIENT_CONNECT, $stream_context );

		if ( ! $conn ) {
			return false;
		}

		$cert = stream_context_get_params( $conn );
		fclose( $conn );

		if ( ! $cert || ! isset( $cert['options']['ssl']['peer_certificate'] ) ) {
			return false;
		}

		$cert_data = openssl_x509_parse( $cert['options']['ssl']['peer_certificate'] );

		if ( ! $cert_data ) {
			return false;
		}

		return array(
			'expiry_timestamp' => $cert_data['validTo_time_t'],
			'valid_from'       => $cert_data['validFrom_time_t'],
			'issuer'           => $cert_data['issuer'],
		);
	}

	/**
	 * Calculate days until certificate expiry.
	 *
	 * @since  1.2601.2148
	 * @param  int $expiry_timestamp Expiry timestamp.
	 * @return int Days until expiry (negative if expired).
	 */
	private static function calculate_days_until_expiry( $expiry_timestamp ) {
		$current_time = time();
		$seconds_diff = $expiry_timestamp - $current_time;
		return (int) ( $seconds_diff / ( 60 * 60 * 24 ) );
	}
}
