<?php
/**
 * Password Security Policy Treatment
 *
 * Tests if strong password policies and 2FA are enforced.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Password Security Treatment Class
 *
 * Evaluates password security policies including strength requirements,
 * two-factor authentication, password expiration, and related security measures.
 *
 * @since 0.6093.1200
 */
class Treatment_Password_Security extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'enforces_password_security';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Password Security Policy';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests if strong password policies and 2FA are enforced';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the treatment check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Password_Security' );
	}
}
