<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }

class Diagnostic_NinjaFormsSpamProtection extends Diagnostic_Base {
	protected static $slug = 'ninja-forms-spam-protection';
	protected static $title = 'Ninja Forms Spam Protection';
	protected static $description = 'Validates plugin configuration';
	protected static $family = 'plugins';
	
	public static function check() {
		if ( ! class_exists( 'Ninja_Forms' ) ) { return null; }
		$settings = get_option( 'ninja_forms_settings' );
		if ( empty( $settings['recaptcha_site_key'] ) ) {
			return array(
				'id' => self::$slug,
				'title' => self::$title,
				'description' => __( 'reCAPTCHA not configured', 'wpshadow' ),
				'severity' => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link' => 'https://wpshadow.com/kb/ninja-forms-spam',
			);
		}
		return null;
	}
}
