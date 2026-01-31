<?php
/**
 * SSL Certificate Expiration Monitoring Diagnostic
 *
 * Monitors SSL certificate expiration and alerts when approaching expiration.
 * Checks certificate validity and triggers warnings at 30 days and 7 days.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Security
 * @since      1.6027.1450
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * SSL Certificate Expiration Diagnostic Class
 *
 * Monitors the SSL certificate for the current site and alerts when
 * the certificate is approaching expiration. Expired SSL certificates
 * cause browser warnings and site inaccessibility.
 *
 * @since 1.6027.1450
 */
class Diagnostic_SSL_Expiration extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'ssl-expiration';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'SSL Certificate Expiration Monitoring';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Monitors SSL certificate expiration and alerts when approaching';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Warning threshold in days
	 *
	 * @var int
	 */
	private const WARNING_THRESHOLD = 30;

	/**
	 * Critical threshold in days
	 *
	 * @var int
	 */
	private const CRITICAL_THRESHOLD = 7;

	/**
	 * Cache key for SSL certificate data
	 *
	 * @var string
	 */
	private const CACHE_KEY = 'wpshadow_ssl_cert_data';

	/**
	 * Cache duration in seconds (12 hours)
	 *
	 * @var int
	 */
	private const CACHE_DURATION = 43200;

	/**
	 * Run the diagnostic check
	 *
	 * Retrieves SSL certificate information and calculates days until expiration.
	 * Triggers warnings based on thresholds:
	 * - 30+ days: No alert (null)
	 * - 7-30 days: Medium severity (threat 50)
	 * - 0-7 days: Critical severity (threat 75)
	 * - Expired: Critical severity (threat 90)
	 *
	 * @since  1.6027.1450
	 * @return array|null Finding array if certificate expiring/expired, null otherwise.
	 */
	public static function check() {
		// Early bailout if site not using HTTPS.
		if ( ! self::is_site_https() ) {
			return null;
		}

		// Get certificate information (cached).
		$cert_data = self::get_certificate_data();

		if ( is_wp_error( $cert_data ) ) {
			// Unable to retrieve certificate, but don't fail diagnostic.
			return null;
		}

		if ( empty( $cert_data['expiration_date'] ) ) {
			return null;
		}

		// Calculate days until expiration.
		$days_remaining = self::calculate_days_remaining( $cert_data['expiration_date'] );

		// If more than warning threshold, no alert.
		if ( $days_remaining > self::WARNING_THRESHOLD ) {
			return null;
		}

		// Build finding based on severity.
		return self::build_finding( $cert_data, $days_remaining );
	}

	/**
	 * Check if site is using HTTPS
	 *
	 * @since  1.6027.1450
	 * @return bool True if site URL uses HTTPS.
	 */
	private static function is_site_https(): bool {
		$site_url = get_site_url();
		return strpos( $site_url, 'https://' ) === 0;
	}

	/**
	 * Get certificate data from cache or fetch fresh
	 *
	 * @since  1.6027.1450
	 * @return array|\WP_Error Certificate data or error.
	 */
	private static function get_certificate_data() {
		// Check cache first.
		$cached = get_transient( self::CACHE_KEY );
		if ( false !== $cached && is_array( $cached ) ) {
			return $cached;
		}

		// Fetch fresh certificate data.
		$cert_data = self::fetch_certificate_data();

		// Cache successful results.
		if ( ! is_wp_error( $cert_data ) ) {
			set_transient( self::CACHE_KEY, $cert_data, self::CACHE_DURATION );
		}

		return $cert_data;
	}

	/**
	 * Fetch SSL certificate data from domain
	 *
	 * @since  1.6027.1450
	 * @return array|\WP_Error Certificate data or error on failure.
	 */
	private static function fetch_certificate_data() {
		$site_url = get_site_url();
		$domain   = wp_parse_url( $site_url, PHP_URL_HOST );

		if ( empty( $domain ) ) {
			return new \WP_Error( 'invalid_domain', __( 'Unable to parse domain from site URL', 'wpshadow' ) );
		}

		// Handle localhost and development domains.
		if ( self::is_development_domain( $domain ) ) {
			return new \WP_Error( 'dev_domain', __( 'Development domain detected', 'wpshadow' ) );
		}

		// Attempt to retrieve certificate.
		$context = stream_context_create(
			array(
				'ssl' => array(
					'capture_peer_cert' => true,
					'verify_peer'       => false,
					'verify_peer_name'  => false,
				),
			)
		);

		// Suppress warnings from stream operations.
		$client = @stream_socket_client(
			"ssl://{$domain}:443",
			$errno,
			$errstr,
			30,
			STREAM_CLIENT_CONNECT,
			$context
		);

		if ( false === $client ) {
			return new \WP_Error( 'connection_failed', sprintf(
				/* translators: %s: error message */
				__( 'Unable to connect to domain: %s', 'wpshadow' ),
				$errstr
			) );
		}

		$params = stream_context_get_params( $client );
		fclose( $client );

		if ( empty( $params['options']['ssl']['peer_certificate'] ) ) {
			return new \WP_Error( 'no_certificate', __( 'No SSL certificate found', 'wpshadow' ) );
		}

		$cert_resource = $params['options']['ssl']['peer_certificate'];
		$cert_data     = openssl_x509_parse( $cert_resource );

		if ( false === $cert_data ) {
			return new \WP_Error( 'parse_failed', __( 'Unable to parse SSL certificate', 'wpshadow' ) );
		}

		return array(
			'issuer'          => isset( $cert_data['issuer']['CN'] ) ? $cert_data['issuer']['CN'] : 'Unknown',
			'subject'         => isset( $cert_data['subject']['CN'] ) ? $cert_data['subject']['CN'] : $domain,
			'expiration_date' => isset( $cert_data['validTo_time_t'] ) ? $cert_data['validTo_time_t'] : null,
			'issued_date'     => isset( $cert_data['validFrom_time_t'] ) ? $cert_data['validFrom_time_t'] : null,
			'serial_number'   => isset( $cert_data['serialNumber'] ) ? $cert_data['serialNumber'] : 'Unknown',
		);
	}

	/**
	 * Check if domain is a development domain
	 *
	 * @since  1.6027.1450
	 * @param  string $domain Domain to check.
	 * @return bool True if development domain.
	 */
	private static function is_development_domain( string $domain ): bool {
		$dev_indicators = array( 'localhost', '127.0.0.1', '.local', '.test', '.dev', 'staging' );

		foreach ( $dev_indicators as $indicator ) {
			if ( stripos( $domain, $indicator ) !== false ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Calculate days remaining until expiration
	 *
	 * @since  1.6027.1450
	 * @param  int $expiration_timestamp Unix timestamp of expiration.
	 * @return int Days remaining (negative if expired).
	 */
	private static function calculate_days_remaining( int $expiration_timestamp ): int {
		$now              = time();
		$seconds_remaining = $expiration_timestamp - $now;
		return (int) floor( $seconds_remaining / DAY_IN_SECONDS );
	}

	/**
	 * Build finding array based on certificate data
	 *
	 * @since  1.6027.1450
	 * @param  array $cert_data       Certificate data.
	 * @param  int   $days_remaining  Days until expiration.
	 * @return array Finding data.
	 */
	private static function build_finding( array $cert_data, int $days_remaining ): array {
		// Determine severity and threat level.
		$severity     = $days_remaining;
		$threat_level = self::calculate_threat_level( $days_remaining );

		// Build description.
		$description = self::build_description( $days_remaining );

		// Build details.
		$details = self::build_finding_details( $cert_data, $days_remaining );

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => $description,
			'severity'     => $severity,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/security-ssl-expiration',
			'family'       => self::$family,
			'meta'         => array(
				'days_remaining'   => $days_remaining,
				'expiration_date'  => gmdate( 'Y-m-d H:i:s', $cert_data['expiration_date'] ),
				'issuer'           => $cert_data['issuer'],
				'subject'          => $cert_data['subject'],
				'certificate_age'  => self::calculate_certificate_age( $cert_data ),
			),
			'details'      => $details,
		);
	}

	/**
	 * Calculate severity based on days remaining
	 *
	 * @since  1.6027.1450
	 * @param  int $days_remaining Days until expiration.
	 * @return string Severity level.
	 */
	private static function calculate_severity( int $days_remaining ): string {
		if ( $days_remaining < 0 ) {
			return 'critical';
		}

		if ( $days_remaining <= self::CRITICAL_THRESHOLD ) {
			return 'critical';
		}

		return 'medium';
	}

	/**
	 * Calculate threat level based on days remaining
	 *
	 * @since  1.6027.1450
	 * @param  int $days_remaining Days until expiration.
	 * @return int Threat level 50-90.
	 */
	private static function calculate_threat_level( int $days_remaining ): int {
		if ( $days_remaining < 0 ) {
			// Expired certificate is critical.
			return 90;
		}

		if ( $days_remaining <= self::CRITICAL_THRESHOLD ) {
			// 0-7 days: critical.
			return 75;
		}

		// 8-30 days: medium.
		return 50;
	}

	/**
	 * Build human-readable description
	 *
	 * @since  1.6027.1450
	 * @param  int $days_remaining Days until expiration.
	 * @return string Description text.
	 */
	private static function build_description( int $days_remaining ): string {
		if ( $days_remaining < 0 ) {
			return sprintf(
				/* translators: %d: number of days */
				__( 'SSL certificate expired %d days ago - site is showing security warnings', 'wpshadow' ),
				abs( $days_remaining )
			);
		}

		if ( $days_remaining === 0 ) {
			return __( 'SSL certificate expires today - immediate action required', 'wpshadow' );
		}

		if ( $days_remaining === 1 ) {
			return __( 'SSL certificate expires tomorrow - urgent action required', 'wpshadow' );
		}

		return sprintf(
			/* translators: %d: number of days */
			__( 'SSL certificate expires in %d days - renewal recommended', 'wpshadow' ),
			$days_remaining
		);
	}

	/**
	 * Build detailed finding information
	 *
	 * @since  1.6027.1450
	 * @param  array $cert_data       Certificate data.
	 * @param  int   $days_remaining  Days until expiration.
	 * @return array<string, mixed> Detailed finding data.
	 */
	private static function build_finding_details( array $cert_data, int $days_remaining ): array {
		$is_expired = $days_remaining < 0;

		return array(
			'why_matters'         => array(
				__( 'Expired SSL certificates trigger browser security warnings', 'wpshadow' ),
				__( 'Users will see "Your connection is not private" errors', 'wpshadow' ),
				__( 'Search engines may deindex pages with expired certificates', 'wpshadow' ),
				__( 'Loss of trust and immediate traffic drop', 'wpshadow' ),
			),
			'impact'              => array(
				'user_experience' => $is_expired ? __( 'Site inaccessible to most users', 'wpshadow' ) : __( 'Impending service disruption', 'wpshadow' ),
				'seo'             => $is_expired ? __( 'Search rankings severely impacted', 'wpshadow' ) : __( 'Potential ranking loss if expired', 'wpshadow' ),
				'trust'           => $is_expired ? __( 'Complete loss of visitor trust', 'wpshadow' ) : __( 'Trust at risk if not renewed', 'wpshadow' ),
			),
			'remediation_steps'   => self::get_remediation_steps( $is_expired ),
			'certificate_details' => array(
				'issuer'      => $cert_data['issuer'],
				'subject'     => $cert_data['subject'],
				'issued_on'   => gmdate( 'Y-m-d', $cert_data['issued_date'] ),
				'expires_on'  => gmdate( 'Y-m-d', $cert_data['expiration_date'] ),
				'serial'      => $cert_data['serial_number'],
			),
			'hosting_providers'   => array(
				__( 'Many hosts offer free SSL renewal through cPanel or control panel', 'wpshadow' ),
				__( 'Let\'s Encrypt certificates auto-renew if properly configured', 'wpshadow' ),
				__( 'Contact hosting support if unsure how to renew', 'wpshadow' ),
			),
		);
	}

	/**
	 * Get remediation steps based on expiration status
	 *
	 * @since  1.6027.1450
	 * @param  bool $is_expired Whether certificate is expired.
	 * @return array Remediation steps.
	 */
	private static function get_remediation_steps( bool $is_expired ): array {
		if ( $is_expired ) {
			return array(
				__( '1. URGENT: Contact hosting provider immediately', 'wpshadow' ),
				__( '2. Request immediate SSL certificate renewal', 'wpshadow' ),
				__( '3. If using Let\'s Encrypt, check auto-renewal configuration', 'wpshadow' ),
				__( '4. Verify DNS is pointing to correct server', 'wpshadow' ),
				__( '5. Test site after renewal: https://www.ssllabs.com/ssltest/', 'wpshadow' ),
			);
		}

		return array(
			__( '1. Contact hosting provider about SSL renewal', 'wpshadow' ),
			__( '2. Check if auto-renewal is enabled (recommended)', 'wpshadow' ),
			__( '3. Verify renewal process to avoid expiration', 'wpshadow' ),
			__( '4. Set calendar reminder 60 days before next expiration', 'wpshadow' ),
		);
	}

	/**
	 * Calculate certificate age in days
	 *
	 * @since  1.6027.1450
	 * @param  array $cert_data Certificate data.
	 * @return int Certificate age in days.
	 */
	private static function calculate_certificate_age( array $cert_data ): int {
		if ( empty( $cert_data['issued_date'] ) ) {
			return 0;
		}

		$now           = time();
		$age_seconds   = $now - $cert_data['issued_date'];
		return (int) floor( $age_seconds / DAY_IN_SECONDS );
	}
}
