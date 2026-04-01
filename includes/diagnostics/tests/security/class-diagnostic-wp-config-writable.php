<?php
/**
 * WP Config Writable Diagnostic
 *
 * Checks whether wp-config.php is writable, which can be a security risk.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_WP_Config_Writable Class
 *
 * Checks whether wp-config.php is writable by the web server.
 *
 * @since 0.6093.1200
 */
class Diagnostic_WP_Config_Writable extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'wp-config-writable';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'wp-config Writable';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether wp-config.php is writable';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$config_path = ABSPATH . 'wp-config.php';
		if ( ! file_exists( $config_path ) ) {
			$config_path = dirname( ABSPATH ) . '/wp-config.php';
		}

		if ( file_exists( $config_path ) && is_writable( $config_path ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'wp-config.php is writable by the web server. Lock it down to reduce attack risk.', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 85,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/wp-config-writable?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'meta'         => array(
					'config_path' => $config_path,
				),
			);
		}

		return null;
	}
}