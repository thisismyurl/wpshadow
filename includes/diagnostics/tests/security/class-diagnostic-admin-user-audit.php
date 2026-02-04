<?php
/**
 * Admin User Audit Diagnostic
 *
 * Identifies potentially risky administrator accounts.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6035.1475
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Admin_User_Audit Class
 *
 * Flags administrator accounts with risky usernames.
 *
 * @since 1.6035.1475
 */
class Diagnostic_Admin_User_Audit extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'admin-user-audit';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Admin User Audit';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for risky administrator accounts';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6035.1475
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$admins = get_users( array( 'role' => 'administrator', 'fields' => array( 'user_login' ) ) );
		if ( empty( $admins ) ) {
			return null;
		}

		$risky = array( 'admin', 'root', 'test', 'user' );
		$found = array();

		foreach ( $admins as $admin ) {
			if ( in_array( strtolower( $admin->user_login ), $risky, true ) ) {
				$found[] = $admin->user_login;
			}
		}

		if ( ! empty( $found ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'One or more administrator accounts use common usernames. Rename or remove them.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 80,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/admin-user-audit',
				'meta'         => array(
					'risky_admins' => $found,
				),
			);
		}

		return null;
	}
}