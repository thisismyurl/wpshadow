<?php
/**
 * HTTP to HTTPS Redirect Diagnostic
 *
 * Issue #4931: No HTTP to HTTPS Redirect Configured
 * Pillar: 🛡️ Safe by Default
 *
 * Checks if HTTP traffic redirects to HTTPS.
 * Mixed content warnings and security issues without HTTPS enforcement.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6050.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_HTTP_HTTPS_Redirect Class
 *
 * @since 1.6050.0000
 */
class Diagnostic_HTTP_HTTPS_Redirect extends Diagnostic_Base {

	protected static $slug = 'http-https-redirect';
	protected static $title = 'No HTTP to HTTPS Redirect Configured';
	protected static $description = 'Checks if HTTP traffic is redirected to HTTPS';
	protected static $family = 'security';

	public static function check() {
		// Check if site URL uses HTTPS
		$site_url = get_option( 'siteurl' );
		$uses_https = strpos( $site_url, 'https://' ) === 0;

		if ( ! $uses_https ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Your site is not using HTTPS. All traffic should be encrypted with SSL/TLS certificates to protect user data.', 'wpshadow' ),
				'severity'     => 'critical',
				'threat_level' => 85,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/https-redirect',
				'details'      => array(
					'current_url'             => $site_url,
					'recommendation'          => 'Install SSL certificate (free via Let\'s Encrypt)',
					'htaccess_redirect'       => 'RewriteCond %{HTTPS} off\nRewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]',
					'seo_benefit'             => 'Google ranks HTTPS sites higher',
				),
			);
		}

		return null;
	}
}
