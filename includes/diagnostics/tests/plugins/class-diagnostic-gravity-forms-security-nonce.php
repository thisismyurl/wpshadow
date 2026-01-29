<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }

class Diagnostic_GravityFormsSecurityNonce extends Diagnostic_Base {
	protected static $slug = 'gravity-forms-security-nonce';
	protected static $title = 'Gravity Forms Security Nonce';
	protected static $description = 'Validates plugin configuration';
	protected static $family = 'plugins';
	
	public static function check() {
		if ( ! class_exists( 'GFForms' ) ) { return null; }
		$enable_nonce = get_option( 'rg_gforms_enable_nonce', true );
		if ( ! $enable_nonce ) {
			return array(
				'id' => self::$slug,
				'title' => self::$title,
				'description' => __( 'Form nonce protection disabled', 'wpshadow' ),
				'severity' => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link' => 'https://wpshadow.com/kb/gravity-forms-security',
			);
		}
		return null;
	}
}
