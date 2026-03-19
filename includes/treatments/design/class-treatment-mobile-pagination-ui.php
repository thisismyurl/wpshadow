<?php
/**
 * Mobile Pagination UI Treatment
 *
 * Validates that pagination controls are mobile-friendly with proper
 * touch targets, loading patterns, and accessibility.
 *
 * @package    WPShadow
 * @subpackage Treatments\Mobile
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile Pagination UI Treatment Class
 *
 * Checks pagination implementation for mobile usability including touch target
 * sizing, loading patterns, and accessibility.
 *
 * @since 1.6093.1200
 */
class Treatment_Mobile_Pagination_UI extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-pagination-ui';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Pagination UI';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Validates pagination controls are mobile-friendly with proper touch targets and loading patterns';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'mobile';

	/**
	 * Run the treatment check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Mobile_Pagination_UI' );
	}

	/**
	 * Check pagination implementation.
	 *
	 * @since 1.6093.1200
	 * @return array Check results.
	 */
	private static function check_pagination() {
		$issues = array();

		// Get blog page or homepage.
		$posts_page = get_option( 'page_for_posts' );
		$test_url   = $posts_page ? get_permalink( $posts_page ) : home_url( '/' );

		$html = self::capture_page_html( $test_url );
		if ( empty( $html ) ) {
			return array( 'issues' => $issues );
		}

		// Check for pagination.
		$has_pagination = preg_match( '/<nav[^>]*class=["\'][^"\']*paginat/i', $html ) ||
						  preg_match( '/class=["\'][^"\']*page-numbers[^"\']*["\']/', $html );

		if ( ! $has_pagination ) {
			// Check if site has enough posts for pagination.
			$post_count = wp_count_posts( 'post' );
			$posts_per_page = get_option( 'posts_per_page', 10 );
			
			if ( $post_count->publish > $posts_per_page ) {
				$issues[] = array(
					'issue_type'  => 'no_pagination',
					'severity'    => 'low',
					'description' => sprintf(
						'Site has %d posts but no pagination detected',
						$post_count->publish
					),
				);
			}
			
			return array( 'issues' => $issues );
		}

		// Check for infinite scroll or Load More.
		$has_infinite_scroll = preg_match( '/infinite[-_]scroll|load[-_]more/i', $html );

		if ( ! $has_infinite_scroll ) {
			$issues[] = array(
				'issue_type'  => 'traditional_pagination',
				'severity'    => 'low',
				'description' => 'Using traditional numbered pagination instead of Load More/infinite scroll',
				'impact'      => 'Suboptimal mobile UX, requires page reload',
			);
		}

		// Check link sizes (if we can detect inline styles).
		if ( preg_match( '/\.pagination.*?font-size:\s*([0-9]+)px/is', $html, $size_match ) ) {
			$font_size = (int) $size_match[1];
			if ( $font_size < 14 ) {
				$issues[] = array(
					'issue_type'  => 'small_links',
					'severity'    => 'medium',
					'description' => sprintf( 'Pagination links font-size is %dpx (should be 16px+ for mobile)', $font_size ),
				);
			}
		}

		// Check for accessibility (role="navigation" or <nav>).
		if ( ! preg_match( '/<nav[^>]*(?:role=["\']navigation["\']|aria-label=["\']pagination["\'])/i', $html ) ) {
			$issues[] = array(
				'issue_type'  => 'no_aria',
				'severity'    => 'low',
				'description' => 'Pagination missing proper ARIA labels or <nav> element',
				'impact'      => 'Accessibility issue for screen readers',
			);
		}

		return array( 'issues' => $issues );
	}

	/**
	 * Capture page HTML.
	 *
	 * @since 1.6093.1200
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
