<?php
/**
 * Mobile Reduce Motion Preference
 *
 * Respects prefers-reduced-motion media query and disables animations.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Accessibility
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Diagnostics\Helpers\Diagnostic_HTML_Helper;
use WPShadow\Core\Diagnostic_Base;

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
 * @since 0.6093.1200
 */
class Diagnostic_Mobile_Reduce_Motion extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-reduce-motion';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Mobile Reduce Motion Preference';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Validates animations respect prefers-reduced-motion';

	/**
	 * The diagnostic family.
	 *
	 * @var string
	 */
	protected static $family = 'accessibility';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = self::find_motion_issues();

		if ( empty( $issues['animation_count'] ) && ! empty( $issues['has_media_query'] ) ) {
			return null; // No animations or already protected
		}

		$threat = 50;
		if ( ! empty( $issues['has_media_query'] ) ) {
			$threat = 40; // Media query exists but animations still run
		}

		return array(
			'id'                  => self::$slug,
			'title'               => self::$title,
			'description'         => sprintf(
				/* translators: %d: number of animations */
				__( 'Found %d animations/transitions not respecting prefers-reduced-motion', 'wpshadow' ),
				$issues['animation_count'] ?? 0
			),
			'severity'            => 'medium',
			'threat_level'        => $threat,
			'animation_count'     => $issues['animation_count'] ?? 0,
			'has_media_query'     => ! empty( $issues['has_media_query'] ),
			'problematic_effects' => $issues['effects'] ?? array(),
			'wcag_violation'      => '2.3.3 Animation from Interactions (Level AAA)',
			'user_impact'         => __( 'Users with vestibular disorders may experience motion sickness', 'wpshadow' ),
			'auto_fixable'        => true,
			'kb_link'             => 'https://wpshadow.com/kb/reduce-motion?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
		);
	}

	/**
	 * Find motion-related accessibility issues.
	 *
	 * @since 0.6093.1200
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
		$animation_count           = preg_match_all( '/animation\s*:/i', $css, $matches );
		$issues['animation_count'] = (int) $animation_count;

		// Check for transitions
		$transition_count           = preg_match_all( '/transition\s*:/i', $css, $matches );
		$issues['animation_count'] += (int) $transition_count;

		// Find specific animation types
		if ( preg_match_all( '/@keyframes\s+(\w+)/i', $css, $animation_names ) ) {
			$issues['effects'] = array_slice( $animation_names[1], 0, 5 );
		}

		// Check for parallax effect (scroll-based animation)
		if ( preg_match( '/transform.*translate|scroll.*event|parallax/i', $html ) ) {
			++$issues['animation_count'];
			$issues['effects'][] = 'Parallax scroll effect';
		}

		// Check for AOS (Animate On Scroll) library
		if ( preg_match( '/aos\.js|data-aos/i', $html ) ) {
			++$issues['animation_count'];
			$issues['effects'][] = 'Animate On Scroll (AOS)';
		}

		return $issues;
	}

	/**
	 * Get page HTML for analysis.
	 *
	 * @since 0.6093.1200
	 * @return string|null HTML content.
	 */
	private static function get_page_html(): ?string {
		return Diagnostic_HTML_Helper::fetch_homepage_html(
			array(
				'timeout'   => 5,
				'sslverify' => false,
			)
		);
	}
}
