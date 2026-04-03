<?php
/**
 * wp-config Permissions Hardened Diagnostic
 *
 * Checks whether wp-config.php has restrictive file permissions to prevent
 * other system users or web processes from reading database credentials.
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
 * wp-config Permissions Hardened Diagnostic Class
 *
 * Uses the Server_Env helper to read the wp-config.php file permissions
 * via fileperms() and flags configurations that are more permissive than 0640.
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
	protected static $description = 'Checks whether wp-config.php has restrictive file permissions to prevent other system users or web processes from reading database credentials.';

	/**
	 * Gauge family/category for dashboard placement.
	 *
	 * @var string
	 */
	protected static $family = 'security';

/**
 * Confidence level of this diagnostic.
 *
 * @var string
 */
protected static $confidence = 'standard';

	/**
	 * Run the diagnostic check.
	 *
	 * Reads wp-config.php permissions via the Server_Env helper and returns
	 * a high-severity finding when the file is more permissive than expected,
	 * skipping gracefully when permissions cannot be determined.
	 *
	 * @since  0.6093.1200
	 * @return array|null Finding array when permissions are too open, null when healthy.
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
			'kb_link'      => 'https://wpshadow.com/kb/wp-config-permissions?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			'details'      => array(
				'path'               => $path,
				'current_permission' => $octal,
				'recommended'        => '0600',
			),
		);
	}
}
