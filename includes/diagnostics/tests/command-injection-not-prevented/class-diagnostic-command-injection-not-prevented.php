<?php
/**
 * Command Injection Not Prevented Diagnostic
 *
 * Checks command injection prevention.
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
 * Diagnostic_Command_Injection_Not_Prevented Class
 *
 * Performs diagnostic check for Command Injection Not Prevented.
 *
 * @since 1.26033.2033
 */
class Diagnostic_Command_Injection_Not_Prevented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'command-injection-not-prevented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Command Injection Not Prevented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks command injection prevention';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26033.2033
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if (   !has_filter('init',
						'escape_shell_commands' ) {
						return array(
						'id'   =>   self::$slug,
						'title'   =>   self::$title,
						'description'   =>   __('Command injection not prevented. Use escapeshellarg/escapeshellcmd and avoid system() calls with user input. Use safer alternatives like WordPress hooks.',
						'severity'   =>   'high',
						'threat_level'   =>   80,
						'auto_fixable'   =>   false,
						'kb_link'   =>   'https://wpshadow.com/kb/command-injection-not-prevented'
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
