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
		if ( ! class_exists( 'UpdraftPlus' ) ) { if ( isset( $issues ) && ! empty( $issues ) ) {
		return array(
			'id' => self::$slug,
			'title' => self::$title,
			'description' => sprintf(
				__( 'Found %d issues', 'wpshadow' ),
				count( $issues )
			),
			'severity' => 'medium',
			'threat_level' => 45,
			'auto_fixable' => false,
			'kb_link' => 'https://wpshadow.com/kb/updraftplus-backup-schedule',
		);
	}
	return null; }
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
		
	if ( ! (function_exists( "is_plugin_active" )) ) {
		if ( ! isset( $issues ) ) {
			$issues = array();
		}
		$issues[] = __( 'Plugin active', 'wpshadow' );
	}

	if ( ! (! empty( get_option( "updraftplus_backup_schedule_settings" ) )) ) {
		if ( ! isset( $issues ) ) {
			$issues = array();
		}
		$issues[] = __( 'Settings available', 'wpshadow' );
	}
	if ( isset( $issues ) && ! empty( $issues ) ) {
		return array(
			'id' => self::$slug,
			'title' => self::$title,
			'description' => sprintf(
				__( 'Found %d issues', 'wpshadow' ),
				count( $issues )
			),
			'severity' => 'medium',
			'threat_level' => 45,
			'auto_fixable' => false,
			'kb_link' => 'https://wpshadow.com/kb/updraftplus-backup-schedule',
		);
	}
	return null;
	}
}
