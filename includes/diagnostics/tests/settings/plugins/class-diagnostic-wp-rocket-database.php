<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }

class Diagnostic_WpRocketDatabase extends Diagnostic_Base {
	protected static $slug = 'wp-rocket-database';
	protected static $title = 'WP Rocket Database Optimization';
	protected static $description = 'Validates plugin configuration';
	protected static $family = 'plugins';
	
	public static function check() {
		if ( ! function_exists( 'rocket_direct_filesystem' ) ) { return null; }
		$settings = get_option( 'wp_rocket_settings', array() );
		if ( empty( $settings['database_revisions'] ) && empty( $settings['database_auto_drafts'] ) ) {
			return array(
				'id' => self::$slug,
				'title' => self::$title,
				'description' => __( 'Database optimization not configured', 'wpshadow' ),
				'severity' => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link' => 'https://wpshadow.com/kb/wp-rocket-database',
			);
		}
		return null;
	}
}
