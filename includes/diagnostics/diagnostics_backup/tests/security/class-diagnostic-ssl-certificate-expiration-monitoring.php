<?php
/**
 * SSL Certificate Expiration Monitoring Diagnostic
 *
 * Tracks SSL certificate expiration and provides advance warnings.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26028.1905
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * SSL Certificate Expiration Monitoring Class
 *
 * Tests SSL certificate validity and expiration.
 *
 * @since 1.26028.1905
 */
class Diagnostic_SSL_Certificate_Expiration_Monitoring extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'ssl-certificate-expiration-monitoring';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'SSL Certificate Expiration Monitoring';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tracks SSL certificate expiration and provides advance warnings';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26028.1905
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if site uses SSL.
		if ( ! is_ssl() ) {
			// Not using SSL is a separate issue (handled by other diagnostics).
			return null;
		}

		$issues = array();
		$cert_info = self::get_certificate_info();

		if ( ! $cert_info['success'] ) {
			$issues[] = __( 'Unable to retrieve SSL certificate information', 'wpshadow' );
		} else {
			// Check expiration.
			if ( $cert_info['days_until_expiration'] < 0 ) {
				$issues[] = sprintf(
					/* translators: %d: days since expiration */
					__( 'SSL certificate expired %d days ago (site is showing browser warnings)', 'wpshadow' ),
					abs( $cert_info['days_until_expiration'] )
				);
			} elseif ( $cert_info['days_until_expiration'] <= 30 ) {
				$issues[] = sprintf(
					/* translators: %d: days until expiration */
					__( 'SSL certificate expires in %d days (renew immediately)', 'wpshadow' ),
					$cert_info['days_until_expiration']
				);
			} elseif ( $cert_info['days_until_expiration'] <= 60 ) {
				$issues[] = sprintf(
					/* translators: %d: days until expiration */
					__( 'SSL certificate expires in %d days (schedule renewal)', 'wpshadow' ),
					$cert_info['days_until_expiration']
				);
			}

			// Check for self-signed certificate.
			if ( $cert_info['self_signed'] ) {
				$issues[] = __( 'Site uses self-signed certificate (browsers show warnings)', 'wpshadow' );
			}

			// Check Let's Encrypt auto-renewal.
			if ( $cert_info['is_letsencrypt'] && ! self::has_letsencrypt_renewal() ) {
				$issues[] = __( 'Let\'s Encrypt certificate detected but auto-renewal not confirmed', 'wpshadow' );
			}
		}

		if ( ! empty( $issues ) ) {
			$severity = 'high';
			if ( isset( $cert_info['days_until_expiration'] ) && $cert_info['days_until_expiration'] < 0 ) {
				$severity = 'critical';
			}

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( ' ', $issues ),
				'severity'     => $severity,
				'threat_level' => 90,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/ssl-certificate-expiration-monitoring',
				'meta'         => array(
					'days_until_expiration' => $cert_info['days_until_expiration'] ?? null,
					'expiration_date'       => $cert_info['expiration_date'] ?? null,
					'issuer'                => $cert_info['issuer'] ?? null,
					'is_letsencrypt'        => $cert_info['is_letsencrypt'] ?? false,
					'self_signed'           => $cert_info['self_signed'] ?? false,
					'issues_found'          => count( $issues ),
				),
			);
		}

		return null;
	}

	/**
	 * Get SSL certificate information.
	 *
	 * @since  1.26028.1905
	 * @return array Certificate information.
	 */
	private static function get_certificate_info() {
		$info = array(
			'success'               => false,
			'days_until_expiration' => 0,
			'expiration_date'       => '',
			'issuer'                => '',
			'is_letsencrypt'        => false,
			'self_signed'           => false,
		);

		$site_url = get_site_url();
		$parsed_url = wp_parse_url( $site_url );
		$host = isset( $parsed_url['host'] ) ? $parsed_url['host'] : '';

		if ( empty( $host ) ) {
			return $info;
		}

		// Try to get certificate via stream context.
		$context = stream_context_create(
			array(
				'ssl' => array(
					'capture_peer_cert' => true,
					'verify_peer'       => false,
					'verify_peer_name'  => false,
				),
			)
		);

		$stream = @stream_socket_client( // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
			'ssl://' . $host . ':443',
			$errno,
			$errstr,
			30,
			STREAM_CLIENT_CONNECT,
			$context
		);

		if ( ! $stream ) {
			return $info;
		}

		$params = stream_context_get_params( $stream );
		fclose( $stream );

		if ( ! isset( $params['options']['ssl']['peer_certificate'] ) ) {
			return $info;
		}

		$cert_resource = $params['options']['ssl']['peer_certificate'];
		$cert_data = openssl_x509_parse( $cert_resource );

		if ( ! $cert_data ) {
			return $info;
		}

		$info['success'] = true;

		// Get expiration date.
		if ( isset( $cert_data['validTo_time_t'] ) ) {
			$expiration_timestamp = $cert_data['validTo_time_t'];
			$info['expiration_date'] = gmdate( 'Y-m-d H:i:s', $expiration_timestamp );
			$info['days_until_expiration'] = (int) floor( ( $expiration_timestamp - time() ) / DAY_IN_SECONDS );
		}

		// Get issuer.
		if ( isset( $cert_data['issuer']['O'] ) ) {
			$info['issuer'] = $cert_data['issuer']['O'];
			
			// Check if Let's Encrypt.
			if ( false !== stripos( $info['issuer'], 'let\'s encrypt' ) ||
				 false !== stripos( $info['issuer'], 'letsencrypt' ) ) {
				$info['is_letsencrypt'] = true;
			}
		}

		// Check if self-signed.
		if ( isset( $cert_data['issuer'] ) && isset( $cert_data['subject'] ) ) {
			$info['self_signed'] = ( $cert_data['issuer'] === $cert_data['subject'] );
		}

		return $info;
	}

	/**
	 * Check if Let's Encrypt auto-renewal is configured.
	 *
	 * @since  1.26028.1905
	 * @return bool True if auto-renewal detected.
	 */
	private static function has_letsencrypt_renewal() {
		// Check for certbot cron job.
		$cron_jobs = _get_cron_array();
		if ( $cron_jobs ) {
			foreach ( $cron_jobs as $timestamp => $cron ) {
				foreach ( $cron as $hook => $dings ) {
					if ( false !== stripos( $hook, 'certbot' ) ||
						 false !== stripos( $hook, 'letsencrypt' ) ||
						 false !== stripos( $hook, 'ssl_renewal' ) ) {
						return true;
					}
				}
			}
		}

		// Check for common SSL management plugins.
		$ssl_plugins = array(
			'really-simple-ssl/rlrsssl-really-simple-ssl.php',
			'wp-letsencrypt-ssl/wp-letsencrypt.php',
		);

		foreach ( $ssl_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				return true;
			}
		}

		// If hosted on managed hosting, assume they handle it.
		$server_software = isset( $_SERVER['SERVER_SOFTWARE'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_SOFTWARE'] ) ) : '';
		if ( false !== stripos( $server_software, 'kinsta' ) ||
			 false !== stripos( $server_software, 'wpengine' ) ||
			 defined( 'IS_PRESSABLE' ) ||
			 defined( 'GD_SYSTEM_PLUGIN_DIR' ) ) {
			return true;
		}

		return false;
	}
}
