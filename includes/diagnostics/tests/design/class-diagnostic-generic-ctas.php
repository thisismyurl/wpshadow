<?php
/**
 * Generic CTAs Diagnostic
 *
 * Tests whether CTAs are generic ('Click Here') or specific. Specific CTAs
 * that tell users exactly what they'll get convert 3x better.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Generic_CTAs Class
 *
 * Detects generic calls-to-action like "Click Here" or "Read More" which
 * convert poorly compared to specific, benefit-focused CTAs.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Generic_CTAs extends Diagnostic_Base {

	protected static $slug        = 'generic-ctas';
	protected static $title       = 'Generic CTAs';
	protected static $description = 'Tests whether CTAs are specific or generic';
	protected static $family      = 'conversion';

	public static function check() {
		$score           = 0;
		$max_score       = 3;
		$score_details   = array();
		$recommendations = array();

		// Get sample of content.
		$posts = get_posts(
			array(
				'post_type'      => array( 'post', 'page' ),
				'posts_per_page' => 30,
				'post_status'    => 'publish',
			)
		);

		$posts_checked            = 0;
		$posts_with_generic_ctas  = 0;
		$posts_with_specific_ctas = 0;

		// Generic CTA patterns.
		$generic_patterns = array(
			'click here',
			'read more',
			'learn more',
			'>here<',
			'click this',
			'see more',
		);

		// Specific CTA patterns (better).
		$specific_patterns = array(
			'get your free',
			'download the',
			'start your',
			'join',
			'subscribe',
			'buy now',
			'get started',
			'try free',
			'sign up',
		);

		foreach ( $posts as $post ) {
			++$posts_checked;
			$content = strtolower( $post->post_content );

			// Check for generic CTAs.
			$has_generic = false;
			foreach ( $generic_patterns as $pattern ) {
				if ( strpos( $content, $pattern ) !== false ) {
					$has_generic = true;
					break;
				}
			}

			if ( $has_generic ) {
				++$posts_with_generic_ctas;
			}

			// Check for specific CTAs.
			$has_specific = false;
			foreach ( $specific_patterns as $pattern ) {
				if ( strpos( $content, $pattern ) !== false ) {
					$has_specific = true;
					break;
				}
			}

			if ( $has_specific ) {
				++$posts_with_specific_ctas;
			}
		}

		// Score based on CTA quality.
		if ( $posts_checked > 0 ) {
			$generic_percentage  = ( $posts_with_generic_ctas / $posts_checked ) * 100;
			$specific_percentage = ( $posts_with_specific_ctas / $posts_checked ) * 100;

			if ( $generic_percentage < 20 && $specific_percentage > 30 ) {
				$score           = 3;
				$score_details[] = __( '✓ Mostly specific, action-oriented CTAs', 'wpshadow' );
			} elseif ( $generic_percentage < 40 ) {
				$score             = 2;
				$score_details[]   = sprintf(
					/* translators: %d: percentage of content with generic CTAs */
					__( '◐ %d%% of content has generic CTAs', 'wpshadow' ),
					round( $generic_percentage )
				);
				$recommendations[] = __( 'Replace generic CTAs with specific ones: "Download Free SEO Checklist" vs "Click Here"', 'wpshadow' );
			} else {
				$score             = 0;
				$score_details[]   = sprintf(
					/* translators: %d: percentage of content using generic CTAs */
					__( '✗ %d%% of content uses generic CTAs', 'wpshadow' ),
					round( $generic_percentage )
				);
				$recommendations[] = __( 'Critical: Rewrite generic CTAs - be specific about what users will get', 'wpshadow' );
			}
		}

		$score_percentage = ( $score / $max_score ) * 100;

		if ( $score_percentage >= 70 ) {
			return null;
		}

		$severity     = 'medium';
		$threat_level = 25;

		return array(
			'id'              => self::$slug,
			'title'           => self::$title,
			'description'     => sprintf(
				/* translators: %d: score percentage */
				__( 'CTA specificity score: %d%%. Generic CTAs like "Click Here" or "Learn More" convert poorly. Specific CTAs convert 3x better: "Download Free WordPress Security Guide" vs "Click Here". Tell users exactly what they\'ll get. Bad: "Submit", Good: "Get My Free Checklist". Include benefit + action.', 'wpshadow' ),
				$score_percentage
			),
			'severity'        => $severity,
			'threat_level'    => $threat_level,
			'auto_fixable'    => false,
			'kb_link'         => 'https://wpshadow.com/kb/effective-ctas',
			'details'         => $score_details,
			'recommendations' => $recommendations,
			'impact'          => __( 'Specific CTAs set clear expectations and reduce friction, resulting in higher conversion rates and better user experience.', 'wpshadow' ),
		);
	}
}
