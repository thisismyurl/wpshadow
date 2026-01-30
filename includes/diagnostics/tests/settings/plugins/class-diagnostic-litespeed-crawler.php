<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }

class Diagnostic_LitespeedCrawler extends Diagnostic_Base {
	protected static $slug = 'litespeed-crawler';
	protected static $title = 'LiteSpeed Cache Crawler';
	protected static $description = 'Validates plugin configuration';
	protected static $family = 'plugins';
	
	public static function check() {
		if ( ! defined( 'LSCWP_V' ) ) { return null; }
		$conf = get_option( 'litespeed.conf', array() );
		if ( empty( $conf['crawler'] ) ) {
			return array(
				'id' => self::$slug,
				'title' => self::$title,
				'description' => __( 'Cache crawler not enabled', 'wpshadow' ),
				'severity' => 'medium',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link' => 'https://wpshadow.com/kb/litespeed-crawler',
			);
		}
		return null;
	}
}
