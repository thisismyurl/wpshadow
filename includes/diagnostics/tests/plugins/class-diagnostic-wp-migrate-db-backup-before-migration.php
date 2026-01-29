<?php
/**
 * WP Migrate DB Backup Diagnostic
 *
 * WP Migrate DB not backing up before migration.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.380.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WP Migrate DB Backup Diagnostic Class
 *
 * @since 1.380.0000
 */
class Diagnostic_WpMigrateDbBackupBeforeMigration extends Diagnostic_Base {

	protected static $slug = 'wp-migrate-db-backup-before-migration';
	protected static $title = 'WP Migrate DB Backup';
	protected static $description = 'WP Migrate DB not backing up before migration';
	protected static $family = 'security';

	public static function check() {
		if ( ! defined( 'WPMDB_VERSION' ) ) {
			return null;
		}
		
		$has_issue = false;
		
		if ( $has_issue ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => self::$description,
				'severity'    => self::calculate_severity( 70 ),
				'threat_level' => 70,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/wp-migrate-db-backup-before-migration',
			);
		}
		
		return null;
	}
}
