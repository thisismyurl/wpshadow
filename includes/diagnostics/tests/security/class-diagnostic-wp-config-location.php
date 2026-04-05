<?php
/**
 * wp-config Location Diagnostic
 *
 * Checks whether wp-config.php has been moved above the web root or
 * otherwise protected from direct web access.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      0.6095
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Diagnostics\Helpers\Diagnostic_Server_Environment_Helper as Server_Env;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Wp_Config_Location Class
 *
 * Uses the Server_Env helper to verify whether wp-config.php is located
 * above the webroot or has restricted permissions.
 *
 * @since 0.6095
 */
class Diagnostic_Wp_Config_Location extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'wp-config-location';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'wp-config Location';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether wp-config.php has been moved above the web root or otherwise protected from direct web access, preventing credential exposure.';

	/**
	 * Gauge family/category.
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
	 * Delegates to the Server_Env helper to check whether wp-config.php is
	 * in a hardened location or has secure permissions, returning a high-severity
	 * finding when the file is exposed with overly permissive settings.
	 *
	 * @since  0.6095
	 * @return array|null Finding array when wp-config.php is exposed, null when healthy.
	 */
	public static function check() {
		$hardened = Server_Env::is_wp_config_hardened();
		if ( null === $hardened || true === $hardened ) {
			return null;
		}

		$octal = Server_Env::get_wp_config_permissions_octal();
		$path  = Server_Env::get_wp_config_path();

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'wp-config.php has overly permissive file permissions. This file contains database credentials and security keys - it should be readable only by the web server process. Restrict permissions to 0400 or 0440.', 'wpshadow' ),
			'severity'     => 'high',
			'threat_level' => 70,
			'details'      => array(
				'path'  => $path,
				'octal' => $octal,
			),
		);
	}
}
