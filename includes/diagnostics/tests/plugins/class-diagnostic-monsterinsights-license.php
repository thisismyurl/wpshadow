<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }

class Diagnostic_MonsterinsightsLicense extends Diagnostic_Base {
	protected static $slug = 'monsterinsights-license';
	protected static $title = 'MonsterInsights License';
	protected static $description = 'Validates plugin configuration';
	protected static $family = 'plugins';
	
	public static function check() {
		if ( ! function_exists( 'MonsterInsights' ) ) { return null; }
		$license = get_option( 'monsterinsights_license_key', '' );
		if ( empty( $license ) && ! class_exists( 'MonsterInsights_Lite' ) ) {
			return array(
				'id' => self::$slug,
				'title' => self::$title,
				'description' => __( 'Pro license not configured', 'wpshadow' ),
				'severity' => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link' => 'https://wpshadow.com/kb/monsterinsights-license',
			);
		}
		return null;
	}
}
