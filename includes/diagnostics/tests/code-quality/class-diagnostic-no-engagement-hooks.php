<?php
/**
 * No Engagement Hooks Diagnostic
 *
 * Tests whether content includes engagement hooks (questions, challenges,
 * calls for discussion). These increase comments, shares, and dwell time.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.5003.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_No_Engagement_Hooks Class
 *
 * Detects when content lacks engagement hooks like questions, polls,
 * challenges, or calls for reader input which drive interaction.
 *
 * @since 1.5003.1200
 */
class Diagnostic_No_Engagement_Hooks extends Diagnostic_Base {

	protected static $slug = 'no-engagement-hooks';
	protected static $title = 'No Engagement Hooks';
	protected static $description = 'Tests whether content includes engagement hooks';
	protected static $family = 'user-engagement';

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
		$posts_with_hooks = 0;

		// Engagement hook patterns.
		$hook_patterns = array(
			'what do you think',
			'let me know',
			'share your',
			'tell us',
			'comment below',
			'your thoughts',
			'have you tried',
			'what\'s your',
			'?',  // Questions.
		);

		foreach ( $posts as $post ) {
			++$posts_checked;
			$content = strtolower( $post->post_content );
			
			$has_hooks = false;
			foreach ( $hook_patterns as $pattern ) {
				if ( strpos( $content, $pattern ) !== false ) {
					$has_hooks = true;
					break;
				}
			}

			if ( $has_hooks ) {
				++$posts_with_hooks;
			}
		}

		// Score based on engagement hook presence.
		if ( $posts_checked > 0 ) {
			$hook_percentage = ( $posts_with_hooks / $posts_checked ) * 100;

			if ( $hook_percentage >= 60 ) {
				$score = 3;
				$score_details[] = sprintf( __( '✓ %d%% of posts include engagement hooks', 'wpshadow' ), round( $hook_percentage ) );
			} elseif ( $hook_percentage >= 30 ) {
				$score = 2;
				$score_details[]   = sprintf( __( '◐ %d%% of posts have engagement hooks', 'wpshadow' ), round( $hook_percentage ) );
				$recommendations[] = __( 'Increase engagement hooks: ask questions, request opinions', 'wpshadow' );
			} else {
				$score = 1;
				$score_details[]   = sprintf( __( '✗ Only %d%% of posts invite engagement', 'wpshadow' ), round( $hook_percentage ) );
				$recommendations[] = __( 'End posts with questions or challenges to encourage discussion', 'wpshadow' );
			}
		}

		// Check comments enabled.
		$comments_open = get_option( 'default_comment_status', 'open' );
		if ( 'open' === $comments_open ) {
			++$score;
			$score_details[] = __( '✓ Comments enabled by default', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ Comments disabled by default', 'wpshadow' );
			$recommendations[] = __( 'Enable comments to allow reader engagement', 'wpshadow' );
		}

		$score_percentage = ( $score / $max_score ) * 100;

		if ( $score_percentage >= 70 ) {
			return null;
		}

		$severity = 'medium';
		$threat_level = 20;

		return array(
			'id'               => self::$slug,
			'title'            => self::$title,
			'description'      => sprintf(
				/* translators: %d: score percentage */
				__( 'Engagement hooks score: %d%%. Posts with questions/discussion prompts get 3x more comments and 50%% longer dwell time. Engagement signals boost SEO. Examples: "What\'s your favorite strategy?", "Have you tried this?", "Share your results". End with clear invitation to respond.', 'wpshadow' ),
				$score_percentage
			),
			'severity'         => $severity,
			'threat_level'     => $threat_level,
			'auto_fixable'     => false,
			'kb_link'          => 'https://wpshadow.com/kb/engagement-hooks',
			'details'          => $score_details,
			'recommendations'  => $recommendations,
			'impact'           => __( 'Engagement hooks encourage comments, shares, and discussion, creating community and signaling content quality to search engines.', 'wpshadow' ),
		);
	}
}
