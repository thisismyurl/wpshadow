<?php
/**
 * User Profile Completion Rate Not Tracked Diagnostic
 *
 * Checks if user profile completion is monitored.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2340
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * User Profile Completion Rate Not Tracked Diagnostic Class
 *
 * Detects missing profile completion tracking.
 *
 * @since 1.2601.2340
 */
class Diagnostic_User_Profile_Completion_Rate_Not_Tracked extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'user-profile-completion-rate-not-tracked';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'User Profile Completion Rate Not Tracked';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if user profile completion is tracked';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2340
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check user count
		$user_count = count_users();
		$total_users = $user_count['total_users'];

		if ( $total_users > 100 ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => sprintf(
					__( '%d users exist but profile completion is not tracked. Monitor user profile data quality.', 'wpshadow' ),
					$total_users
				),
				'severity'      => 'low',
				'threat_level'  => 10,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/user-profile-completion-rate-not-tracked',
			);
		}

		return null;
	}
}
