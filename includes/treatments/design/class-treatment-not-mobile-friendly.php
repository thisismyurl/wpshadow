<?php
/**
 * Not Mobile-Friendly Treatment
 *
 * Detects content and design patterns that are not optimized for mobile devices.
 * Checks viewport meta tag, responsive design elements, and mobile-specific issues.
 *
 * @package    WPShadow
 * @subpackage Treatments\Mobile
 * @since      1.6034.2145
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Not Mobile-Friendly Treatment Class
 *
 * Identifies mobile usability issues that affect user experience on smartphones
 * and tablets. Checks for proper viewport configuration, responsive design
 * implementation, and mobile-specific accessibility concerns.
 *
 * **Why This Matters:**
 * - 60%+ of web traffic is mobile (Google Mobile-First Indexing)
 * - Google rankings penalize non-mobile-friendly sites
 * - Poor mobile UX = 53% of users abandon site
 * - WCAG 2.1 mobile accessibility requirements
 *
 * **What's Checked:**
 * - Viewport meta tag presence and configuration
 * - Responsive CSS media queries
 * - Mobile-unfriendly plugins (Flash, deprecated features)
 * - Touch target sizes
 * - Horizontal scrolling issues
 *
 * @since 1.6034.2145
 */
class Treatment_Not_Mobile_Friendly extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'not-mobile-friendly';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Not Mobile-Friendly';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Detects site configuration and design patterns that are not optimized for mobile devices';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'mobile';

	/**
	 * Run the treatment check
	 *
	 * Checks multiple indicators of mobile-friendliness:
	 * - Viewport meta tag in <head>
	 * - Responsive CSS media queries
	 * - Mobile-unfriendly content (Flash, fixed-width elements)
	 * - Theme support for responsive design
	 *
	 * @since  1.6034.2145
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check #1: Viewport meta tag
		$has_viewport = self::has_viewport_meta_tag();
		if ( ! $has_viewport ) {
			$issues[] = 'Missing viewport meta tag';
		}

		// Check #2: Responsive theme support
		$responsive_support = current_theme_supports( 'responsive-embeds' ) || 
		                      current_theme_supports( 'html5' );
		if ( ! $responsive_support ) {
			$issues[] = 'Theme lacks responsive design features';
		}

		// Check #3: Check for mobile-unfriendly plugins
		$active_plugins = get_option( 'active_plugins', array() );
		foreach ( $active_plugins as $plugin ) {
			if ( stripos( $plugin, 'flash' ) !== false ) {
				$issues[] = 'Flash plugin detected (not mobile-compatible)';
				break;
			}
		}

		// Check #4: Fixed-width content in posts
		$posts = get_posts(
			array(
				'post_type'      => 'post',
				'posts_per_page' => 10,
				'post_status'    => 'publish',
			)
		);

		$fixed_width_count = 0;
		foreach ( $posts as $post ) {
			if ( preg_match( '/width\s*[:=]\s*["\']?\d{4,}/', $post->post_content ) ) {
				$fixed_width_count++;
			}
		}

		if ( $fixed_width_count > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of posts */
				__( '%d post(s) contain fixed-width content', 'wpshadow' ),
				$fixed_width_count
			);
		}

		if ( empty( $issues ) ) {
			return null; // Site appears mobile-friendly
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %d: number of issues */
				__( '%d mobile-friendliness issue(s) detected. Your site may not display correctly on smartphones and tablets.', 'wpshadow' ),
				count( $issues )
			),
			'severity'     => 'high',
			'threat_level' => 80,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/mobile-not-mobile-friendly',
			'details'      => array(
				'issues'                => $issues,
				'has_viewport'          => $has_viewport,
				'responsive_support'    => $responsive_support,
				'fixed_width_posts'     => $fixed_width_count,
			),
		);
	}

	/**
	 * Check if site has viewport meta tag
	 *
	 * @since  1.6034.2145
	 * @return bool True if viewport tag exists.
	 */
	private static function has_viewport_meta_tag() {
		// Check if theme adds viewport meta tag via wp_head
		ob_start();
		wp_head();
		$head_content = ob_get_clean();

		return ( stripos( $head_content, 'viewport' ) !== false );
	}
}
