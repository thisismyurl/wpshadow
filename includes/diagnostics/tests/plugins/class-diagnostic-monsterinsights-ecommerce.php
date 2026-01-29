<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }

class Diagnostic_MonsterinsightsEcommerce extends Diagnostic_Base {
	protected static $slug = 'monsterinsights-ecommerce';
	protected static $title = 'MonsterInsights eCommerce';
	protected static $description = 'Validates plugin configuration';
	protected static $family = 'plugins';
	
	public static function check() {
		if ( ! function_exists( 'MonsterInsights' ) ) { return null; }
		if ( ! class_exists( 'WooCommerce' ) ) { return null; }
		$ecommerce = get_option( 'monsterinsights_ecommerce', '' );
		if ( empty( $ecommerce ) ) {
			return array(
				'id' => self::$slug,
				'title' => self::$title,
				'description' => __( 'eCommerce tracking not enabled', 'wpshadow' ),
				'severity' => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link' => 'https://wpshadow.com/kb/monsterinsights-ecommerce',
			);
		}
		return null;
	}
}
