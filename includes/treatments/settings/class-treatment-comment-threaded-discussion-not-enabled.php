<?php
/**
 * Comment Threaded Discussion Not Enabled Treatment
 *
 * Checks if threaded comments are enabled.
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
 * Comment Threaded Discussion Not Enabled Treatment Class
 *
 * Detects disabled threaded comments.
 *
 * @since 1.6093.1200
 */
class Treatment_Comment_Threaded_Discussion_Not_Enabled extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'comment-threaded-discussion-not-enabled';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Comment Threaded Discussion Not Enabled';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if threaded comments are enabled';

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
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Comment_Threaded_Discussion_Not_Enabled' );
	}
}
