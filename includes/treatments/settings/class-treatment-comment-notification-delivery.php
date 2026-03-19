<?php
/**
 * Comment Notification Delivery Treatment
 *
 * Verifies comment notification emails are being delivered to users.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Comment Notification Delivery Treatment Class
 *
 * Checks comment notification email delivery to users.
 *
 * @since 1.6093.1200
 */
class Treatment_Comment_Notification_Delivery extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'comment-notification-delivery';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Comment Notification Delivery';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies comment notification email delivery to users';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'comments';

	/**
	 * Run the treatment check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Comment_Notification_Delivery' );
	}
}
