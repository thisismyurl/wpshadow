<?php
/**
 * No Admin User Limit Diagnostic
 *
 * Detects when multiple admin users exist unnecessarily,
 * creating privilege escalation and compromised account risks.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Security
 * @since      1.6035.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic: No Admin User Limit
 *
 * Checks whether admin user count is minimized
 * to reduce privilege escalation risk.
 *
 * @since 1.6035.2148
 */
class Diagnostic_No_Admin_User_Limit extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-admin-user-limit';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Admin User Limit';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether admin count is minimized';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Whether this diagnostic is auto-fixable
	 *
	 * @var bool
	 */
	protected static $auto_fixable = false;

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6035.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Count admin users
		$admin_users = count_users();
		$admin_count = $admin_users['avail_roles']['administrator'] ?? 0;

		if ( $admin_count > 2 ) {
			$admin_list = get_users( array(
				'role' => 'administrator',
				'fields' => 'ID',
			) );

			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => sprintf(
					__(
						'You have %d admin users, which is excessive. Best practice: 1-2 admins (you + backup). Each admin account is a potential target. If one account is compromised, attacker gets full site access. Review admin list, remove unnecessary accounts, demote to Editor where possible. Only keep admins for people actively managing site. Contractors, consultants should have lower permissions.',
						'wpshadow'
					),
					$admin_count
				),
				'severity'      => 'high',
				'threat_level'  => 65,
				'auto_fixable'  => false,
				'admin_count'   => $admin_count,
				'business_impact' => array(
					'metric'         => 'Privilege Escalation Risk',
					'potential_gain' => 'Reduce number of accounts with full site access',
					'roi_explanation' => 'Limiting admin accounts reduces targets for attackers and minimizes damage from compromised accounts.',
				),
				'kb_link'       => 'https://wpshadow.com/kb/admin-user-limit',
			);
		}

		return null;
	}
}
