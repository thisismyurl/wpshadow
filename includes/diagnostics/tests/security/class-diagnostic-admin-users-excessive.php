<?php
/**
 * Admin Users Excessive Diagnostic
 *
 * Flags when there are unusually many administrator accounts.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Admin_Users_Excessive Class
 *
 * Checks the count of administrator users and flags potential risk.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Admin_Users_Excessive extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'admin-users-excessive';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Admin Users Excessive';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for an unusually high number of administrator accounts';

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
		$admins = get_users( array( 'role' => 'administrator', 'fields' => array( 'ID' ) ) );
		$count  = is_array( $admins ) ? count( $admins ) : 0;

		if ( $count >= 10 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'There are 10 or more administrator accounts. Reduce admin access to minimize security risk.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/admin-users-excessive?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'meta'         => array(
					'admin_count' => $count,
				),
			);
		}

		if ( $count >= 5 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'There are several administrator accounts. Review and remove unused admin access.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/admin-users-excessive?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'meta'         => array(
					'admin_count' => $count,
				),
			);
		}

		return null;
	}
}