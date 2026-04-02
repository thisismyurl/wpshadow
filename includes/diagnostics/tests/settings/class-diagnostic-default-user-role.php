<?php
/**
 * Default User Role Reviewed Diagnostic (Stub)
 *
 * TODO stub mapped to the settings gauge.
 *
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Diagnostics\Helpers\Diagnostic_WP_Settings_Helper as WP_Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Default_User_Role_Reviewed Class
 *
 * TODO: Implement full test logic and remediation guidance.
 */
class Diagnostic_Default_User_Role extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'default-user-role';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Default User Role';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether the default role assigned to new user registrations is a safe, low-privilege role such as Subscriber rather than an elevated role.';

	/**
	 * Gauge family/category.
	 *
	 * @var string
	 */
	protected static $family = 'settings';

	/**
	 * Run the diagnostic check.
	 *
	 * TODO Test Plan:
	 * - Check get_option('default_role') for unintended elevated or unsuitable roles.
	 *
	 * TODO Fix Plan:
	 * - Set the default role to the least privilege needed for new accounts.
	 * - Use WordPress hooks, filters, settings, DB fixes, PHP config, or accessible server settings.
	 * - Do not modify WordPress core files.
	 * - Ensure performance/security/success impact and align with WPShadow commandments.
	 *
	 * @since  0.6093.1200
	 * @return array|null Finding array if issue exists, null if healthy.
	 */
	public static function check() {
		$role = WP_Settings::get_default_user_role();

		// 'subscriber' is the safe default — minimal capabilities.
		if ( 'subscriber' === $role ) {
			return null;
		}

		$dangerous = in_array( $role, array( 'administrator', 'editor', 'author' ), true );

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => $dangerous
				? __( 'The default user role for new registrations is set to a high-privilege role. Anyone who registers will immediately have broad site access. Change it to Subscriber unless you have a specific business reason.', 'wpshadow' )
				: __( 'The default user role for new registrations is not the standard Subscriber role. Confirm this is intentional and that the assigned role grants only the minimum permissions required.', 'wpshadow' ),
			'severity'     => $dangerous ? 'high' : 'medium',
			'threat_level' => $dangerous ? 80 : 40,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/default-user-role',
			'details'      => array(
				'default_role'    => $role,
				'registration_open' => WP_Settings::is_registration_open(),
			),
		);
	}
}
