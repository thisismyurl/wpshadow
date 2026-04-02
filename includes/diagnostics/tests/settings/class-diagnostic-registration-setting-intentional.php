<?php
/**
 * Registration Setting Intentional Diagnostic (Stub)
 *
 * Generated diagnostic stub for post-install hardening checklist item 24.
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
 * Registration Setting Intentional Diagnostic Class (Stub)
 *
 * TODO: Implement robust, production-safe test logic.
 * TODO: Implement companion treatment after validation.
 * TODO: Add KB article and user-facing remediation guidance.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Registration_Setting_Intentional extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'registration-setting-intentional';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Registration Setting Intentional';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Stub diagnostic for Registration Setting Intentional. TODO: implement full test and remediation guidance.';

	/**
	 * Gauge family/category for dashboard placement.
	 *
	 * @var string
	 */
	protected static $family = 'settings';

	/**
	 * Run the diagnostic check.
	 *
	 * TODO Test Plan:
	 * Read users_can_register option and compare site mode.
	 *
	 * TODO Fix Plan:
	 * Fix by enabling/disabling registration appropriately.
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
		if ( ! WP_Settings::is_registration_open() ) {
			return null;
		}

		$default_role = WP_Settings::get_default_user_role();
		$high_risk    = ! in_array( $default_role, array( 'subscriber', 'customer' ), true );

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'Open user registration is enabled on your site. Anyone can create an account. If this is intentional (e.g. membership site) ensure spam registrations are handled and the default role grants minimal privileges.', 'wpshadow' ),
			'severity'     => $high_risk ? 'high' : 'medium',
			'threat_level' => $high_risk ? 70 : 40,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/registration-setting',
			'details'      => array(
				'registration_open' => true,
				'default_role'      => $default_role,
				'high_risk_role'    => $high_risk,
			),
		);
	}
}
