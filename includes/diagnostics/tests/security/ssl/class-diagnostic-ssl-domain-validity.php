<?php
/**
 * SSL Domain Validity Diagnostic
 *
 * Checks if SSL certificate matches the domain.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * SSL Domain Validity Diagnostic Class
 *
 * Verifies SSL certificate is valid for the current domain.
 * Like checking that your security badge has the right name on it.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Ssl_Domain_Validity extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'ssl-domain-validity';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'SSL Domain Validity';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if SSL certificate matches the domain';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'ssl';

	/**
	 * Run the SSL domain validity diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if domain validity issues detected, null otherwise.
	 */
	public static function check() {
		// Check if site URL uses HTTPS.
		$site_url = get_site_url();
		$home_url = get_home_url();

		if ( false === strpos( $site_url, 'https://' ) ) {
			return array(
				'id'           => self::$slug . '-not-using-ssl',
				'title'        => __( 'Site Not Using SSL/HTTPS', 'wpshadow' ),
				'description'  => __( 'Your site isn\'t using a secure connection (the padlock icon browsers show). Without SSL/HTTPS, visitor data (like passwords and credit cards) isn\'t encrypted during transmission (like sending postcards instead of sealed letters). This also hurts search rankings. Contact your hosting provider to set up a free SSL certificate.', 'wpshadow' ),
				'severity'     => 'critical',
				'threat_level' => 90,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/ssl-setup?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'context'      => array(
					'site_url' => $site_url,
					'home_url' => $home_url,
				),
			);
		}

		// Check if we're actually on HTTPS.
		if ( ! is_ssl() ) {
			return array(
				'id'           => self::$slug . '-mixed-urls',
				'title'        => __( 'Site URL Set to HTTPS But Not Loading Securely', 'wpshadow' ),
				'description'  => __( 'Your site is configured to use HTTPS in settings, but we\'re not currently on a secure connection (like having a security badge but not showing it at the door). This might be a redirect issue or server configuration problem. Check your .htaccess file or server settings to force HTTPS.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/ssl-redirect?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'context'      => array(
					'site_url' => $site_url,
					'is_ssl'   => is_ssl(),
				),
			);
		}

		$parsed = wp_parse_url( $site_url );
		$domain = $parsed['host'] ?? '';

		if ( empty( $domain ) ) {
			return null;
		}

		// Try to verify certificate domain match.
		$cert_domains = self::get_certificate_domains( $domain );

		if ( false === $cert_domains ) {
			return array(
				'id'           => self::$slug . '-cannot-verify',
				'title'        => __( 'SSL Certificate Domain Cannot Be Verified', 'wpshadow' ),
				'description'  => __( 'We couldn\'t verify your SSL certificate matches your domain (like not being able to read the name on your security badge). This is usually a temporary issue. Try again later or contact your hosting provider if this persists.', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/ssl-certificate?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'context'      => array(
					'domain' => $domain,
				),
			);
		}

		// Check if current domain matches certificate.
		$domain_matches = false;
		foreach ( $cert_domains as $cert_domain ) {
			if ( $domain === $cert_domain ) {
				$domain_matches = true;
				break;
			}

			// Check wildcard match.
			if ( 0 === strpos( $cert_domain, '*.' ) ) {
				$wildcard_pattern = str_replace( '*.', '', $cert_domain );
				if ( false !== strpos( $domain, $wildcard_pattern ) ) {
					$domain_matches = true;
					break;
				}
			}
		}

		if ( ! $domain_matches ) {
			return array(
				'id'           => self::$slug . '-domain-mismatch',
				'title'        => __( 'SSL Certificate Domain Mismatch', 'wpshadow' ),
				'description'  => sprintf(
					/* translators: 1: your domain, 2: certificate domains */
					__( 'Your SSL certificate is for a different domain (like wearing someone else\'s security badge). Your domain: %1$s. Certificate covers: %2$s. Visitors will see security warnings. Get a new SSL certificate for your domain from your hosting provider.', 'wpshadow' ),
					$domain,
					implode( ', ', $cert_domains )
				),
				'severity'     => 'critical',
				'threat_level' => 85,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/ssl-certificate?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'context'      => array(
					'domain'       => $domain,
					'cert_domains' => $cert_domains,
				),
			);
		}

		return null; // Domain matches certificate.
	}

	/**
	 * Get domains covered by SSL certificate.
	 *
	 * @since 0.6093.1200
	 * @param  string $domain Domain to check.
	 * @return array|false Array of covered domains or false on failure.
	 */
	private static function get_certificate_domains( $domain ) {
		// Try to get cached certificate domains first.
		$cache_key = 'wpshadow_ssl_domains_' . md5( $domain );
		$cached = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		// Use stream context to get certificate.
		$context = stream_context_create(
			array(
				'ssl' => array(
					'capture_peer_cert' => true,
					'verify_peer'       => false,
					'verify_peer_name'  => false,
				),
			)
		);

		$stream = @stream_socket_client(
			'ssl://' . $domain . ':443',
			$errno,
			$errstr,
			30,
			STREAM_CLIENT_CONNECT,
			$context
		);

		if ( ! $stream ) {
			return false;
		}

		$params = stream_context_get_params( $stream );
		fclose( $stream );

		if ( ! isset( $params['options']['ssl']['peer_certificate'] ) ) {
			return false;
		}

		$cert_resource = $params['options']['ssl']['peer_certificate'];
		$cert_data = openssl_x509_parse( $cert_resource );

		if ( ! $cert_data ) {
			return false;
		}

		$domains = array();

		// Get CN (Common Name).
		if ( isset( $cert_data['subject']['CN'] ) ) {
			$domains[] = $cert_data['subject']['CN'];
		}

		// Get SANs (Subject Alternative Names).
		if ( isset( $cert_data['extensions']['subjectAltName'] ) ) {
			$san_string = $cert_data['extensions']['subjectAltName'];
			$san_parts = explode( ',', $san_string );
			foreach ( $san_parts as $san ) {
				$san = trim( str_replace( 'DNS:', '', $san ) );
				if ( ! empty( $san ) && ! in_array( $san, $domains, true ) ) {
					$domains[] = $san;
				}
			}
		}

		// Cache for 1 day.
		set_transient( $cache_key, $domains, DAY_IN_SECONDS );

		return $domains;
	}
}
