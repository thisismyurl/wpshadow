<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }

class Diagnostic_ShortpixelBackupRetention extends Diagnostic_Base {
	protected static $slug = 'shortpixel-backup-retention';
	protected static $title = 'ShortPixel Backup Images';
	protected static $description = 'Validates plugin configuration';
	protected static $family = 'plugins';
	
	public static function check() {
		if ( ! class_exists( 'ShortPixelAPI' ) ) { return null; }
		$backup = get_option( 'wp-short-backup-images' );
		if ( ! $backup ) {
			return array(
				'id' => self::$slug,
				'title' => self::$title,
				'description' => __( 'Original images not being backed up', 'wpshadow' ),
				'severity' => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link' => 'https://wpshadow.com/kb/shortpixel-backup',
			);
		}
		return null;
	}
}
