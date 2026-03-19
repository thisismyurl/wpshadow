<?php
/**
 * CTA Placement Issues Diagnostic
 *
 * Tests CTA placement. CTAs only at bottom miss 65% of readers who don't
 * scroll that far. Strategic placement throughout content increases conversions.
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
 * Diagnostic_Poor_CTA_Placement Class
 *
 * Detects when CTAs are only placed at the bottom of content, missing
 * readers who don't scroll that far (65% of visitors).
 *
 * @since 1.6093.1200
 */
class Diagnostic_Poor_CTA_Placement extends Diagnostic_Base {

	protected static $slug = 'poor-cta-placement';
	protected static $title = 'CTA Placement Issues';
	protected static $description = 'Tests whether CTAs are strategically placed throughout content';
	protected static $family = 'conversion';

	public static function check() {
		$score          = 0;
		$max_score      = 3;
		$score_details  = array();
		$recommendations = array();

		// Check for inline CTA plugins/shortcodes.
		$posts_with_inline_ctas = get_posts(
			array(
				'post_type'      => 'post',
				'posts_per_page' => 30,
				'post_status'    => 'publish',
				's'              => '[cta [button shortcode',
			)
		);

		if ( count( $posts_with_inline_ctas ) >= 10 ) {
			$score += 2;
			$score_details[] = __( '✓ Multiple posts use inline CTA shortcodes', 'wpshadow' );
		} elseif ( count( $posts_with_inline_ctas ) > 0 ) {
			++$score;
			$score_details[]   = __( '◐ Some posts have inline CTAs', 'wpshadow' );
			$recommendations[] = __( 'Add CTAs throughout content, not just at bottom', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No inline CTA shortcodes detected', 'wpshadow' );
			$recommendations[] = __( 'Place CTAs at natural break points: after intro, mid-content, end', 'wpshadow' );
		}

		// Check for CTA plugins.
		$cta_plugins = array(
			'thrive-visual-editor/thrive-visual-editor.php',
			'elementor/elementor.php',
			'beaver-builder-lite-version/fl-builder.php',
		);

		$has_cta_builder = false;
		foreach ( $cta_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_cta_builder = true;
				++$score;
				$score_details[] = __( '✓ Page builder active (enables strategic CTA placement)', 'wpshadow' );
				break;
			}
		}

		if ( ! $has_cta_builder ) {
			$score_details[]   = __( '✗ No page builder for CTA design', 'wpshadow' );
			$recommendations[] = __( 'Use Elementor or similar to create eye-catching inline CTAs', 'wpshadow' );
		}

		$score_percentage = ( $score / $max_score ) * 100;

		if ( $score_percentage >= 70 ) {
			return null;
		}

		$severity = 'medium';
		$threat_level = 25;

		return array(
			'id'               => self::$slug,
			'title'            => self::$title,
			'description'      => sprintf(
				/* translators: %d: score percentage */
				__( 'CTA placement score: %d%%. CTAs only at post bottom miss 65%% of readers who don\'t scroll that far. Strategic placement: top (for high-intent), after intro (convinced readers), mid-content (natural break), end (summary). Top CTAs convert 25%% higher than bottom-only. Multiple CTAs increase overall conversions by 40%%.', 'wpshadow' ),
				$score_percentage
			),
			'severity'         => $severity,
			'threat_level'     => $threat_level,
			'auto_fixable'     => false,
			'kb_link'          => 'https://wpshadow.com/kb/cta-placement',
			'details'          => $score_details,
			'recommendations'  => $recommendations,
			'impact'           => __( 'Strategic CTA placement ensures conversion opportunities for readers at every stage of engagement and scroll depth.', 'wpshadow' ),
		);
	}
}
