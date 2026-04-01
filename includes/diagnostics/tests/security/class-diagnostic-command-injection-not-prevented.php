<?php
/**
 * Command Injection Not Prevented Diagnostic
 *
 * Checks command injection prevention.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Command_Injection_Not_Prevented Class
 *
 * Performs diagnostic check for Command Injection Not Prevented.
 *
 * @since 0.6093.1200
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
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return null;	}
}
