<?php
/**
 * User Role Segregation Diagnostic
 *
 * Ensures roles are separated (not all users are admins).
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_User_Role_Segregation Class
 *
 * Checks role distribution for segregation of duties.
 *
 * @since 1.6093.1200
 */
class Diagnostic_User_Role_Segregation extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'user-role-segregation';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'User Role Segregation';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for segregation of duties in user roles';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'compliance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$counts = count_users();
		$total = (int) $counts['total_users'];
		$admins = isset( $counts['avail_roles']['administrator'] ) ? (int) $counts['avail_roles']['administrator'] : 0;

		if ( $total > 0 && $admins === $total ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'All users are administrators. Assign least-privilege roles to reduce risk.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 80,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/user-role-segregation',
				'meta'         => array(
					'admins' => $admins,
					'total'  => $total,
				),
			);
		}

		return null;
	}
}