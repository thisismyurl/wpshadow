<?php
/**
 * Comment Moderation Queue Treatment
 *
 * Checks if pending comments are being processed.
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
 * Comment Moderation Queue Treatment Class
 *
 * Verifies that pending comments are being reviewed and processed
 * in a timely manner.
 *
 * @since 1.6093.1200
 */
class Treatment_Comment_Moderation_Queue extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'comment-moderation-queue';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Comment Moderation Queue';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if pending comments are being processed';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'publisher';

	/**
	 * Run the comment moderation queue treatment check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if moderation issues detected, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Comment_Moderation_Queue' );
	}
}
