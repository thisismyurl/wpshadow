<?php
/**
 * wp-config Permissions Hardened Diagnostic (Stub)
 *
 * Generated diagnostic stub for post-install hardening checklist item 36.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Diagnostics\Helpers\Diagnostic_Server_Environment_Helper as Server_Env;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * wp-config Permissions Hardened Diagnostic Class (Stub)
 *
 * TODO: Implement robust, production-safe test logic.
 * TODO: Implement companion treatment after validation.
 * TODO: Add KB article and user-facing remediation guidance.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Wp_Config_Permissions_Hardened extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'wp-config-permissions-hardened';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'wp-config Permissions Hardened';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Stub diagnostic for wp-config Permissions Hardened. TODO: implement full test and remediation guidance.';

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
	 * Use fileperms on wp-config.php and parent fallback checks.
	 *
	 * TODO Fix Plan:
	 * Fix by restricting config file permissions.
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
		$hardened = Server_Env::is_wp_config_hardened();

		// Cannot determine permissions (e.g. open_basedir restriction) — skip.
		if ( null === $hardened ) {
			return null;
		}

		if ( $hardened ) {
			return null;
		}

		$path  = Server_Env::get_wp_config_path();
		$octal = Server_Env::get_wp_config_permissions_octal();

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'wp-config.php has overly permissive file permissions. This file contains your database credentials and secret keys. Restrict it to 600 or 640 so only the web server process owner can read it.', 'wpshadow' ),
			'severity'     => 'high',
			'threat_level' => 70,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/wp-config-permissions',
			'details'      => array(
				'path'               => $path,
				'current_permission' => $octal,
				'recommended'        => '0600',
			),
		);
	}
}
