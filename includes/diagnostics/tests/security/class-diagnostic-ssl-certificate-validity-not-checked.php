<?php
/**
 * SSL Certificate Validity Not Checked Diagnostic
 *
 * Checks if SSL certificate validity is monitored.
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
 * SSL Certificate Validity Not Checked Diagnostic Class
 *
 * Detects unchecked SSL certificate validity.
 *
 * @since 1.2601.2352
 */
class Diagnostic_SSL_Certificate_Validity_Not_Checked extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'ssl-certificate-validity-not-checked';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'SSL Certificate Validity Not Checked';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if SSL certificate validity is monitored';

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
		// Check if site is using HTTPS
		if ( 'https' !== parse_url( home_url(), PHP_URL_SCHEME ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'SSL certificate validity is not checked. Ensure your SSL certificate is valid and set up certificate expiration alerts to prevent security issues.', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 75,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/ssl-certificate-validity-not-checked',
			);
		}

		return null;
	}
}
