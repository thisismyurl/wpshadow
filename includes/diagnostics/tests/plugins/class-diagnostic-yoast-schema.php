<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }

class Diagnostic_YoastSchema extends Diagnostic_Base {
	protected static $slug = 'yoast-schema';
	protected static $title = 'Yoast Schema Markup';
	protected static $description = 'Validates plugin configuration';
	protected static $family = 'plugins';
	
	public static function check() {
		if ( ! class_exists( 'WPSEO_Options' ) ) { return null; }
		$titles = get_option( 'wpseo_titles', array() );
		$has_schema = ! empty( $titles['company_name'] ) || ! empty( $titles['person_name'] );
		if ( ! $has_schema ) {
			return array(
				'id' => self::$slug,
				'title' => self::$title,
				'description' => __( 'Schema markup not configured', 'wpshadow' ),
				'severity' => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link' => 'https://wpshadow.com/kb/yoast-schema',
			);
		}
		return null;
	}
}
