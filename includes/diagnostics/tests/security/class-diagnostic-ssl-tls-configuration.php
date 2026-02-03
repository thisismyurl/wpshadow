<?php
/**
 * SSL/TLS Configuration Diagnostic
 *
 * Analyzes SSL certificate and HTTPS configuration.
 *
 * @since   1.26033.2145
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * SSL/TLS Configuration Diagnostic
 *
 * Evaluates SSL certificate validity and security configuration.
 *
 * @since 1.26033.2145
 */
class Diagnostic_SSL_TLS_Configuration extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'ssl-tls-configuration';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'SSL/TLS Configuration';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Analyzes SSL certificate and HTTPS configuration';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26033.2145
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if site uses HTTPS
		$is_ssl = is_ssl();
		$site_url = get_option( 'siteurl' );
		$home_url = get_option( 'home' );
		$uses_https = strpos( $site_url, 'https://' ) === 0 && strpos( $home_url, 'https://' ) === 0;

		// Check for SSL forcing plugins
		$ssl_plugins = array(
			'really-simple-ssl/rlrsssl-really-simple-ssl.php' => 'Really Simple SSL',
			'wp-force-ssl/wp-force-ssl.php'                   => 'WP Force SSL',
		);

		$active_ssl_plugin = null;
		foreach ( $ssl_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$active_ssl_plugin = $name;
				break;
			}
		}

		// Check HSTS header
		$has_hsts = false;
		if ( function_exists( 'apache_response_headers' ) ) {
			$headers = apache_response_headers();
			$has_hsts = isset( $headers['Strict-Transport-Security'] );
		}

		// Generate findings if not using HTTPS
		if ( ! $uses_https ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Site not configured for HTTPS. Modern browsers mark HTTP sites as "Not Secure", damaging user trust.', 'wpshadow' ),
				'severity'     => 'critical',
				'threat_level' => 85,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/ssl-tls-configuration',
				'meta'         => array(
					'is_ssl'            => $is_ssl,
					'uses_https'        => $uses_https,
					'site_url'          => $site_url,
					'home_url'          => $home_url,
					'recommendation'    => 'Install SSL certificate and migrate to HTTPS',
					'free_ssl'          => 'Let\'s Encrypt provides free SSL certificates',
					'migration_steps'   => array(
						'1. Obtain SSL certificate',
						'2. Install Really Simple SSL plugin',
						'3. Update site URLs to HTTPS',
						'4. Fix mixed content warnings',
						'5. Redirect HTTP to HTTPS',
					),
					'seo_impact'        => 'Google prioritizes HTTPS sites in search rankings',
				),
			);
		}

		// Check if HSTS is configured
		if ( $uses_https && ! $has_hsts ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'HSTS (HTTP Strict Transport Security) not configured. Enable HSTS to prevent SSL stripping attacks.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/ssl-tls-configuration',
				'meta'         => array(
					'has_hsts'       => $has_hsts,
					'recommendation' => 'Add HSTS header via .htaccess or security plugin',
					'htaccess_code'  => 'Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"',
					'benefit'        => 'Prevents man-in-the-middle attacks and SSL stripping',
				),
			);
		}

		return null;
	}
}
