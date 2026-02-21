<?php
/**
 * Failed Login Attempts Treatment
 *
 * Detects unusual failed login activity or missing login monitoring.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6035.1335
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Failed_Login_Attempts Class
 *
 * Checks for excessive failed login attempts or lack of monitoring.
 *
 * @since 1.6035.1335
 */
class Treatment_Failed_Login_Attempts extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'failed-login-attempts';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Failed Login Attempts';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for excessive failed login attempts';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6035.1335
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Failed_Login_Attempts' );
	}
}