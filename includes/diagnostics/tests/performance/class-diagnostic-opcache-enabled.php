<?php
/**
 * OPcache Enabled Diagnostic (Stub)
 *
 * Generated diagnostic stub for post-install hardening checklist item 69.
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
 * OPcache Enabled Diagnostic Class (Stub)
 *
 * TODO: Implement robust, production-safe test logic.
 * TODO: Implement companion treatment after validation.
 * TODO: Add KB article and user-facing remediation guidance.
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
	protected static $description = 'Stub diagnostic for OPcache Enabled. TODO: implement full test and remediation guidance.';

	/**
	 * Gauge family/category for dashboard placement.
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * TODO Test Plan:
	 * Use opcache_get_status enabled flag.
	 *
	 * TODO Fix Plan:
	 * Fix by enabling OPcache in PHP config.
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
				'kb_link'      => 'https://wpshadow.com/kb/opcache-enabled',
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
			'kb_link'      => 'https://wpshadow.com/kb/opcache-enabled',
			'details'      => array(
				'opcache_installed' => false,
				'opcache_enabled'   => false,
			),
		);
	}
}
