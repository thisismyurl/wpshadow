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
		if ( ! function_exists( 'MonsterInsights' ) ) { if ( isset( $issues ) && ! empty( $issues ) ) {
		return array(
			'id' => self::$slug,
			'title' => self::$title,
			'description' => sprintf(
				__( 'Found %d issues', 'wpshadow' ),
				count( $issues )
			),
			'severity' => 'medium',
			'threat_level' => 45,
			'auto_fixable' => false,
			'kb_link' => 'https://wpshadow.com/kb/monsterinsights-popular-posts',
		);
	}
	return null; }
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
		
	if ( ! (function_exists( "is_plugin_active" )) ) {
		if ( ! isset( $issues ) ) {
			$issues = array();
		}
		$issues[] = __( 'Plugin active', 'wpshadow' );
	}

	if ( ! (! empty( get_option( "monsterinsights_popular_posts_settings" ) )) ) {
		if ( ! isset( $issues ) ) {
			$issues = array();
		}
		$issues[] = __( 'Settings available', 'wpshadow' );
	}
	if ( isset( $issues ) && ! empty( $issues ) ) {
		return array(
			'id' => self::$slug,
			'title' => self::$title,
			'description' => sprintf(
				__( 'Found %d issues', 'wpshadow' ),
				count( $issues )
			),
			'severity' => 'medium',
			'threat_level' => 45,
			'auto_fixable' => false,
			'kb_link' => 'https://wpshadow.com/kb/monsterinsights-popular-posts',
		);
	}
	return null;
	}
}
