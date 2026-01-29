<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }

class Diagnostic_MonsterinsightsPopularPosts extends Diagnostic_Base {
	protected static $slug = 'monsterinsights-popular-posts';
	protected static $title = 'MonsterInsights Popular Posts';
	protected static $description = 'Validates plugin configuration';
	protected static $family = 'plugins';
	
	public static function check() {
		if ( ! function_exists( 'MonsterInsights' ) ) { return null; }
		$popular = get_option( 'monsterinsights_popular_posts_enabled', false );
		if ( ! $popular ) {
			return array(
				'id' => self::$slug,
				'title' => self::$title,
				'description' => __( 'Popular Posts disabled', 'wpshadow' ),
				'severity' => 'low',
				'threat_level' => 15,
				'auto_fixable' => false,
				'kb_link' => 'https://wpshadow.com/kb/popular-posts',
			);
		}
		return null;
	}
}
