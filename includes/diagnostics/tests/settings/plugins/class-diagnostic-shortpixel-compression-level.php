<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }

class Diagnostic_ShortpixelCompressionLevel extends Diagnostic_Base {
	protected static $slug = 'shortpixel-compression-level';
	protected static $title = 'ShortPixel Compression';
	protected static $description = 'Validates plugin configuration';
	protected static $family = 'plugins';
	
	public static function check() {
		if ( ! class_exists( 'ShortPixelAPI' ) ) { return null; }
		$compression = get_option( 'wp-short-pixel-compression' );
		if ( empty( $compression ) ) {
			return array(
				'id' => self::$slug,
				'title' => self::$title,
				'description' => __( 'Compression level not configured', 'wpshadow' ),
				'severity' => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link' => 'https://wpshadow.com/kb/shortpixel-compression',
			);
		}
		return null;
	}
}
