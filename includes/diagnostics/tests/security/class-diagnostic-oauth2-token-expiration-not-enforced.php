<?php
/**
 * OAuth2 Token Expiration Not Enforced Diagnostic
 *
 * Checks if OAuth2 token expiration is enforced.
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
 * OAuth2 Token Expiration Not Enforced Diagnostic Class
 *
 * Detects missing OAuth2 token expiration.
 *
 * @since 1.2601.2352
 */
class Diagnostic_OAuth2_Token_Expiration_Not_Enforced extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'oauth2-token-expiration-not-enforced';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'OAuth2 Token Expiration Not Enforced';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if OAuth2 token expiration is enforced';

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
		// Check if OAuth2 tokens have expiration
		if ( ! has_filter( 'validate_oauth_token', 'check_token_expiration' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'OAuth2 token expiration is not enforced. Set token expiration to 1 hour and implement refresh tokens for enhanced security.', 'wpshadow' ),
				'severity'      => 'high',
				'threat_level'  => 70,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/oauth2-token-expiration-not-enforced',
			);
		}

		return null;
	}
}
