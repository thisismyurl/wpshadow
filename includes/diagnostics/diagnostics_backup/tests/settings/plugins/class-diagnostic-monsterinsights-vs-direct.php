<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }

class Diagnostic_MonsterinsightsVsDirect extends Diagnostic_Base {
	protected static $slug = 'monsterinsights-vs-direct';
	protected static $title = 'MonsterInsights vs Direct GA';
	protected static $description = 'Validates plugin configuration';
	protected static $family = 'plugins';
	
	public static function check() {
		global \$wp_scripts;
		\$has_direct_ga = false;
		if ( isset( \$wp_scripts->registered ) ) {
			foreach ( \$wp_scripts->registered as \$script ) {
				if ( strpos( \$script->src, 'google-analytics.com/analytics.js' ) !== false ||
				     strpos( \$script->src, 'googletagmanager.com/gtag/js' ) !== false ) {
					\$has_direct_ga = true;
					break;
				}
			}
		}
		if ( \$has_direct_ga && ( function_exists( 'MonsterInsights' ) || class_exists( 'MonsterInsights_Lite' ) ) ) {
			return array(
				'id' => self::\$slug,
				'title' => self::\$title,
				'description' => __( 'Both MonsterInsights and direct GA detected', 'wpshadow' ),
				'severity' => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link' => 'https://wpshadow.com/kb/double-tracking',
			);
		}

		// Plugin integration checks
		if ( ! function_exists( 'get_plugins' ) ) {
			$issues[] = __( 'Plugin listing not available', 'wpshadow' );
		}
		if ( ! function_exists( 'is_plugin_active' ) ) {
			$issues[] = __( 'Plugin status check unavailable', 'wpshadow' );
		}
		// Verify integration point
		if ( ! function_exists( 'do_action' ) ) {
			$issues[] = __( 'Action hooks unavailable', 'wpshadow' );
		}
		return null;
	}
}
