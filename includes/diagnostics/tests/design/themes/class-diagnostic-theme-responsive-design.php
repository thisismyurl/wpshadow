<?php
/**
 * Theme Responsive Design Diagnostic
 *
 * Verifies theme renders properly on mobile devices.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.5030.1045
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme Responsive Design Class
 *
 * Checks for mobile-friendly meta tags and CSS.
 *
 * @since 1.5030.1045
 */
class Diagnostic_Theme_Responsive_Design extends Diagnostic_Base {

	protected static $slug        = 'theme-responsive-design';
	protected static $title       = 'Theme Responsive Design';
	protected static $description = 'Verifies mobile-friendly design';
	protected static $family      = 'themes';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.5030.1045
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$cache_key = 'wpshadow_responsive_design';
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		$issues = array();

		// Check viewport meta tag.
		$response = wp_remote_get( home_url(), array( 'timeout' => 10 ) );
		
		if ( ! is_wp_error( $response ) ) {
			$html = wp_remote_retrieve_body( $response );
			
			// Check for viewport meta tag.
			if ( ! preg_match( '/<meta[^>]+name=["\']viewport["\']/i', $html ) ) {
				$issues[] = 'Missing viewport meta tag';
			}

			// Check for mobile-unfriendly elements.
			if ( preg_match( '/<table[^>]+width=["\'](?:800|900|1000)/i', $html ) ) {
				$issues[] = 'Fixed-width tables detected (breaks mobile layout)';
			}
		}

		// Check theme's stylesheet for media queries.
		$stylesheet_uri = get_stylesheet_uri();
		$response       = wp_remote_get( $stylesheet_uri, array( 'timeout' => 10 ) );
		
		if ( ! is_wp_error( $response ) ) {
			$css = wp_remote_retrieve_body( $response );
			
			// Count media queries.
			preg_match_all( '/@media[^{]+\{/i', $css, $matches );
			$media_query_count = count( $matches[0] );
			
			if ( $media_query_count < 3 ) {
				$issues[] = sprintf( 'Few media queries found (%d) - may not be responsive', $media_query_count );
			}
		}

		// Check if theme declares responsive support.
		$current_theme = wp_get_theme();
		$theme_tags    = $current_theme->get( 'Tags' );
		
		if ( is_array( $theme_tags ) && ! in_array( 'responsive-layout', $theme_tags, true ) ) {
			$issues[] = 'Theme does not declare responsive-layout support';
		}

		if ( ! empty( $issues ) ) {
			$result = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of issues */
					__( '%d responsive design issues found. Fix to improve mobile experience.', 'wpshadow' ),
					count( $issues )
				),
				'severity'     => 'medium',
				'threat_level' => 45,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/themes-responsive-design',
				'data'         => array(
					'issues'       => $issues,
					'total_issues' => count( $issues ),
				),
			);

			set_transient( $cache_key, $result, 24 * HOUR_IN_SECONDS );
			return $result;
		}

		set_transient( $cache_key, null, 24 * HOUR_IN_SECONDS );
		return null;
	}
}
