<?php
/**
 * Theme Hero Section Issues Diagnostic
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26031.1600
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Diagnostic_Theme_Hero_Section_Issues extends Diagnostic_Base {
	protected static $slug = 'theme-hero-section-issues';
	protected static $title = 'Theme Hero Section Issues';
	protected static $description = 'Detects hero section/slider performance problems';
	protected static $family = 'performance';

	public static function check() {
		$home_url = home_url( '/' );
		$response = wp_remote_get( $home_url );
		
		if ( is_wp_error( $response ) ) {
			return null;
		}

		$html = wp_remote_retrieve_body( $response );
		
		// Check for large hero images or sliders.
		$has_slider = preg_match( '/slider|carousel|swiper|slick/i', $html );
		
		if ( $has_slider ) {
			// Check for unoptimized large images.
			if ( preg_match_all( '/<img[^>]+src=["\']([^"\']+)["\'][^>]*>/i', $html, $matches ) ) {
				foreach ( $matches[1] as $img_url ) {
					if ( preg_match( '/hero|slider|banner/i', $img_url ) ) {
						// Check if image is properly sized.
						if ( ! preg_match( '/\-\d+x\d+\./', $img_url ) ) {
							return array(
								'id'           => self::$slug,
								'title'        => self::$title,
								'description'  => __( 'Hero section contains unoptimized full-size images - may slow page load', 'wpshadow' ),
								'severity'     => 'medium',
								'threat_level' => 45,
								'auto_fixable' => false,
								'kb_link'      => 'https://wpshadow.com/kb/theme-hero-section-issues',
							);
						}
					}
				}
			}
		}
		return null;
	}
}
