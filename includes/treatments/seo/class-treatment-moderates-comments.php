<?php
/**
 * Treatment: Comments Moderated Regularly
 *
 * Tests if comments are being moderated and responded to in a timely manner.
 * Active comment moderation builds community and prevents spam.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Comments Moderated Regularly Treatment Class
 *
 * Checks if site moderates comments regularly and maintains
 * clean, spam-free comment sections.
 *
 * Detection methods:
 * - Comment moderation settings
 * - Spam filtering tools
 * - Recent comment activity
 * - Comment response time
 *
 * @since 0.6093.1200
 */
class Treatment_Moderates_Comments extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'moderates-comments';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Comments Moderated Regularly';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests if comments are being moderated and responded to in a timely manner';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'user-engagement';

	/**
	 * Run the treatment check.
	 *
	 * Scoring system (5 points):
	 * - 1 point: Comment moderation enabled
	 * - 1 point: Spam filtering active (Akismet, etc.)
	 * - 1 point: Low spam-to-legitimate ratio
	 * - 1 point: Admin responses to recent comments
	 * - 1 point: No pending comments older than 7 days
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Moderates_Comments' );
	}
}
