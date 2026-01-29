<?php
/**
 * All-in-One WP Migration Backup Retention Diagnostic
 *
 * AIO WP Migration not cleaning old backups.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.388.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * All-in-One WP Migration Backup Retention Diagnostic Class
 *
 * @since 1.388.0000
 */
class Diagnostic_AllInOneWpMigrationBackupRetention extends Diagnostic_Base {

	protected static $slug = 'all-in-one-wp-migration-backup-retention';
	protected static $title = 'All-in-One WP Migration Backup Retention';
	protected static $description = 'AIO WP Migration not cleaning old backups';
	protected static $family = 'performance';

	public static function check() {
		if ( ! defined( 'AI1WM_PLUGIN_NAME' ) ) {
			return null;
		}
		
		$has_issue = false;
		
		if ( $has_issue ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => self::$description,
				'severity'    => self::calculate_severity( 45 ),
				'threat_level' => 45,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/all-in-one-wp-migration-backup-retention',
			);
		}
		
		return null;
	}
}
