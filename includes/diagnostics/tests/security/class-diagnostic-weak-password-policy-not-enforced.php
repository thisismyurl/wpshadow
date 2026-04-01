<?php
/**
 * Weak Password Policy Not Enforced Diagnostic
 *
 * Checks password policy.
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
 * Diagnostic_Weak_Password_Policy_Not_Enforced Class
 *
 * Performs diagnostic check for Weak Password Policy Not Enforced.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Weak_Password_Policy_Not_Enforced extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'weak-password-policy-not-enforced';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Weak Password Policy Not Enforced';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks password policy';

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
		if ( ! get_option( 'strong_password_policy_enabled' ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Weak password policy not enforced. Require stronger passwords and enforce complexity to reduce account takeover risk.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/weak-password-policy-not-enforced?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
