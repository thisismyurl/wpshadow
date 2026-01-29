<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }

class Diagnostic_UpdraftplusPremiumFeatures extends Diagnostic_Base {
	protected static $slug = 'updraftplus-premium-features';
	protected static $title = 'UpdraftPlus Premium Features';
	protected static $description = 'Validates plugin configuration';
	protected static $family = 'plugins';
	
	public static function check() {
		if ( ! class_exists( 'UpdraftPlus' ) ) { return null; }
		if ( defined( 'UPDRAFTPLUS_PREMIUM_VERSION' ) ) {
			$migrator = UpdraftPlus_Options::get_updraft_option( 'updraft_migrator_configured' );
			if ( empty( $migrator ) ) {
				return array(
					'id' => self::$slug,
					'title' => self::$title,
					'description' => __( 'Premium features not fully utilized', 'wpshadow' ),
					'severity' => 'medium',
					'threat_level' => 35,
					'auto_fixable' => false,
					'kb_link' => 'https://wpshadow.com/kb/updraftplus-premium',
				);
			}
		}
		return null;
	}
}
