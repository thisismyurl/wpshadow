<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }

class Diagnostic_ContactForm7PerformanceOptimization extends Diagnostic_Base {
	protected static $slug = 'contact-form-7-performance-optimization';
	protected static $title = 'Contact Form 7 Performance';
	protected static $description = 'Validates plugin configuration';
	protected static $family = 'plugins';
	
	public static function check() {
		if ( ! defined( 'WPCF7_VERSION' ) ) { return null; }
		global $wpdb;
		$forms = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'wpcf7_contact_form'" );
		if ( $forms > 20 ) {
			return array(
				'id' => self::$slug,
				'title' => self::$title,
				'description' => sprintf( __( '%d forms may impact performance', 'wpshadow' ), $forms ),
				'severity' => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link' => 'https://wpshadow.com/kb/cf7-performance',
			);
		}
		return null;
	}
}
