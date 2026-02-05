<?php
/**
 * Theme Mobile Menu Treatment
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6031.1600
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Treatment_Theme_Mobile_Menu extends Treatment_Base {
	protected static $slug = 'theme-mobile-menu';
	protected static $title = 'Theme Mobile Menu';
	protected static $description = 'Detects mobile menu functionality issues';
	protected static $family = 'functionality';

	public static function check() {
		$home_url = home_url( '/' );
		$response = wp_remote_get( $home_url );

		if ( is_wp_error( $response ) ) {
			return null;
		}

		$html = wp_remote_retrieve_body( $response );

		// Check for mobile menu implementation.
		$has_mobile_menu = preg_match( '/mobile-menu|hamburger|nav-toggle|menu-toggle/i', $html );

		if ( ! $has_mobile_menu && has_nav_menu( 'primary' ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Theme may not have responsive mobile menu - navigation could be broken on mobile devices', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/theme-mobile-menu',
			);
		}
		return null;
	}
}
