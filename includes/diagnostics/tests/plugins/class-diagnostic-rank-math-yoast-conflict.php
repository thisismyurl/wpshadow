<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }

class Diagnostic_RankMathYoastConflict extends Diagnostic_Base {
	protected static $slug = 'rank-math-yoast-conflict';
	protected static $title = 'Rank Math vs Yoast Conflict';
	protected static $description = 'Validates plugin configuration';
	protected static $family = 'plugins';
	
	public static function check() {
		if ( ! class_exists( 'RankMath' ) ) { return null; }
		if ( is_plugin_active( 'wordpress-seo/wp-seo.php' ) || is_plugin_active( 'wordpress-seo-premium/wp-seo-premium.php' ) ) {
			return array(
				'id' => self::$slug,
				'title' => self::$title,
				'description' => __( 'Both Rank Math and Yoast active - conflict detected', 'wpshadow' ),
				'severity' => 'critical',
				'threat_level' => 95,
				'auto_fixable' => false,
				'kb_link' => 'https://wpshadow.com/kb/rank-math-yoast-conflict',
			);
		}
		return null;
	}
}
