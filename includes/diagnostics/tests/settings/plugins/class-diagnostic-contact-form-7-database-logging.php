<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }

class Diagnostic_ContactForm7DatabaseLogging extends Diagnostic_Base {
	protected static $slug = 'contact-form-7-database-logging';
	protected static $title = 'Contact Form 7 Database Logging';
	protected static $description = 'Validates plugin configuration';
	protected static $family = 'plugins';
	
	public static function check() {
		if ( ! defined( 'WPCF7_VERSION' ) ) { return null; }
		if ( ! class_exists( 'WPCF7_ContactFormDB' ) ) {
			return array(
				'id' => self::$slug,
				'title' => self::$title,
				'description' => __( 'Form submissions not logged to database', 'wpshadow' ),
				'severity' => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link' => 'https://wpshadow.com/kb/cf7-logging',
			);
		}
		return null;
	}
}
