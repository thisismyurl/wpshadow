<?php
/**
 * Comment Moderation Queue Not Monitored Treatment
 *
 * Checks if comment moderation is monitored.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6030.2352
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Comment Moderation Queue Not Monitored Treatment Class
 *
 * Detects unmonitored comment moderation.
 *
 * @since 1.6030.2352
 */
class Treatment_Comment_Moderation_Queue_Not_Monitored extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'comment-moderation-queue-not-monitored';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Comment Moderation Queue Not Monitored';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if comment moderation is monitored';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6030.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Comment_Moderation_Queue_Not_Monitored' );
	}
}
