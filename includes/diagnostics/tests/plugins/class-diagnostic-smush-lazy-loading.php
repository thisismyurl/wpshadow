<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }

class Diagnostic_SmushLazyLoading extends Diagnostic_Base {
	protected static $slug = 'smush-lazy-loading';
	protected static $title = 'Smush Lazy Loading';
	protected static $description = 'Validates plugin configuration';
	protected static $family = 'plugins';
	
	public static function check() {
		if ( ! class_exists( 'WP_Smush' ) ) { return null; }
		$lazy = get_option( 'wp-smush-lazy_load' );
		if ( empty( $lazy ) ) {
			return array(
				'id' => self::$slug,
				'title' => self::$title,
				'description' => __( 'Lazy loading not enabled', 'wpshadow' ),
				'severity' => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link' => 'https://wpshadow.com/kb/smush-lazy',
			);
		}
		return null;
	}
}
