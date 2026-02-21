<?php
/**
 * Comment Reply Notifications Missing Treatment
 *
 * Checks if reply notification emails are unavailable for threaded comments.
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
 * Comment Reply Notifications Missing Treatment Class
 *
 * Detects missing reply notification support for threaded comments.
 *
 * @since 1.5049.1331
 */
class Treatment_Comment_Reply_Notifications_Missing extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'comment-reply-notifications-missing';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Comment Reply Notifications Missing';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if reply notifications are available for threaded comments';

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
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Comment_Reply_Notifications_Missing' );
	}
}
