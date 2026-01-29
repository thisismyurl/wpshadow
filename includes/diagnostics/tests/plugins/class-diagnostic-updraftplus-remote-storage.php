<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }

class Diagnostic_UpdraftplusRemoteStorage extends Diagnostic_Base {
	protected static $slug = 'updraftplus-remote-storage';
	protected static $title = 'UpdraftPlus Remote Storage';
	protected static $description = 'Validates plugin configuration';
	protected static $family = 'plugins';
	
	public static function check() {
		if ( ! class_exists( 'UpdraftPlus' ) ) { return null; }
		$storage = UpdraftPlus_Options::get_updraft_option( 'updraft_service' );
		if ( empty( $storage ) || in_array( $storage, array( 'none', '' ), true ) ) {
			return array(
				'id' => self::$slug,
				'title' => self::$title,
				'description' => __( 'Remote storage not configured - backups only on server', 'wpshadow' ),
				'severity' => 'critical',
				'threat_level' => 90,
				'auto_fixable' => false,
				'kb_link' => 'https://wpshadow.com/kb/updraftplus-remote',
			);
		}
		return null;
	}
}
