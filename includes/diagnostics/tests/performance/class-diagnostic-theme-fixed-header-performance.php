<?php
/**
 * Theme Fixed Header Performance Diagnostic
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

class Diagnostic_Theme_Fixed_Header_Performance extends Diagnostic_Base {
	protected static $slug = 'theme-fixed-header-performance';
	protected static $title = 'Theme Fixed Header Performance';
	protected static $description = 'Checks if fixed header/sticky elements impact performance';
	protected static $family = 'performance';

	public static function check() {
		$home_url = home_url( '/' );
		$response = wp_remote_get( $home_url );

		if ( is_wp_error( $response ) ) {
			return null;
		}

		$html = wp_remote_retrieve_body( $response );

		// Check for fixed/sticky header.
		$has_fixed = preg_match( '/position:\s*fixed|position:\s*sticky/i', $html ) ||
		             preg_match( '/fixed-header|sticky-header/i', $html );

		if ( $has_fixed ) {
			// Check if it contains heavy elements.
			$has_heavy_content = preg_match( '/<img[^>]+>.*?fixed|sticky.*?<img[^>]+>/is', $html ) ||
			                     preg_match( '/mega-menu.*?fixed|sticky.*?mega-menu/is', $html );

			if ( $has_heavy_content ) {
				return array(
					'id'           => self::$slug,
					'title'        => self::$title,
					'description'  => __( 'Fixed/sticky header contains images or mega menu - may cause scroll jank', 'wpshadow' ),
					'severity'     => 'medium',
					'threat_level' => 40,
					'auto_fixable' => false,
					'kb_link'      => 'https://wpshadow.com/kb/theme-fixed-header-performance',
				);
			}
		}
		return null;
	}
}
