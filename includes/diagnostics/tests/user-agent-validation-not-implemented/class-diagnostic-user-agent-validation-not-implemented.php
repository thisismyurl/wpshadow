<?php
/**
 * User Agent Validation Not Implemented Diagnostic
 *
 * Checks user agent validation.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26033.2033
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_User_Agent_Validation_Not_Implemented Class
 *
 * Performs diagnostic check for User Agent Validation Not Implemented.
 *
 * @since 1.26033.2033
 */
class Diagnostic_User_Agent_Validation_Not_Implemented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'user-agent-validation-not-implemented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'User Agent Validation Not Implemented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks user agent validation';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26033.2033
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if (   !has_filter('init',
						'validate_user_agent' ) {
						return array(
						'id'   =>   self::$slug,
						'title'   =>   self::$title,
						'description'   =>   __('User agent validation not implemented. Detect spoofed user agents and suspicious browser fingerprints.',
						'severity'   =>   'low',
						'threat_level'   =>   20,
						'auto_fixable'   =>   false,
						'kb_link'   =>   'https://wpshadow.com/kb/user-agent-validation-not-implemented'
						);
						);,
						);
						}
						return null;
						}
						return null;
						}
						return null;
	}
}
