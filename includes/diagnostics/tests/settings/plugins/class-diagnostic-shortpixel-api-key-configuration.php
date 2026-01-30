<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }

class Diagnostic_ShortpixelApiKeyConfiguration extends Diagnostic_Base {
	protected static $slug = 'shortpixel-api-key-configuration';
	protected static $title = 'ShortPixel API Key';
	protected static $description = 'Validates plugin configuration';
	protected static $family = 'plugins';
	
	public static function check() {
		if ( ! class_exists( 'ShortPixelAPI' ) ) { return null; }
		$api_key = get_option( 'wp-short-pixel-apiKey' );
		if ( empty( $api_key ) ) {
			return array(
				'id' => self::$slug,
				'title' => self::$title,
				'description' => __( 'API key not configured', 'wpshadow' ),
				'severity' => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link' => 'https://wpshadow.com/kb/shortpixel-api',
			);
		}
		return null;
	}
}
