<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }

class Diagnostic_LitespeedEsi extends Diagnostic_Base {
	protected static $slug = 'litespeed-esi';
	protected static $title = 'LiteSpeed ESI Configuration';
	protected static $description = 'Validates plugin configuration';
	protected static $family = 'plugins';
	
	public static function check() {
		if ( ! defined( 'LSCWP_V' ) ) { return null; }
		$conf = get_option( 'litespeed.conf', array() );
		if ( empty( $conf['esi'] ) ) {
			return array(
				'id' => self::$slug,
				'title' => self::$title,
				'description' => __( 'ESI not enabled for dynamic content', 'wpshadow' ),
				'severity' => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link' => 'https://wpshadow.com/kb/litespeed-esi',
			);
		}
		return null;
	}
}
