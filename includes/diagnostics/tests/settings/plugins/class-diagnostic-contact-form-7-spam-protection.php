<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }

class Diagnostic_ContactForm7SpamProtection extends Diagnostic_Base {
	protected static $slug = 'contact-form-7-spam-protection';
	protected static $title = 'Contact Form 7 Spam Protection';
	protected static $description = 'Validates plugin configuration';
	protected static $family = 'plugins';
	
	public static function check() {
		if ( ! defined( 'WPCF7_VERSION' ) ) { return null; }
		$recaptcha = get_option( 'wpcf7' );
		if ( empty( $recaptcha['recaptcha'] ) ) {
			return array(
				'id' => self::$slug,
				'title' => self::$title,
				'description' => __( 'No spam protection configured', 'wpshadow' ),
				'severity' => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link' => 'https://wpshadow.com/kb/cf7-spam',
			);
		}
		return null;
	}
}
