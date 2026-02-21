<?php
/**
 * Mobile Anchor Link Performance Treatment
 *
 * Validates that anchor links (jump links) work smoothly on mobile with
 * proper scroll behavior and offset for fixed headers.
 *
 * @package    WPShadow
 * @subpackage Treatments\Mobile
 * @since      1.602.1250
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile Anchor Link Performance Treatment Class
 *
 * Checks anchor link implementation for mobile smooth scrolling, proper
 * offset calculations, and accessibility.
 *
 * @since 1.602.1250
 */
class Treatment_Mobile_Anchor_Link_Performance extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-anchor-link-performance';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Anchor Link Performance';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Validates anchor links work smoothly on mobile with proper scroll behavior and fixed header offset';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'mobile';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.602.1250
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Mobile_Anchor_Link_Performance' );
	}

	/**
	 * Check anchor link implementation.
	 *
	 * @since  1.602.1250
	 * @return array Check results.
	 */
	private static function check_anchor_links() {
		$issues = array();

		// Capture homepage to check for anchor links and fixed header.
		$html = self::capture_page_html( home_url( '/' ) );
		if ( empty( $html ) ) {
			return array( 'issues' => $issues );
		}

		// Check for fixed header.
		$has_fixed_header = preg_match( '/position:\s*fixed|position:\s*sticky/i', $html );

		// Check for anchor links.
		$has_anchor_links = preg_match_all( '/<a[^>]*href=["\']#[^"\']+["\']/', $html, $anchor_matches );

		if ( ! $has_anchor_links ) {
			// No anchor links, no issues to report.
			return array( 'issues' => $issues );
		}

		// Check for smooth scroll.
		$has_smooth_scroll = preg_match( '/scroll-behavior:\s*smooth/i', $html );

		if ( ! $has_smooth_scroll ) {
			$issues[] = array(
				'issue_type'  => 'no_smooth_scroll',
				'severity'    => 'low',
				'description' => 'Anchor links detected but smooth scrolling not enabled',
				'impact'      => 'Instant jumps are jarring on mobile',
			);
		}

		// Check for scroll offset with fixed header.
		if ( $has_fixed_header ) {
			$has_scroll_offset = preg_match( '/scroll-padding-top|scroll-margin-top/i', $html );

			if ( ! $has_scroll_offset ) {
				$issues[] = array(
					'issue_type'  => 'fixed_header_no_offset',
					'severity'    => 'medium',
					'description' => 'Fixed header detected but no scroll offset configured',
					'impact'      => 'Anchor links will hide content under fixed header',
				);
			}
		}

		// Check for JavaScript smooth scroll implementation.
		$has_js_scroll = preg_match( '/scrollTo|scrollIntoView|smooth.*scroll/i', $html );

		return array( 'issues' => $issues );
	}

	/**
	 * Capture page HTML.
	 *
	 * @since  1.602.1250
	 * @param  string $url Page URL.
	 * @return string HTML content.
	 */
	private static function capture_page_html( $url ) {
		$response = wp_remote_get(
			$url,
			array(
				'timeout'    => 10,
				'user-agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_0 like Mac OS X)',
			)
		);

		if ( is_wp_error( $response ) ) {
			return '';
		}

		return wp_remote_retrieve_body( $response );
	}
}
