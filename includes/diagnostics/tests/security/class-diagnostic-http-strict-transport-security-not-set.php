<?php
/**
 * HTTP Strict Transport Security Not Set Diagnostic
 *
 * Checks HSTS.
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
 * Diagnostic_HTTP_Strict_Transport_Security_Not_Set Class
 *
 * Performs diagnostic check for Http Strict Transport Security Not Set.
 *
 * @since 0.6093.1200
 */
class Diagnostic_HTTP_Strict_Transport_Security_Not_Set extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'http-strict-transport-security-not-set';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'HTTP Strict Transport Security Not Set';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks HSTS';

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
