<?php
/**
 * Orphaned User Meta Cleanup Diagnostic
 *
 * Checks for user meta entries referencing deleted users.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.5049.1401
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Orphaned User Meta Cleanup Diagnostic Class
 *
 * Detects user meta entries orphaned by deleted users.
 *
 * @since 1.5049.1401
 */
class Diagnostic_Orphaned_User_Meta_Cleanup extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'orphaned-user-meta-cleanup';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Orphaned User Meta Cleanup';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for user metadata from deleted users';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.5049.1401
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		$orphaned = (int) $wpdb->get_var(
			"SELECT COUNT(1) FROM {$wpdb->usermeta} um
			LEFT JOIN {$wpdb->users} u ON um.user_id = u.ID
			WHERE u.ID IS NULL"
		);

		if ( $orphaned >= 50 ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Orphaned user metadata from deleted users was found. Cleaning it up can improve performance.', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 25,
				'auto_fixable' => false,
				'details'      => array(
					'orphaned_count' => $orphaned,
				),
				'kb_link'      => 'https://wpshadow.com/kb/orphaned-user-meta-cleanup',
			);
		}

		return null;
	}
}
