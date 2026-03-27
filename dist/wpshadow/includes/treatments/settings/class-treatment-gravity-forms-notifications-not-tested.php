<?php
/**
 * Gravity Forms Notifications Not Tested Treatment
 *
 * Checks if Gravity Forms notifications are tested.
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
 * Gravity Forms Notifications Not Tested Treatment Class
 *
 * Detects untested GF notifications.
 *
 * @since 1.6093.1200
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
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Gravity_Forms_Notifications_Not_Tested' );
	}
}
