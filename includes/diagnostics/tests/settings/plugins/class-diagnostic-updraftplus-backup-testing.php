<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }

class Diagnostic_UpdraftplusBackupTesting extends Diagnostic_Base {
	protected static $slug = 'updraftplus-backup-testing';
	protected static $title = 'UpdraftPlus Backup Testing';
	protected static $description = 'Validates plugin configuration';
	protected static $family = 'plugins';
	
	public static function check() {
		if ( ! class_exists( 'UpdraftPlus' ) ) { return null; }
		$last_test = get_option( 'updraftplus_last_backup_test', 0 );
		if ( empty( $last_test ) || ( time() - $last_test ) > ( 90 * DAY_IN_SECONDS ) ) {
			return array(
				'id' => self::$slug,
				'title' => self::$title,
				'description' => __( 'Backups not tested in 90+ days', 'wpshadow' ),
				'severity' => 'high',
				'threat_level' => 65,
				'auto_fixable' => false,
				'kb_link' => 'https://wpshadow.com/kb/updraftplus-testing',
			);
		}
		return null;
	}
}
