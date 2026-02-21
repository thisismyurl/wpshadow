<?php
/**
 * Mobile Reduce Motion Preference
 *
 * Respects prefers-reduced-motion media query and disables animations.
 *
 * @package    WPShadow
 * @subpackage Treatments\Accessibility
 * @since      1.602.1430
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Treatments\Helpers\Treatment_HTML_Helper;
use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mobile Reduce Motion Preference
 *
 * Validates that animations and transitions respect the
 * prefers-reduced-motion media query for accessibility.
 * WCAG 2.3.3 Level AAA requirement.
 *
 * @since 1.602.1430
 */
class Treatment_Mobile_Reduce_Motion extends Treatment_Base {

	/**
	 * The treatment slug.
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-reduce-motion';

	/**
	 * The treatment title.
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Reduce Motion Preference';

	/**
	 * The treatment description.
	 *
	 * @var string
	 */
	protected static $description = 'Validates animations respect prefers-reduced-motion';

	/**
	 * The treatment family.
	 *
	 * @var string
	 */
	protected static $family = 'accessibility';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.602.1430
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Mobile_Reduce_Motion' );
	}

	/**
	 * Find motion-related accessibility issues.
	 *
	 * @since  1.602.1430
	 * @return array Issues found.
	 */
	private static function find_motion_issues(): array {
		$html = self::get_page_html();
		if ( ! $html ) {
			return array();
		}

		$issues = array(
			'animation_count' => 0,
			'has_media_query' => false,
			'effects'         => array(),
		);

		// Extract all CSS
		preg_match_all( '/<style[^>]*>(.*?)<\/style>/is', $html, $style_matches );
		$css = implode( "\n", $style_matches[1] ?? array() );

		// Check for prefers-reduced-motion media query
		$issues['has_media_query'] = (bool) preg_match(
			'/@media\s*\(?[^)]*prefers-reduced-motion/i',
			$css
		);

		// Check for animations
		$animation_count = preg_match_all( '/animation\s*:/i', $css, $matches );
		$issues['animation_count'] = (int) $animation_count;

		// Check for transitions
		$transition_count = preg_match_all( '/transition\s*:/i', $css, $matches );
		$issues['animation_count'] += (int) $transition_count;

		// Find specific animation types
		if ( preg_match_all( '/@keyframes\s+(\w+)/i', $css, $animation_names ) ) {
			$issues['effects'] = array_slice( $animation_names[1], 0, 5 );
		}

		// Check for parallax effect (scroll-based animation)
		if ( preg_match( '/transform.*translate|scroll.*event|parallax/i', $html ) ) {
			$issues['animation_count']++;
			$issues['effects'][] = 'Parallax scroll effect';
		}

		// Check for AOS (Animate On Scroll) library
		if ( preg_match( '/aos\.js|data-aos/i', $html ) ) {
			$issues['animation_count']++;
			$issues['effects'][] = 'Animate On Scroll (AOS)';
		}

		return $issues;
	}

	/**
	 * Get page HTML for analysis.
	 *
	 * @since  1.602.1430
	 * @return string|null HTML content.
	 */
	private static function get_page_html(): ?string {
		return Treatment_HTML_Helper::fetch_homepage_html(
			array(
				'timeout'   => 5,
				'sslverify' => false,
			)
		);
	}
}
