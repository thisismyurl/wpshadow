<?php
/**
 * SSL Certificate Renewal Notification Not Configured Diagnostic
 *
 * Checks if SSL renewal notifications are set.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2352
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * SSL Certificate Renewal Notification Not Configured Diagnostic Class
 *
 * Detects missing SSL renewal alerts.
 *
 * @since 1.2601.2352
 */
class Diagnostic_SSL_Certificate_Renewal_Notification_Not_Configured extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'ssl-certificate-renewal-notification-not-configured';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'SSL Certificate Renewal Notification Not Configured';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if SSL renewal notifications are configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check if is HTTPS
		if ( ! is_ssl() ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'SSL certificate is not configured. Install an SSL certificate and set up renewal notifications to avoid expiration.', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 75,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/ssl-certificate-renewal-notification-not-configured',
			);
		}

		return null;
	}
}
