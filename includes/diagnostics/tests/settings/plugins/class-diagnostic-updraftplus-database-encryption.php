<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }

class Diagnostic_UpdraftplusDatabaseEncryption extends Diagnostic_Base {
	protected static $slug = 'updraftplus-database-encryption';
	protected static $title = 'UpdraftPlus Database Encryption';
	protected static $description = 'Validates plugin configuration';
	protected static $family = 'plugins';
	
	public static function check() {
		if ( ! class_exists( 'UpdraftPlus' ) ) { return null; }
		$encrypt = UpdraftPlus_Options::get_updraft_option( 'updraft_encryptionphrase' );
		if ( empty( $encrypt ) ) {
			return array(
				'id' => self::$slug,
				'title' => self::$title,
				'description' => __( 'Database backups not encrypted', 'wpshadow' ),
				'severity' => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link' => 'https://wpshadow.com/kb/updraftplus-encryption',
			);
		}
		return null;
	}
}
