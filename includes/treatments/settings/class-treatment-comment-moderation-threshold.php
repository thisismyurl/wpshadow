<?php
/**
 * Comment Moderation Threshold Treatment
 *
 * Verifies comment moderation rules are configured to prevent spam while
 * allowing legitimate discussion.
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
 * Comment Moderation Threshold Treatment Class
 *
 * Checks comment moderation queue configuration.
 *
 * @since 1.6093.1200
 */
class Treatment_Comment_Moderation_Threshold extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'comment-moderation-threshold';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Comment Moderation Threshold';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies comment moderation threshold';

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
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Comment_Moderation_Threshold' );
	}
}
