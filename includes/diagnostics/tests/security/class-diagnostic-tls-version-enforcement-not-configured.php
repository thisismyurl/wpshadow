<?php
/**
 * TLS Version Enforcement Not Configured
 *
 * Checks if minimum TLS version is enforced for secure connections,
 * preventing downgrade attacks and weak encryption protocols.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_TLS_Version_Enforcement_Not_Configured Class
 *
 * Detects when servers allow outdated TLS versions (1.0, 1.1) that are
 * vulnerable to POODLE, BEAST, and other downgrade attacks.
 *
 * @since 1.2601.2200
 */
class Diagnostic_TLS_Version_Enforcement_Not_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'tls-version-enforcement-not-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'TLS Version Enforcement Not Configured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies minimum TLS 1.2 is enforced for all HTTPS connections';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Skip if not using HTTPS.
		if ( ! is_ssl() ) {
			return null; // Different diagnostic will catch missing HTTPS.
		}

		// Check for pro security module first.
		if ( Upgrade_Path_Helper::has_pro_product( 'security' ) ) {
			return null;
		}

		// Try to determine TLS configuration.
		$tls_config = self::detect_tls_configuration();

		// If TLS 1.2+ is enforced, return null.
		if ( ! empty( $tls_config['enforced'] ) && $tls_config['min_version'] >= 1.2 ) {
			return null;
		}

		// Check if user has manually configured TLS enforcement.
		$manual_tls = get_option( 'wpshadow_tls_enforcement_configured', false );
		if ( $manual_tls ) {
			return null;
		}

		// TLS enforcement not detected.
		$finding = array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __(
				'Your server may allow outdated TLS versions (1.0, 1.1) that are vulnerable to downgrade attacks. TLS 1.0 and 1.1 are officially deprecated (RFC 8996, 2021) and vulnerable to: POODLE attacks (padding oracle), BEAST attacks (cipher block chaining), weak ciphers (RC4, 3DES). Major browsers now block TLS 1.0/1.1 by default. PCI DSS compliance requires TLS 1.2+ for payment data. Modern TLS 1.3 provides: perfect forward secrecy (ephemeral keys), reduced handshake latency (faster connections), stronger cipher suites (AES-GCM, ChaCha20). Enforcing TLS 1.2+ prevents man-in-the-middle attacks that steal login credentials and customer data.',
				'wpshadow'
			),
			'severity'     => 'high',
			'threat_level' => 70,
			'auto_fixable' => false,
			'kb_link'      => 'tls-version-enforcement-setup',
			'details'      => $tls_config,
		);

		// Add upgrade path for WPShadow Pro Security (when available).
		$finding = Upgrade_Path_Helper::add_upgrade_path(
			$finding,
			'security',
			'tls-enforcement',
			'tls-manual-configuration'
		);

		return $finding;
	}

	/**
	 * Detect TLS configuration from server environment.
	 *
	 * @since  1.2601.2200
	 * @return array TLS configuration details.
	 */
	private static function detect_tls_configuration() {
		$config = array(
			'enforced'    => false,
			'min_version' => null,
			'detected_at' => null,
		);

		// Check Apache configuration.
		if ( function_exists( 'apache_get_modules' ) && in_array( 'mod_ssl', apache_get_modules(), true ) ) {
			// Check for SSLProtocol directive (requires file access).
			$apache_configs = array(
				'/etc/apache2/apache2.conf',
				'/etc/httpd/conf/httpd.conf',
				'/usr/local/apache2/conf/httpd.conf',
			);

			foreach ( $apache_configs as $config_file ) {
				if ( file_exists( $config_file ) && is_readable( $config_file ) ) {
					$contents = file_get_contents( $config_file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
					if ( preg_match( '/SSLProtocol\s+(-all\s+)?\+?TLSv1\.([23])/i', $contents, $matches ) ) {
						$config['enforced']     = true;
						$config['min_version']  = 1.2;
						$config['detected_at']  = 'apache_config';
						$config['config_value'] = $matches[0];
						break;
					}
				}
			}
		}

		// Check nginx configuration.
		$nginx_configs = array(
			'/etc/nginx/nginx.conf',
			'/usr/local/nginx/conf/nginx.conf',
		);

		foreach ( $nginx_configs as $config_file ) {
			if ( file_exists( $config_file ) && is_readable( $config_file ) ) {
				$contents = file_get_contents( $config_file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
				if ( preg_match( '/ssl_protocols\s+TLSv1\.([23])/i', $contents, $matches ) ) {
					$config['enforced']     = true;
					$config['min_version']  = 1.2;
					$config['detected_at']  = 'nginx_config';
					$config['config_value'] = $matches[0];
					break;
				}
			}
		}

		// Check server variables.
		if ( isset( $_SERVER['SSL_PROTOCOL'] ) ) {
			$protocol = sanitize_text_field( wp_unslash( $_SERVER['SSL_PROTOCOL'] ) );
			if ( preg_match( '/TLSv1\.([0-9])/', $protocol, $matches ) ) {
				$version                = 1 + ( intval( $matches[1] ) / 10 );
				$config['min_version']  = $version;
				$config['detected_at']  = 'server_variable';
				$config['config_value'] = $protocol;
			}
		}

		return $config;
	}
}
