<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }

class Diagnostic_SmushCdnIntegration extends Diagnostic_Base {
	protected static $slug = 'smush-cdn-integration';
	protected static $title = 'Smush CDN Integration';
	protected static $description = 'Validates plugin configuration';
	protected static $family = 'plugins';
	
	public static function check() {
		if ( ! class_exists( 'WP_Smush' ) ) { return null; }
		if ( defined( 'WP_SMUSH_PREMIUM' ) ) {
			$cdn = get_option( 'wp-smush-cdn_status' );
			if ( empty( $cdn ) ) {
				return array(
					'id' => self::$slug,
					'title' => self::$title,
					'description' => __( 'Premium CDN not configured', 'wpshadow' ),
					'severity' => 'medium',
					'threat_level' => 40,
					'auto_fixable' => false,
					'kb_link' => 'https://wpshadow.com/kb/smush-cdn',
				);
			}
		}
		return null;
	}
}
