<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }

class Diagnostic_ElementorProFormSecurity extends Diagnostic_Base {
	protected static $slug = 'elementor-pro-form-security';
	protected static $title = 'Elementor Pro Form Security';
	protected static $description = 'Validates plugin configuration';
	protected static $family = 'plugins';
	
	public static function check() {
		if ( ! defined( 'ELEMENTOR_PRO_VERSION' ) ) { return null; }
		$forms = get_posts( array( 'post_type' => 'elementor_library', 'meta_key' => '_elementor_template_type', 'meta_value' => 'form' ) );
		if ( ! empty( $forms ) ) {
			$recaptcha = get_option( 'elementor_pro_recaptcha_site_key', '' );
			if ( empty( $recaptcha ) ) {
				return array(
					'id' => self::$slug,
					'title' => self::$title,
					'description' => __( 'Forms active without reCAPTCHA', 'wpshadow' ),
					'severity' => 'high',
					'threat_level' => 65,
					'auto_fixable' => false,
					'kb_link' => 'https://wpshadow.com/kb/elementor-form-security',
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
