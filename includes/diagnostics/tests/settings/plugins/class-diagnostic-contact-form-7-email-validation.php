<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }

class Diagnostic_ContactForm7EmailValidation extends Diagnostic_Base {
	protected static $slug = 'contact-form-7-email-validation';
	protected static $title = 'Contact Form 7 Email Validation';
	protected static $description = 'Validates plugin configuration';
	protected static $family = 'plugins';
	
	public static function check() {
		if ( ! defined( 'WPCF7_VERSION' ) ) { return null; }
		$mail = get_option( 'wpcf7_mail' );
		if ( empty( $mail['recipient'] ) || false === strpos( $mail['recipient'], '@' ) ) {
			return array(
				'id' => self::$slug,
				'title' => self::$title,
				'description' => __( 'Invalid email recipient configured', 'wpshadow' ),
				'severity' => 'high',
				'threat_level' => 65,
				'auto_fixable' => false,
				'kb_link' => 'https://wpshadow.com/kb/cf7-email',
			);
		}
		return null;
	}
}
