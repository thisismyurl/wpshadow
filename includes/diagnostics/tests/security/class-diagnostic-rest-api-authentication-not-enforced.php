<?php
/**
 * REST API Authentication Not Enforced Diagnostic
 *
 * Checks if REST API authentication is enforced.
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
 * REST API Authentication Not Enforced Diagnostic Class
 *
 * Detects unauthenticated REST API access.
 *
 * @since 1.2601.2352
 */
class Diagnostic_REST_API_Authentication_Not_Enforced extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'rest-api-authentication-not-enforced';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'REST API Authentication Not Enforced';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if REST API authentication is enforced';

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
		// Check if REST API auth is enforced
		if ( ! has_filter( 'rest_authentication_errors', 'enforce_rest_auth' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'REST API authentication is not enforced. Disable anonymous REST API access or require authentication to prevent data leakage.', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 65,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/rest-api-authentication-not-enforced',
			);
		}

		return null;
	}
}
