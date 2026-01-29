<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }

class Diagnostic_GravityFormsSpamProtection extends Diagnostic_Base {
	protected static $slug = 'gravity-forms-spam-protection';
	protected static $title = 'Gravity Forms Spam Protection';
	protected static $description = 'Validates plugin configuration';
	protected static $family = 'plugins';
	
	public static function check() {
		if ( ! class_exists( 'GFForms' ) ) { return null; }
		$akismet = get_option( 'rg_gforms_enable_akismet', false );
		$captcha = get_option( 'rg_gforms_captcha_type', 'none' );
		if ( ! $akismet && $captcha === 'none' ) {
			return array(
				'id' => self::$slug,
				'title' => self::$title,
				'description' => __( 'No spam protection enabled', 'wpshadow' ),
				'severity' => 'critical',
				'threat_level' => 85,
				'auto_fixable' => false,
				'kb_link' => 'https://wpshadow.com/kb/gravity-forms-spam',
			);
		}
		return null;
	}
}
