<?php
/**
 * Comment Spam and Moderation Configuration
 *
 * Validates spam prevention and comment moderation setup.
 *
 * @since   1.6030.2148
 * @package WPShadow\Treatments
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Comment_Spam_Moderation Class
 *
 * Checks for proper spam prevention and comment moderation configuration.
 *
 * @since 1.6030.2148
 */
class Treatment_Comment_Spam_Moderation extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'comment-spam-moderation';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Comment Spam and Moderation';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Validates spam prevention and comment moderation setup';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'comments';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6030.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Comment_Spam_Moderation' );
	}
}
