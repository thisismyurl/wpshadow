<?php
/**
 * Comment Notification Email Content Issues Treatment
 *
 * Checks for potential problems in comment notification email content.
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
 * Comment Notification Email Content Issues Treatment Class
 *
 * Detects missing site identity fields used in notification emails.
 *
 * @since 1.6093.1200
 */
class Treatment_Comment_Notification_Email_Content_Issues extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'comment-notification-email-content-issues';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Comment Notification Email Content Issues';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for missing content in comment notification emails';

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
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Comment_Notification_Email_Content_Issues' );
	}
}
