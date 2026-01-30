<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }

class Diagnostic_ElementorProPopupUx extends Diagnostic_Base {
	protected static $slug = 'elementor-pro-popup-ux';
	protected static $title = 'Elementor Pro Popup UX';
	protected static $description = 'Validates plugin configuration';
	protected static $family = 'plugins';
	
	public static function check() {
		if ( ! defined( 'ELEMENTOR_PRO_VERSION' ) ) { return null; }
		global $wpdb;
		$popups = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'elementor_library' AND post_status = 'publish'" );
		if ( $popups > 5 ) {
			return array(
				'id' => self::$slug,
				'title' => self::$title,
				'description' => sprintf( __( '%d active popups - review UX impact', 'wpshadow' ), $popups ),
				'severity' => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link' => 'https://wpshadow.com/kb/elementor-popups',
			);
		}
		return null;
	}
}
