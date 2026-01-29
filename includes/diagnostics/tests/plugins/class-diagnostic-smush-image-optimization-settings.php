<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }

class Diagnostic_SmushImageOptimizationSettings extends Diagnostic_Base {
	protected static $slug = 'smush-image-optimization-settings';
	protected static $title = 'Smush Image Optimization';
	protected static $description = 'Validates plugin configuration';
	protected static $family = 'plugins';
	
	public static function check() {
		if ( ! class_exists( 'WP_Smush' ) ) { return null; }
		$settings = get_option( 'wp-smush-settings' );
		if ( empty( $settings['auto'] ) ) {
			return array(
				'id' => self::$slug,
				'title' => self::$title,
				'description' => __( 'Automatic optimization not enabled', 'wpshadow' ),
				'severity' => 'high',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link' => 'https://wpshadow.com/kb/smush-auto',
			);
		}
		return null;
	}
}
