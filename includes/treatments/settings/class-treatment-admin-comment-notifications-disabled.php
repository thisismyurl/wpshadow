<?php
/**
 * Admin Comment Notifications Disabled Treatment
 *
 * Checks if admin comment notifications are disabled.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.5049.1331
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin Comment Notifications Disabled Treatment Class
 *
 * Detects disabled admin comment notifications.
 *
 * @since 1.5049.1331
 */
class Treatment_Admin_Comment_Notifications_Disabled extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'admin-comment-notifications-disabled';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Admin Comment Notifications Disabled';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if admin comment notifications are disabled';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.5049.1331
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\\WPShadow\\Diagnostics\\Diagnostic_Admin_Comment_Notifications_Disabled' );
	}
}
