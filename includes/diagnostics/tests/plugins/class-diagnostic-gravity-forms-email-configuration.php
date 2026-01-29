<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }

class Diagnostic_GravityFormsEmailConfiguration extends Diagnostic_Base {
	protected static $slug = 'gravity-forms-email-configuration';
	protected static $title = 'Gravity Forms Email Config';
	protected static $description = 'Validates plugin configuration';
	protected static $family = 'plugins';
	
	public static function check() {
		if ( ! class_exists( 'GFForms' ) ) { return null; }
		$from_email = get_option( 'rg_gforms_default_from_email', '' );
		if ( empty( $from_email ) ) {
			return array(
				'id' => self::$slug,
				'title' => self::$title,
				'description' => __( 'Default from email not set', 'wpshadow' ),
				'severity' => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link' => 'https://wpshadow.com/kb/gravity-forms-email',
			);
		}
		return null;
	}
}
