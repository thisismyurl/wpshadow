<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }

class Diagnostic_MonsterinsightsAddons extends Diagnostic_Base {
	protected static $slug = 'monsterinsights-addons';
	protected static $title = 'MonsterInsights Premium Addons';
	protected static $description = 'Validates plugin configuration';
	protected static $family = 'plugins';
	
	public static function check() {
		if ( ! function_exists( 'MonsterInsights' ) || class_exists( 'MonsterInsights_Lite' ) ) { return null; }
		\$addons = array( 'forms', 'media', 'dimensions' );
		\$active = array();
		foreach ( \$addons as \$addon ) {
			if ( get_option( "monsterinsights_addon_{\$addon}_active", false ) ) {
				\$active[] = \$addon;
			}
		}
		if ( empty( \$active ) ) {
			return array(
				'id' => self::\$slug,
				'title' => self::\$title,
				'description' => __( 'No premium addons active', 'wpshadow' ),
				'severity' => 'low',
				'threat_level' => 20,
				'auto_fixable' => false,
				'kb_link' => 'https://wpshadow.com/kb/monsterinsights-addons',
			);
		}
		return null;
	}
}
