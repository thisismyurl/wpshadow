<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }

class Diagnostic_LitespeedOptimization extends Diagnostic_Base {
	protected static $slug = 'litespeed-optimization';
	protected static $title = 'LiteSpeed Cache Optimization';
	protected static $description = 'Validates plugin configuration';
	protected static $family = 'plugins';
	
	public static function check() {
		if ( ! defined( 'LSCWP_V' ) ) { return null; }
		$conf = get_option( 'litespeed.conf', array() );
		if ( empty( $conf['cache-browser'] ) || empty( $conf['optm-css_minify'] ) ) {
			return array(
				'id' => self::$slug,
				'title' => self::$title,
				'description' => __( 'Key optimization features disabled', 'wpshadow' ),
				'severity' => 'high',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link' => 'https://wpshadow.com/kb/litespeed-optimization',
			);
		}
		return null;
	}
}
