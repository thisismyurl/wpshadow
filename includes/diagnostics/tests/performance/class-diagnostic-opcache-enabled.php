<?php
/**
 * OPcache Enabled Diagnostic
 *
 * Verifies that PHP OPcache is loaded and active, which caches compiled PHP
 * bytecode and significantly reduces CPU usage on every request.
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
 * OPcache Enabled Diagnostic Class
 *
 * @since 0.6093.1200
 */
class Diagnostic_Opcache_Enabled extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'opcache-enabled';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'OPcache Enabled';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether PHP OPcache is enabled on the server, which caches compiled PHP bytecode and significantly reduces page generation time.';

	/**
	 * Gauge family/category for dashboard placement.
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * Checks whether the Zend OPcache extension is loaded and that caching
	 * is enabled via opcache_get_status() or ini settings.
	 *
	 * @since  0.6093.1200
	 * @return array|null Finding array when OPcache is absent or disabled, null when healthy.
	 */
	public static function check() {
		if ( Server_Env::is_opcache_enabled() ) {
			return null;
		}

		$installed = Server_Env::is_opcache_installed();

		if ( $installed ) {
			// Installed but explicitly disabled in php.ini.
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'PHP OPcache is installed but not enabled. OPcache compiles and caches PHP bytecode, dramatically reducing PHP execution time (typically 2–5× faster). Enable it by setting opcache.enable=1 in your php.ini or via the hosting control panel.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/opcache-enabled?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'      => array(
					'opcache_installed' => true,
					'opcache_enabled'   => false,
					'fix'               => __( 'Set opcache.enable = 1 in php.ini or ask your hosting provider to enable OPcache.', 'wpshadow' ),
				),
			);
		}

		// Not installed at all.
		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'PHP OPcache is not installed on this server. OPcache significantly reduces PHP execution time by caching compiled bytecode. Contact your hosting provider or system administrator to install and enable the Zend OPcache extension.', 'wpshadow' ),
			'severity'     => 'high',
			'threat_level' => 65,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/opcache-enabled?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			'details'      => array(
				'opcache_installed' => false,
				'opcache_enabled'   => false,
			),
		);
	}
}
