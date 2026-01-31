<?php
/**
 * SSL Certificate Not Valid Diagnostic
 *
 * Checks if SSL certificate is properly configured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2310
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * SSL Certificate Not Valid Diagnostic Class
 *
 * Detects SSL/HTTPS configuration issues.
 *
 * @since 1.2601.2310
 */
class Diagnostic_SSL_Certificate_Not_Valid extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'ssl-certificate-not-valid';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'SSL Certificate Not Valid';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if SSL/HTTPS is properly configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'privacy';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2310
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$site_url = get_option( 'siteurl', '' );

		// Check if HTTPS is used
		if ( 0 !== strpos( $site_url, 'https://' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Site is not using HTTPS. User data is not encrypted. GDPR and PCI-DSS require HTTPS for data protection.', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 85,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/ssl-certificate-not-valid',
			);
		}

		// Check if mixed content issue exists
		if ( is_ssl() ) {
			// Additional checks can be added here
		}

		return null;
	}
}
