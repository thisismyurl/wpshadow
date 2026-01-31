<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }

class Diagnostic_GravityFormsAddons extends Diagnostic_Base {
	protected static $slug = 'gravity-forms-addons';
	protected static $title = 'Gravity Forms Addons';
	protected static $description = 'Validates plugin configuration';
	protected static $family = 'plugins';
	
	public static function check() {
		if ( ! class_exists( 'GFForms' ) ) { return null; }
		$addons = array( 'gravityformswebapi', 'gravityformsquiz', 'gravityformssurvey' );
		$active = array();
		foreach ( $addons as $addon ) {
			if ( class_exists( $addon ) ) {
				$active[] = $addon;
			}
		}
		if ( empty( $active ) ) {
			return array(
				'id' => self::$slug,
				'title' => self::$title,
				'description' => __( 'No premium addons active', 'wpshadow' ),
				'severity' => 'low',
				'threat_level' => 20,
				'auto_fixable' => false,
				'kb_link' => 'https://wpshadow.com/kb/gravity-forms-addons',
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
