<?php
/**
 * Treatment: Database Version Supported
 *
 * Provides guidance for upgrading the MySQL or MariaDB database server.
 * Database engine upgrades must be performed at the hosting or server level;
 * a WordPress plugin cannot upgrade the database server software.
 *
 * Risk level: n/a (guidance only)
 *
 * @package WPShadow
 * @subpackage Treatments
 * @since 0.6095
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Returns hosting-level guidance for upgrading the database server version.
 */
class Treatment_Database_Version_Supported extends Treatment_Base {

	/** @var string */
	protected static $slug = 'database-version-supported';

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
	 * Return database server upgrade guidance.
	 *
	 * @return array{success:bool, message:string}
	 */
	public static function apply(): array {
		global $wpdb;

		$db_version = $wpdb->db_version();
		$server     = $wpdb->get_var( 'SELECT VERSION()' ) ?? $db_version;

		return [
			'success' => false,
			'message' => sprintf(
				/* translators: %s: current database version string */
				__(
					"Your current database version is: %s\n\n"
					. "WordPress recommends MySQL 8.0+ or MariaDB 10.6+.\n"
					. "Database server upgrades must be performed at the hosting or server level.\n\n"
					. "IMPORTANT — BACK UP FIRST:\n"
					. "  Always create a full database backup before any upgrade.\n"
					. "  Use: mysqldump -u USER -p DATABASE > backup_$(date +%%Y%%m%%d).sql\n"
					. "  Or use the 'Backup' tool in cPanel / phpMyAdmin.\n\n"
					. "OPTION 1 — cPanel / Shared Hosting:\n"
					. "  1. Check if your host offers MySQL 8.0 or MariaDB 10.6 under 'MySQL Databases'\n"
					. "     or 'Software' in cPanel.\n"
					. "  2. Most hosts upgrade databases cluster-wide — open a support ticket:\n"
					. "     'Please upgrade my database to MySQL 8.0 or MariaDB 10.6.'\n"
					. "  3. Ask about maintenance windows and test your site after the upgrade.\n\n"
					. "OPTION 2 — VPS/Dedicated (Ubuntu/Debian — MariaDB):\n"
					. "  sudo apt-get install mariadb-server\n"
					. "  sudo mysql_upgrade -u root -p\n"
					. "  sudo service mariadb restart\n\n"
					. "OPTION 3 — VPS/Dedicated (MySQL 8):\n"
					. "  Follow the official MySQL upgrade guide:\n"
					. "  https://dev.mysql.com/doc/refman/8.0/en/upgrading.html\n\n"
					. "VERIFICATION:\n"
					. "  mysql --version   (or mariadb --version)\n\n"
					. "Re-run the WPShadow scan after upgrading.",
					'wpshadow'
				),
				esc_html( $server )
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
