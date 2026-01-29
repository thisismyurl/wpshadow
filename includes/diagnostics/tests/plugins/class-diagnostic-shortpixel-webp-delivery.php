<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }

class Diagnostic_ShortpixelWebpDelivery extends Diagnostic_Base {
	protected static $slug = 'shortpixel-webp-delivery';
	protected static $title = 'ShortPixel WebP Delivery';
	protected static $description = 'Validates plugin configuration';
	protected static $family = 'plugins';
	
	public static function check() {
		if ( ! class_exists( 'ShortPixelAPI' ) ) { return null; }
		$webp = get_option( 'wp-short-pixel-create-webp' );
		if ( empty( $webp ) ) {
			return array(
				'id' => self::$slug,
				'title' => self::$title,
				'description' => __( 'WebP format not enabled', 'wpshadow' ),
				'severity' => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link' => 'https://wpshadow.com/kb/shortpixel-webp',
			);
		}
		return null;
	}
}
