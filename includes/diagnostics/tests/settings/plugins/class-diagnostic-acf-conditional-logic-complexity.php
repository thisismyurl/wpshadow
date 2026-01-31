<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }

class Diagnostic_AcfConditionalLogicComplexity extends Diagnostic_Base {
	protected static $slug = 'acf-conditional-logic-complexity';
	protected static $title = 'ACF Conditional Logic';
	protected static $description = 'Validates plugin configuration';
	protected static $family = 'plugins';
	
	public static function check() {
		if ( ! class_exists( 'ACF' ) ) { return null; }
		$groups = acf_get_field_groups();
		$complex_logic = 0;
		foreach ( $groups as $group ) {
			$fields = acf_get_fields( $group['key'] );
			foreach ( $fields as $field ) {
				if ( ! empty( $field['conditional_logic'] ) && count( $field['conditional_logic'] ) > 5 ) {
					$complex_logic++;
				}
			}
		}
		if ( $complex_logic > 10 ) {
			return array(
				'id' => self::$slug,
				'title' => self::$title,
				'description' => sprintf( __( '%d fields with complex conditional logic', 'wpshadow' ), $complex_logic ),
				'severity' => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link' => 'https://wpshadow.com/kb/acf-conditional-logic',
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
