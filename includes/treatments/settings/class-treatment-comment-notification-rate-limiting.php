<?php
/**
 * Comment Notification Rate Limiting Treatment
 *
 * Checks whether comment notification email volume is excessive.
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
 * Comment Notification Rate Limiting Treatment Class
 *
 * Detects excessive comment notification volume.
 *
 * @since 1.5049.1331
 */
class Treatment_Comment_Notification_Rate_Limiting extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'comment-notification-rate-limiting';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Comment Notification Rate Limiting';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for excessive comment notification email volume';

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
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Comment_Notification_Rate_Limiting' );
	}
}
