<?php
/**
 * User Activity Tracking Not Implemented Diagnostic
 *
 * Checks if user activity is tracked.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2352
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * User Activity Tracking Not Implemented Diagnostic Class
 *
 * Detects missing user activity tracking.
 *
 * @since 1.2601.2352
 */
class Diagnostic_User_Activity_Tracking_Not_Implemented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'user-activity-tracking-not-implemented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'User Activity Tracking Not Implemented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if user activity is tracked';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for user tracking or analytics
		if ( ! has_filter( 'user_register' ) && ! is_plugin_active( 'jetpack/jetpack.php' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'User activity tracking is not implemented. Track user behavior to improve UX and increase engagement.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 15,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/user-activity-tracking-not-implemented',
			);
		}

		return null;
	}
}
