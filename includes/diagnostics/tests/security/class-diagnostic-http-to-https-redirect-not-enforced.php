<?php
/**
 * HTTP To HTTPS Redirect Not Enforced Diagnostic
 *
 * Checks if HTTP to HTTPS redirect is enforced.
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
 * HTTP To HTTPS Redirect Not Enforced Diagnostic Class
 *
 * Detects missing HTTP redirect.
 *
 * @since 1.2601.2352
 */
class Diagnostic_HTTP_To_HTTPS_Redirect_Not_Enforced extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'http-to-https-redirect-not-enforced';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'HTTP To HTTPS Redirect Not Enforced';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if HTTP to HTTPS redirect is enforced';

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
		// Check if site is HTTPS
		if ( 'https' !== parse_url( home_url(), PHP_URL_SCHEME ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'HTTP to HTTPS redirect is not enforced. Add 301 redirects from HTTP to HTTPS in .htaccess or server configuration.', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 75,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/http-to-https-redirect-not-enforced',
			);
		}

		return null;
	}
}
