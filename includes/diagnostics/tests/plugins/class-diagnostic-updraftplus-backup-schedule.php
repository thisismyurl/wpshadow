<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }

class Diagnostic_UpdraftplusBackupSchedule extends Diagnostic_Base {
	protected static $slug = 'updraftplus-backup-schedule';
	protected static $title = 'UpdraftPlus Backup Schedule';
	protected static $description = 'Validates plugin configuration';
	protected static $family = 'plugins';
	
	public static function check() {
		if ( ! class_exists( 'UpdraftPlus' ) ) { return null; }
		$schedule = UpdraftPlus_Options::get_updraft_option( 'updraft_interval' );
		if ( empty( $schedule ) || 'manual' === $schedule ) {
			return array(
				'id' => self::$slug,
				'title' => self::$title,
				'description' => __( 'Automatic backups not scheduled', 'wpshadow' ),
				'severity' => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link' => 'https://wpshadow.com/kb/updraftplus-schedule',
			);
		}
		return null;
	}
}
