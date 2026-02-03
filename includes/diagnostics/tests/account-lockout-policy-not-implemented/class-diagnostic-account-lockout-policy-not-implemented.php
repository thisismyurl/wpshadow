<?php
/**
 * Account Lockout Policy Not Implemented Diagnostic
 *
 * Checks account lockout.
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
 * Diagnostic_Account_Lockout_Policy_Not_Implemented Class
 *
 * Performs diagnostic check for Account Lockout Policy Not Implemented.
 *
 * @since 1.26033.2033
 */
class Diagnostic_Account_Lockout_Policy_Not_Implemented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'account-lockout-policy-not-implemented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Account Lockout Policy Not Implemented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks account lockout';

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
		if (   !get_option('account_lockout_enabled' ) {
						return array(
						'id'   =>   self::$slug,
						'title'   =>   self::$title,
						'description'   =>   __('Account lockout policy not implemented. Lock accounts after N failed login attempts to prevent brute force attacks.',
						'severity'   =>   'high',
						'threat_level'   =>   75,
						'auto_fixable'   =>   true,
						'kb_link'   =>   'https://wpshadow.com/kb/account-lockout-policy-not-implemented'
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
