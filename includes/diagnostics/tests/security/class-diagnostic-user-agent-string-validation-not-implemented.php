<?php
/**
 * User Agent String Validation Not Implemented Diagnostic
 *
 * Checks if user agent validation is implemented.
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
 * User Agent String Validation Not Implemented Diagnostic Class
 *
 * Detects missing user agent validation.
 *
 * @since 1.2601.2352
 */
class Diagnostic_User_Agent_String_Validation_Not_Implemented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'user-agent-string-validation-not-implemented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'User Agent String Validation Not Implemented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if user agent validation is implemented';

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
		// Check for user agent validation
		if ( ! has_filter( 'init', 'validate_user_agent' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'User agent string validation is not implemented. Detect and block suspicious user agents associated with known exploit tools and malicious bots.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 20,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/user-agent-string-validation-not-implemented',
			);
		}

		return null;
	}
}
