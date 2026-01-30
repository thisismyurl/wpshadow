<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }

class Diagnostic_RankMathEssentialSettings extends Diagnostic_Base {
	protected static $slug = 'rank-math-essential-settings';
	protected static $title = 'Rank Math Essential Settings';
	protected static $description = 'Validates plugin configuration';
	protected static $family = 'plugins';
	
	public static function check() {
		if ( ! class_exists( 'RankMath' ) ) { return null; }
		$general = get_option( 'rank-math-options-general', array() );
		if ( empty( $general['sitemap'] ) || empty( $general['breadcrumbs'] ) ) {
			return array(
				'id' => self::$slug,
				'title' => self::$title,
				'description' => __( 'Essential SEO features not enabled', 'wpshadow' ),
				'severity' => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link' => 'https://wpshadow.com/kb/rank-math-settings',
			);
		}
		return null;
	}
}
