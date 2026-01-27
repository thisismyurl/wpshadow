<?php
/**
 * Diagnostic: HTTPS/SSL Configuration
 *
 * Checks if the site is properly configured to use HTTPS.
 * Unencrypted HTTP transmits passwords, session cookies, and personal data in plain text.
 *
 * Philosophy: Privacy-first (#10), protect users, inspire confidence (#8)
 * KB Link: https://wpshadow.com/kb/security-https-enabled
 * Training: https://wpshadow.com/training/security-https-enabled
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
 * HTTPS Enabled Diagnostic Class
 *
 * Validates SSL/HTTPS configuration and mixed content issues.
 */
class Diagnostic_HTTPS_Enabled extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'https-enabled';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'HTTPS Not Properly Configured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Your site is not using HTTPS, exposing sensitive data during transmission.';

	/**
	 * Run the diagnostic check
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issues found, null otherwise.
	 */
	public static function check() {
		$issues       = array();
		$threat_level = 0;

		// Check 1: Is site URL using HTTPS?
		$site_url = get_option( 'siteurl' );
		$home_url = get_option( 'home' );

		$site_uses_https = ( strpos( $site_url, 'https://' ) === 0 );
		$home_uses_https = ( strpos( $home_url, 'https://' ) === 0 );

		if ( ! $site_uses_https || ! $home_uses_https ) {
			$issues[] = __( 'WordPress is not configured to use HTTPS', 'wpshadow' );
			$threat_level += 60;
		}

		// Check 2: Is current request HTTPS?
		if ( ! is_ssl() ) {
			$issues[] = __( 'Current page is not served over HTTPS', 'wpshadow' );
			$threat_level += 25;
		}

		// Check 3: FORCE_SSL_ADMIN not set
		if ( ! defined( 'FORCE_SSL_ADMIN' ) || ! FORCE_SSL_ADMIN ) {
			$issues[] = __( 'Admin area is not forced to use HTTPS', 'wpshadow' );
			$threat_level += 10;
		}

		// If no issues found
		if ( empty( $issues ) ) {
			return null;
		}

		$message = sprintf(
			/* translators: 1: list of HTTPS issues */
			__( 'Your site has HTTPS configuration problems: %s. Without HTTPS, passwords, personal data, and session cookies are transmitted in plain text and can be intercepted by attackers.', 'wpshadow' ),
			implode( '; ', $issues )
		);

		// Additional context for mixed content
		$additional_info = '';
		if ( $site_uses_https ) {
			$additional_info = ' ' . __( 'Note: Even though HTTPS is configured, some resources may still load over HTTP (mixed content). After fixing, we recommend scanning for mixed content issues.', 'wpshadow' );
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => $message . $additional_info,
			'severity'    => 'critical',
			'threat_level' => min( $threat_level, 100 ),
			'auto_fixable' => false, // Requires SSL certificate installation
			'kb_link'     => 'https://wpshadow.com/kb/security-https-enabled',
			'training_link' => 'https://wpshadow.com/training/security-https-enabled',
			'manual_steps' => array(
				__( 'Install an SSL certificate on your server (Let\'s Encrypt is free)', 'wpshadow' ),
				__( 'Update WordPress Site URL to use https:// in Settings > General', 'wpshadow' ),
				__( 'Update Home URL to use https:// in Settings > General', 'wpshadow' ),
				__( 'Add "define(\'FORCE_SSL_ADMIN\', true);" to wp-config.php', 'wpshadow' ),
				__( 'Set up 301 redirects from HTTP to HTTPS', 'wpshadow' ),
				__( 'Scan for and fix mixed content issues', 'wpshadow' ),
			),
			'impact'      => array(
				'security' => __( 'Passwords and sensitive data transmitted in plain text', 'wpshadow' ),
				'privacy'  => __( 'User browsing activity can be monitored by ISPs and attackers', 'wpshadow' ),
				'seo'      => __( 'Google prioritizes HTTPS sites in search rankings', 'wpshadow' ),
				'trust'    => __( 'Modern browsers show "Not Secure" warnings', 'wpshadow' ),
			),
			'evidence'    => array(
				'site_url'        => $site_url,
				'home_url'        => $home_url,
				'current_is_ssl'  => is_ssl() ? 'yes' : 'no',
				'force_ssl_admin' => defined( 'FORCE_SSL_ADMIN' ) && FORCE_SSL_ADMIN ? 'yes' : 'no',
			),
		);
	}
}
