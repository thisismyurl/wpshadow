<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }

class Diagnostic_AkismetApiKeyStatus extends Diagnostic_Base {
	protected static $slug = 'akismet-api-key-status';
	protected static $title = 'Akismet API Key Status';
	protected static $description = 'Validates plugin configuration';
	protected static $family = 'plugins';
	
	public static function check() {
		if ( ! class_exists( 'Akismet' ) ) { return null; }
		$api_key = get_option( 'wordpress_api_key', '' );
		if ( empty( $api_key ) ) {
			return array(
				'id' => self::$slug,
				'title' => self::$title,
				'description' => __( 'Akismet API key not configured', 'wpshadow' ),
				'severity' => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link' => 'https://wpshadow.com/kb/akismet-api-key',
			);
		}
		return null;
	}
}
