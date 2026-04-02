<?php
/**
 * Access Control List Diagnostic
 *
 * Checks user role distribution to ensure least-privilege access.
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
 * Diagnostic_Access_Control_List Class
 *
 * Evaluates role distribution for access control hygiene.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Access_Control_List extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'access-control-list';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Access Control List';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks role distribution for least-privilege access';

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

		if ( $total > 0 && ( $admins / $total ) > 0.5 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'More than half of users are administrators. Apply least-privilege access.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/access-control-list',
				'meta'         => array(
					'admins' => $admins,
					'total'  => $total,
				),
			);
		}

		return null;
	}
}