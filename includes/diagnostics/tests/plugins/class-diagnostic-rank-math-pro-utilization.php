<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }

class Diagnostic_RankMathProUtilization extends Diagnostic_Base {
	protected static $slug = 'rank-math-pro-utilization';
	protected static $title = 'Rank Math Pro Utilization';
	protected static $description = 'Validates plugin configuration';
	protected static $family = 'plugins';
	
	public static function check() {
		if ( ! class_exists( 'RankMath' ) ) { return null; }
		if ( defined( 'RANK_MATH_PRO_FILE' ) ) {
			$modules = get_option( 'rank_math_modules', array() );
			if ( empty( $modules['local-seo'] ) && empty( $modules['analytics'] ) ) {
				return array(
					'id' => self::$slug,
					'title' => self::$title,
					'description' => __( 'Rank Math Pro active but key modules not enabled', 'wpshadow' ),
					'severity' => 'medium',
					'threat_level' => 40,
					'auto_fixable' => false,
					'kb_link' => 'https://wpshadow.com/kb/rank-math-pro',
				);
			}
		}
		return null;
	}
}
