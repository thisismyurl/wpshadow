<?php
/**
 * Theme Social Media Icons Diagnostic
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Diagnostic_Theme_Social_Media_Icons extends Diagnostic_Base {
	protected static $slug = 'theme-social-media-icons';
	protected static $title = 'Theme Social Media Icons';
	protected static $description = 'Checks if social media icons load properly';
	protected static $family = 'functionality';

	public static function check() {
		$home_url = home_url( '/' );
		$response = wp_remote_get( $home_url );

		if ( is_wp_error( $response ) ) {
			return null;
		}

		$html = wp_remote_retrieve_body( $response );

		// Check for social icon CSS/JS loading errors.
		$has_social_icons = preg_match( '/social|facebook|twitter|instagram|linkedin/i', $html );

		if ( $has_social_icons ) {
			// Check for broken icon font loading.
			$has_font_awesome = strpos( $html, 'font-awesome' ) !== false || strpos( $html, 'fontawesome' ) !== false;
			$has_icon_font = strpos( $html, 'icon-font' ) !== false;

			if ( ( $has_font_awesome || $has_icon_font ) && preg_match( '/404.*\.(woff|ttf|eot)/i', $html ) ) {
				return array(
					'id'           => self::$slug,
					'title'        => self::$title,
					'description'  => __( 'Social media icon fonts may not be loading correctly - check for 404 errors', 'wpshadow' ),
					'severity'     => 'low',
					'threat_level' => 30,
					'auto_fixable' => false,
					'kb_link'      => 'https://wpshadow.com/kb/theme-social-media-icons?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				);
			}
		}
		return null;
	}
}
