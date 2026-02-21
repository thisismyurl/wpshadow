<?php
/**
 * Weak Password Policy Not Enforced Treatment
 *
 * Checks password policy.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6033.2033
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Weak_Password_Policy_Not_Enforced Class
 *
 * Performs treatment check for Weak Password Policy Not Enforced.
 *
 * @since 1.6033.2033
 */
class Treatment_Weak_Password_Policy_Not_Enforced extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'weak-password-policy-not-enforced';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Weak Password Policy Not Enforced';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks password policy';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6033.2033
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Weak_Password_Policy_Not_Enforced' );
	}
						return null;
						}
						return null;
	}
}
