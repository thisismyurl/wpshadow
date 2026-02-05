<?php
/**
 * No CTAs in Content Treatment
 *
 * Tests whether posts contain any calls-to-action. Posts without CTAs convert
 * at 0% compared to industry average of 2-5%.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.5003.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_No_CTAs Class
 *
 * Detects posts with no calls-to-action at all. Every post should guide
 * readers toward a next action - subscribe, download, product, etc.
 *
 * @since 1.5003.1200
 */
class Treatment_No_CTAs extends Treatment_Base {

	protected static $slug = 'no-ctas';
	protected static $title = 'No CTAs in Content';
	protected static $description = 'Tests whether posts contain calls-to-action';
	protected static $family = 'conversion';

	public static function check() {
		$score          = 0;
		$max_score      = 3;
		$score_details  = array();
		$recommendations = array();

		// Get sample of posts.
		$posts = get_posts(
			array(
				'post_type'      => 'post',
				'posts_per_page' => 30,
				'post_status'    => 'publish',
			)
		);

		$posts_checked = 0;
		$posts_with_ctas = 0;
		$posts_without_ctas = 0;

		// CTA indicators.
		$cta_patterns = array(
			'<a',           // Links.
			'<button',      // Buttons.
			'[button',      // Shortcode buttons.
			'[cta',         // CTA shortcodes.
			'href',         // Any links.
			'subscribe',    // Subscribe CTAs.
			'download',     // Download CTAs.
			'get',          // Get started/get free.
			'join',         // Join CTAs.
			'sign up',      // Signup.
		);

		foreach ( $posts as $post ) {
			++$posts_checked;
			$content = strtolower( $post->post_content );
			
			$has_cta = false;
			$cta_count = 0;
			
			// Check for any CTA indicators.
			foreach ( $cta_patterns as $pattern ) {
				if ( strpos( $content, $pattern ) !== false ) {
					$has_cta = true;
					++$cta_count;
				}
			}

			if ( $has_cta ) {
				++$posts_with_ctas;
			} else {
				++$posts_without_ctas;
			}
		}

		// Score based on CTA presence.
		if ( $posts_checked > 0 ) {
			$cta_percentage = ( $posts_with_ctas / $posts_checked ) * 100;

			if ( $cta_percentage >= 80 ) {
				$score = 3;
				$score_details[] = sprintf( __( '✓ %d%% of posts contain CTAs', 'wpshadow' ), round( $cta_percentage ) );
			} elseif ( $cta_percentage >= 50 ) {
				$score = 2;
				$score_details[]   = sprintf( __( '◐ %d%% of posts have CTAs (%d without)', 'wpshadow' ), round( $cta_percentage ), $posts_without_ctas );
				$recommendations[] = __( 'Add CTAs to all posts - guide readers to next action', 'wpshadow' );
			} else {
				$score = 0;
				$score_details[]   = sprintf( __( '✗ Only %d%% of posts have CTAs (%d posts lack any CTA)', 'wpshadow' ), round( $cta_percentage ), $posts_without_ctas );
				$recommendations[] = __( 'Critical: Add CTAs to every post - subscribe, download, product, related content', 'wpshadow' );
			}
		}

		$score_percentage = ( $score / $max_score ) * 100;

		if ( $score_percentage >= 70 ) {
			return null;
		}

		$severity = 'critical';
		$threat_level = 40;

		return array(
			'id'               => self::$slug,
			'title'            => self::$title,
			'description'      => sprintf(
				/* translators: %d: score percentage, %d: posts without CTAs */
				__( 'CTA presence score: %d%% (%d posts lack CTAs). Posts without CTAs convert at 0%%. Every post should guide readers to a next action: newsletter signup, related content, product trial, downloadable resource. Industry average: 2-5%% conversion with CTAs. Missing CTAs = missed opportunities.', 'wpshadow' ),
				$score_percentage,
				$posts_without_ctas
			),
			'severity'         => $severity,
			'threat_level'     => $threat_level,
			'auto_fixable'     => false,
			'kb_link'          => 'https://wpshadow.com/kb/cta-essentials',
			'details'          => $score_details,
			'recommendations'  => $recommendations,
			'impact'           => __( 'CTAs convert readers into subscribers, customers, or engaged community members. Without them, traffic generates zero business value.', 'wpshadow' ),
		);
	}
}
