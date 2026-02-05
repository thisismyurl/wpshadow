<?php
/**
 * Gravity Forms Notifications Not Tested Treatment
 *
 * Checks if Gravity Forms notifications are tested.
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
 * Gravity Forms Notifications Not Tested Treatment Class
 *
 * Detects untested GF notifications.
 *
 * @since 1.6030.2352
 */
class Treatment_Gravity_Forms_Notifications_Not_Tested extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'gravity-forms-notifications-not-tested';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Gravity Forms Notifications Not Tested';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if Gravity Forms notifications are tested';

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
