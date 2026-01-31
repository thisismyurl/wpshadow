<?php
/**
 * Gravity Forms Notifications Not Tested Diagnostic
 *
 * Checks if Gravity Forms notifications are tested.
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
 * Gravity Forms Notifications Not Tested Diagnostic Class
 *
 * Detects untested GF notifications.
 *
 * @since 1.2601.2352
 */
class Diagnostic_Gravity_Forms_Notifications_Not_Tested extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'gravity-forms-notifications-not-tested';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Gravity Forms Notifications Not Tested';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if Gravity Forms notifications are tested';

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
		// Check if Gravity Forms is active
		if ( ! class_exists( 'GFCommon' ) ) {
			return null;
		}

		// Check if notification tests are documented
		if ( ! get_option( 'gf_notifications_tested_date' ) ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Gravity Forms notifications have not been tested. Test all form notifications to ensure emails are being sent correctly to admins and users.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 10,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/gravity-forms-notifications-not-tested',
			);
		}

		return null;
	}
}
