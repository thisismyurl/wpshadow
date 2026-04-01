<?php
/**
 * HSTS Headers Diagnostic
 *
 * Checks if HTTP Strict Transport Security headers are configured.
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
 * HSTS Headers Diagnostic Class
 *
 * Verifies HTTP Strict Transport Security is enabled.
 * Like telling browsers to always use the secure entrance.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Hsts_Headers extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'hsts-headers';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'HTTP Strict Transport Security (HSTS)';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if HTTP Strict Transport Security headers are configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'ssl';

	/**
	 * Run the HSTS headers diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if HSTS issues detected, null otherwise.
	 */
	public static function check() {
		// Only relevant if site uses HTTPS.
		if ( ! is_ssl() ) {
			return null; // Not using SSL (separate diagnostic).
		}

		// Check if HSTS header is being sent.
		$hsts_enabled = false;
		$hsts_header = '';
		$hsts_max_age = 0;
		$hsts_subdomains = false;
		$hsts_preload = false;

		// Check headers_list() for HSTS (if available during diagnostic run).
		if ( function_exists( 'headers_list' ) ) {
			$headers = headers_list();
			foreach ( $headers as $header ) {
				if ( false !== stripos( $header, 'strict-transport-security' ) ) {
					$hsts_enabled = true;
					$hsts_header = $header;

					// Parse max-age.
					if ( preg_match( '/max-age=(\d+)/', $header, $matches ) ) {
						$hsts_max_age = (int) $matches[1];
					}

					// Check for includeSubDomains.
					if ( false !== stripos( $header, 'includeSubDomains' ) ) {
						$hsts_subdomains = true;
					}

					// Check for preload.
					if ( false !== stripos( $header, 'preload' ) ) {
						$hsts_preload = true;
					}

					break;
				}
			}
		}

		// Alternative: Check if header is defined via PHP or .htaccess.
		if ( ! $hsts_enabled ) {
			// Check for send_frame_options_header filter (similar pattern).
			$hsts_filter_exists = has_filter( 'wp_headers', 'add_hsts_header' );
			if ( $hsts_filter_exists ) {
				$hsts_enabled = true;
			}
		}

		// Check common security plugins that add HSTS.
		$security_plugins_with_hsts = array(
			'Really Simple SSL'    => class_exists( 'rsssl_admin' ),
			'iThemes Security'     => class_exists( 'ITSEC_Core' ),
			'Wordfence'            => class_exists( 'wordfence' ),
			'All In One WP Security' => class_exists( 'AIO_WP_Security' ),
		);

		$has_security_plugin = false;
		$security_plugin_name = '';
		foreach ( $security_plugins_with_hsts as $plugin => $active ) {
			if ( $active ) {
				$has_security_plugin = true;
				$security_plugin_name = $plugin;
				break;
			}
		}

		// If HSTS not enabled.
		if ( ! $hsts_enabled ) {
			$severity = $has_security_plugin ? 'low' : 'medium';
			$threat_level = $has_security_plugin ? 30 : 50;

			$description = __( 'Adding HSTS (HTTP Strict Transport Security) tells browsers to always use the secure connection (like putting up a sign that says "always use the front entrance, never the back door"). This prevents attackers from downgrading your connection to insecure HTTP. ', 'wpshadow' );

			if ( $has_security_plugin ) {
				$description .= sprintf(
					/* translators: %s: security plugin name */
					__( 'You have %s installed—check if it has an HSTS option you can enable. ', 'wpshadow' ),
					$security_plugin_name
				);
			} else {
				$description .= __( 'You can enable this through your hosting provider, a security plugin, or by adding a header to your .htaccess file.', 'wpshadow' );
			}

			return array(
				'id'           => self::$slug . '-not-enabled',
				'title'        => __( 'HSTS Not Enabled', 'wpshadow' ),
				'description'  => $description,
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/hsts?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'context'      => array(
					'security_plugin' => $security_plugin_name,
				),
			);
		}

		// Check if max-age is too short.
		if ( $hsts_max_age > 0 && $hsts_max_age < 31536000 ) {
			return array(
				'id'           => self::$slug . '-short-duration',
				'title'        => __( 'HSTS Duration Too Short', 'wpshadow' ),
				'description'  => sprintf(
					/* translators: 1: current max-age in days, 2: recommended duration */
					__( 'Your HSTS policy expires in %1$d days (like a temporary security rule). The recommended duration is at least %2$d days (1 year) to provide long-term protection. Increase the max-age value in your HSTS header configuration.', 'wpshadow' ),
					(int) ( $hsts_max_age / 86400 ),
					365
				),
				'severity'     => 'low',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/hsts?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'context'      => array(
					'max_age'         => $hsts_max_age,
					'max_age_days'    => (int) ( $hsts_max_age / 86400 ),
					'subdomains'      => $hsts_subdomains,
					'preload'         => $hsts_preload,
				),
			);
		}

		// Check if includeSubDomains is missing (only warn, not critical).
		if ( ! $hsts_subdomains ) {
			return array(
				'id'           => self::$slug . '-no-subdomains',
				'title'        => __( 'HSTS Not Protecting Subdomains', 'wpshadow' ),
				'description'  => __( 'Your HSTS policy doesn\'t cover subdomains (like securing the main building but not the attached garage). If you use subdomains (like shop.yoursite.com), add "includeSubDomains" to your HSTS header for complete protection.', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 20,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/hsts?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'context'      => array(
					'max_age'    => $hsts_max_age,
					'subdomains' => $hsts_subdomains,
					'preload'    => $hsts_preload,
				),
			);
		}

		return null; // HSTS properly configured.
	}
}
