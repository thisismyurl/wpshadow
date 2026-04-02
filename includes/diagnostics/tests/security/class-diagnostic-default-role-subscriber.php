<?php
/**
 * Default Role is Subscriber Diagnostic (Stub)
 *
 * Generated diagnostic stub for post-install hardening checklist item 25.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Diagnostics\Helpers\Diagnostic_WP_Settings_Helper as WP_Settings;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Default Role is Subscriber Diagnostic Class (Stub)
 *
 * TODO: Implement robust, production-safe test logic.
 * TODO: Implement companion treatment after validation.
 * TODO: Add KB article and user-facing remediation guidance.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Default_Role_Subscriber extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'default-role-subscriber';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Default Role is Subscriber';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Stub diagnostic for Default Role is Subscriber. TODO: implement full test and remediation guidance.';

	/**
	 * Gauge family/category for dashboard placement.
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * TODO Test Plan:
	 * Read default_role option value.
	 *
	 * TODO Fix Plan:
	 * Fix by setting safest default role.
	 *
	 * Constraints:
	 * - Must be testable using built-in WordPress functions or PHP checks.
	 * - Must be fixable via hooks/filters/settings/DB/PHP/server setting.
	 * - Must not modify WordPress core files.
	 * - Must improve performance, security, or site success.
	 *
	 * @since  0.6093.1200
	 * @return array|null Return finding array when issue exists, null when healthy.
	 */
	public static function check() {
		$role = WP_Settings::get_default_user_role();

		if ( 'subscriber' === $role ) {
			return null;
		}

		$dangerous = in_array( $role, array( 'administrator', 'editor', 'author' ), true );

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => $dangerous
				? __( 'The default user role for new registrations is set to a high-privilege role. Any visitor who registers will immediately have broad site access including editing posts or managing the site.', 'wpshadow' )
				: __( 'The default user role for new registrations is not Subscriber. Confirm this is intentional and that the role grants only the minimum capabilities required for your use case.', 'wpshadow' ),
			'severity'     => $dangerous ? 'high' : 'medium',
			'threat_level' => $dangerous ? 80 : 40,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/default-role-subscriber',
			'details'      => array(
				'default_role'      => $role,
				'registration_open' => WP_Settings::is_registration_open(),
				'high_risk'         => $dangerous,
			),
		);
	}
}
