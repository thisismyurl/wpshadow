<?php
/**
 * Treatment: OPcache Enabled
 *
 * Provides guidance for enabling PHP OPcache. OPcache must be enabled in
 * php.ini or the server's PHP configuration — it cannot be activated from
 * inside a running PHP script.
 *
 * Risk level: n/a (guidance only)
 *
 * @package WPShadow
 * @subpackage Treatments
 * @since 0.6093.1300
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Returns hosting/server instructions for enabling PHP OPcache.
 */
class Treatment_Opcache_Enabled extends Treatment_Base {

	/** @var string */
	protected static $slug = 'opcache-enabled';

	// =========================================================================
	// Treatment_Base contract
	// =========================================================================

	public static function get_finding_id(): string {
		return self::$slug;
	}

	public static function get_risk_level(): string {
		return 'none';
	}

	/**
	 * Return OPcache enablement guidance.
	 *
	 * @return array{success:bool, message:string}
	 */
	public static function apply(): array {
		$php_version     = PHP_VERSION;
		$ini_path        = php_ini_loaded_file();
		$opcache_loaded  = in_array( 'Zend OPcache', get_loaded_extensions(), true );

		$status = $opcache_loaded
			? __( 'OPcache extension is loaded but may not be enabled (opcache.enable=0).', 'wpshadow' )
			: __( 'OPcache extension is not loaded.', 'wpshadow' );

		return [
			'success' => false,
			'message' => sprintf(
				/* translators: 1: PHP version, 2: php.ini path, 3: status string */
				__(
					"OPcache must be enabled in your PHP configuration.\n\n"
					. "Your environment: PHP %1\$s | php.ini: %2\$s | %3\$s\n\n"
					. "OPTION 1 — cPanel / Shared Hosting:\n"
					. "  1. In cPanel, go to 'MultiPHP INI Editor' or 'PHP Configuration'.\n"
					. "  2. Set opcache.enable = 1\n"
					. "  3. Set opcache.memory_consumption = 128 (or higher for large sites)\n"
					. "  4. Click Save. No server restart needed on shared hosting.\n\n"
					. "OPTION 2 — Edit php.ini directly (VPS/Dedicated):\n"
					. "  1. Open: %2\$s\n"
					. "  2. Find [opcache] section and set:\n"
					. "       opcache.enable=1\n"
					. "       opcache.enable_cli=1\n"
					. "       opcache.memory_consumption=128\n"
					. "       opcache.max_accelerated_files=10000\n"
					. "       opcache.revalidate_freq=60\n"
					. "  3. Restart PHP-FPM: sudo service php%4\$s-fpm restart\n\n"
					. "OPTION 3 — .user.ini (shared hosting without direct php.ini access):\n"
					. "  Create a .user.ini file in your WordPress root with:\n"
					. "       opcache.enable=1\n"
					. "       opcache.memory_consumption=64\n"
					. "  Note: .user.ini changes are cached; changes may take up to 5 minutes.\n\n"
					. "VERIFICATION:\n"
					. "  Add <?php phpinfo(); ?> to a temp file and check OPcache section.\n"
					. "  Or check your hosting control panel's PHP configuration viewer for the OPcache section.\n\n"
					. "Re-run the WPShadow scan after enabling OPcache.",
					'wpshadow'
				),
				$php_version,
				$ini_path ?: __( 'path not available', 'wpshadow' ),
				$status,
				substr( $php_version, 0, 3 ) // e.g. "8.1"
			),
		];
	}

	/**
	 * No state to undo (guidance only).
	 *
	 * @return array{success:bool, message:string}
	 */
	public static function undo(): array {
		return [
			'success' => true,
			'message' => __( 'This is a guidance-only treatment — no changes were made by WPShadow.', 'wpshadow' ),
		];
	}
}
