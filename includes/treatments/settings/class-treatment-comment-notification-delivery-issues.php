<?php
/**
 * Comment Notification Delivery Issues Treatment
 *
 * Checks for missing configuration that can affect comment email delivery.
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
 * Comment Notification Delivery Issues Treatment Class
 *
 * Detects potential mail delivery configuration issues.
 *
 * @since 1.6093.1200
 */
class Treatment_Comment_Notification_Delivery_Issues extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'comment-notification-delivery-issues';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Comment Notification Delivery Issues';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for potential delivery issues with comment notifications';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the treatment check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Comment_Notification_Delivery_Issues' );
	}
}
