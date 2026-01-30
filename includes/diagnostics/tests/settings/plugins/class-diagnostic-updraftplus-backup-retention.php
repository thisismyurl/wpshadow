<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }

class Diagnostic_UpdraftplusBackupRetention extends Diagnostic_Base {
	protected static $slug = 'updraftplus-backup-retention';
	protected static $title = 'UpdraftPlus Backup Retention';
	protected static $description = 'Validates plugin configuration';
	protected static $family = 'plugins';
	
	public static function check() {
		if ( ! class_exists( 'UpdraftPlus' ) ) { return null; }
		$retain = UpdraftPlus_Options::get_updraft_option( 'updraft_retain' );
		if ( empty( $retain ) || $retain < 2 ) {
			return array(
				'id' => self::$slug,
				'title' => self::$title,
				'description' => __( 'Insufficient backup retention - recommend 3+ backups', 'wpshadow' ),
				'severity' => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link' => 'https://wpshadow.com/kb/updraftplus-retention',
			);
		}
		return null;
	}
}
