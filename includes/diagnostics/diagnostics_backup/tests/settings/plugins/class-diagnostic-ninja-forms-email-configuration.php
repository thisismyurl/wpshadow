<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }

class Diagnostic_NinjaFormsEmailConfiguration extends Diagnostic_Base {
	protected static $slug = 'ninja-forms-email-configuration';
	protected static $title = 'Ninja Forms Email Configuration';
	protected static $description = 'Validates plugin configuration';
	protected static $family = 'plugins';
	
	public static function check() {
		if ( ! class_exists( 'Ninja_Forms' ) ) { return null; }
		$forms = Ninja_Forms()->form()->get_forms();
		foreach ( $forms as $form ) {
			$actions = $form->get_actions();
			if ( empty( $actions ) ) {
				return array(
					'id' => self::$slug,
					'title' => self::$title,
					'description' => __( 'Form without email notifications', 'wpshadow' ),
					'severity' => 'medium',
					'threat_level' => 50,
					'auto_fixable' => false,
					'kb_link' => 'https://wpshadow.com/kb/ninja-forms-email',
				);
			}
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
