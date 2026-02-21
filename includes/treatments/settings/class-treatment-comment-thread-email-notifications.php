<?php
/**
 * Comment Thread Email Notifications Treatment
 *
 * Checks if thread email notifications are available for comments.
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
 * Comment Thread Email Notifications Treatment Class
 *
 * Detects missing thread notification support for commenters.
 *
 * @since 1.5049.1331
 */
class Treatment_Comment_Thread_Email_Notifications extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'comment-thread-email-notifications';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Comment Thread Email Notifications';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if comment thread email notifications are available';

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
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Comment_Thread_Email_Notifications' );
	}
}
