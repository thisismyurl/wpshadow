<?php
/**
 * Regex DoS Attack Not Prevented Diagnostic
 *
 * Checks ReDoS prevention.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6033.2033
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Regex_DoS_Attack_Not_Prevented Class
 *
 * Performs diagnostic check for Regex Dos Attack Not Prevented.
 *
 * @since 1.6033.2033
 */
class Diagnostic_Regex_DoS_Attack_Not_Prevented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'regex-dos-attack-not-prevented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Regex DoS Attack Not Prevented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks ReDoS prevention';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6033.2033
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if (   !has_filter('init',
						'validate_regex_patterns' ) {
						return array(
						'id'   =>   self::$slug,
						'title'   =>   self::$title,
						'description'   =>   __('Regex DoS attack not prevented. Avoid complex nested quantifiers and use timeouts on regex operations.',
						'severity'   =>   'high',
						'threat_level'   =>   55,
						'auto_fixable'   =>   false,
						'kb_link'   =>   'https://wpshadow.com/kb/regex-dos-attack-not-prevented'
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
