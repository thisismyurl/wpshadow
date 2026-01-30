<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }

class Diagnostic_YoastRedirects extends Diagnostic_Base {
	protected static $slug = 'yoast-redirects';
	protected static $title = 'Yoast Redirect Manager';
	protected static $description = 'Validates plugin configuration';
	protected static $family = 'plugins';
	
	public static function check() {
		if ( ! defined( 'WPSEO_PREMIUM_FILE' ) ) { return null; }
		global $wpdb;
		$count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}yoast_seo_redirects" );
		if ( $count === null ) {
			return array(
				'id' => self::$slug,
				'title' => self::$title,
				'description' => __( 'Redirect manager table not found', 'wpshadow' ),
				'severity' => 'medium',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link' => 'https://wpshadow.com/kb/yoast-redirects',
			);
		}
		return null;
	}
}
