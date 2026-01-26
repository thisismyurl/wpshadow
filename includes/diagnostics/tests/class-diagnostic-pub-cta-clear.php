<?php
/**
 * Diagnostic: CTA is Clear & Compelling
 *
 * Checks if Call-to-Action (CTA) elements are clear and compelling by verifying:
 * 1. They use action-oriented words (not vague terms like "click here" or "submit")
 * 2. They have visual prominence (proper styling to stand out)
 *
 * This diagnostic analyzes the site's homepage HTML to find CTA elements (buttons,
 * submit inputs, and button-styled links) and evaluates their text quality and
 * visual styling.
 *
 * @since   1.2601.2148
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CTA Clear & Compelling Diagnostic
 *
 * Evaluates whether CTAs on the site use compelling action words and
 * have visual styling that makes them stand out to users.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Pub_Cta_Clear extends Diagnostic_Base {
	protected static $slug = 'pub-cta-clear';

	protected static $title = 'Pub Cta Clear';

	protected static $description = 'Automatically initialized lean diagnostic for Pub Cta Clear. Optimized for minimal overhead while surfacing high-value signals.';

	protected static $family = 'general';

	protected static $family_label = 'General';

	/**
	 * Get diagnostic ID
	 *
	 * @since 1.2601.2148
	 * @return string The diagnostic identifier.
	 */
	public static function get_id(): string {
		return 'pub-cta-clear';
	}

	/**
	 * Get diagnostic name
	 *
	 * @since 1.2601.2148
	 * @return string The human-readable diagnostic name.
	 */
	public static function get_name(): string {
		return __( 'CTA is Clear & Compelling', 'wpshadow' );
	}

	/**
	 * Get diagnostic description
	 *
	 * @since 1.2601.2148
	 * @return string The diagnostic description.
	 */
	public static function get_description(): string {
		return __( 'CTA uses action words and stands out?', 'wpshadow' );
	}

	/**
	 * Get diagnostic category
	 *
	 * @since 1.2601.2148
	 * @return string The diagnostic category.
	 */
	public static function get_category(): string {
		return 'content_publishing';
	}

	/**
	 * Get threat level
	 *
	 * @since  1.2601.2148
	 * @return int 0-100 severity level
	 */
	public static function get_threat_level(): int {
		return 25;
	}

	/**
	 * Run diagnostic test
	 *
	 * @since  1.2601.2148
	 * @return array Diagnostic results
	 */
	public static function run(): array {
		$result = self::check();

		if ( null === $result ) {
			return array(
				'status'  => 'pass',
				'message' => __( 'CTAs are clear and compelling', 'wpshadow' ),
				'data'    => array(),
			);
		}

		return array(
			'status'  => 'fail',
			'message' => $result['description'],
			'data'    => $result,
		);
	}

	/**
	 * Get KB article URL
	 *
	 * @since  1.2601.2148
	 * @return string The knowledge base article URL.
	 */
	public static function get_kb_article(): string {
		return 'https://wpshadow.com/kb/pub-cta-clear';
	}

	/**
	 * Get training video URL
	 *
	 * @since  1.2601.2148
	 * @return string The training video URL.
	 */
	public static function get_training_video(): string {
		return 'https://wpshadow.com/training/category-content-publishing';
	}

	/**
	 * Run the diagnostic check
	 *
	 * Checks if CTAs (Call-to-Action elements) are clear and compelling by verifying:
	 * 1. They use action-oriented words (not vague terms like "click here" or "submit")
	 * 2. They have visual prominence (proper styling to stand out)
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issues found, null otherwise.
	 */
	public static function check(): ?array {
		// Get the home page HTML for analysis.
		$url      = home_url( '/' );
		$response = wp_remote_get(
			$url,
			array(
				'timeout'   => 10,
				'sslverify' => false,
			)
		);

		if ( is_wp_error( $response ) ) {
			return null;
		}

		$html = wp_remote_retrieve_body( $response );
		if ( empty( $html ) ) {
			return null;
		}

		return self::analyze_html( $html );
	}

	/**
	 * Analyze HTML content for CTA quality
	 *
	 * @since  1.2601.2148
	 * @param  string $html The HTML content to analyze.
	 * @return array|null Finding array if issues found, null otherwise.
	 */
	protected static function analyze_html( string $html ): ?array {
		// Find all CTA elements (buttons, button-styled links).
		$cta_elements = array();

		// Extract buttons.
		if ( preg_match_all( '/<button[^>]*>(.*?)<\/button>/is', $html, $button_matches ) ) {
			$cta_elements = array_merge( $cta_elements, $button_matches[1] );
		}

		// Extract submit inputs.
		if ( preg_match_all( '/<input[^>]*type=["\']submit["\'][^>]*(value=["\']([^"\']+)["\'])?/i', $html, $submit_matches ) ) {
			if ( ! empty( $submit_matches[2] ) ) {
				$cta_elements = array_merge( $cta_elements, $submit_matches[2] );
			}
		}

		// Extract links with button/CTA classes.
		if ( preg_match_all( '/<a[^>]*class=["\'][^"\']*(?:btn|button|cta|call-to-action)[^"\']*["\'][^>]*>(.*?)<\/a>/is', $html, $link_matches ) ) {
			$cta_elements = array_merge( $cta_elements, $link_matches[1] );
		}

		if ( empty( $cta_elements ) ) {
			// No CTAs found - not our concern (that's pub-cta-present).
			return null;
		}

		// Vague/weak CTA text patterns.
		$vague_patterns = array(
			'submit',
			'click here',
			'click',
			'here',
			'ok',
			'yes',
			'no',
			'go',
			'send',
			'button',
			'more',
			'read more',
			'learn more',
		);

		// Action words that make CTAs compelling.
		$action_words = array(
			'download',
			'subscribe',
			'get started',
			'start',
			'join',
			'register',
			'sign up',
			'buy now',
			'shop',
			'discover',
			'explore',
			'contact',
			'request',
			'claim',
			'unlock',
			'access',
			'try free',
			'book',
			'schedule',
			'reserve',
		);

		$weak_ctas     = 0;
		$strong_ctas   = 0;
		$weak_examples = array();

		foreach ( $cta_elements as $cta_text ) {
			$clean_text = strtolower( wp_strip_all_tags( trim( $cta_text ) ) );

			if ( empty( $clean_text ) ) {
				++$weak_ctas;
				continue;
			}

			// Check for vague patterns.
			$is_vague = false;
			foreach ( $vague_patterns as $pattern ) {
				if ( false !== strpos( $clean_text, $pattern ) ) {
					$is_vague = true;
					break;
				}
			}

			// Check for strong action words.
			$has_action_word = false;
			foreach ( $action_words as $word ) {
				if ( false !== strpos( $clean_text, $word ) ) {
					$has_action_word = true;
					break;
				}
			}

			if ( $is_vague || ! $has_action_word ) {
				++$weak_ctas;
				if ( count( $weak_examples ) < 3 ) {
					$weak_examples[] = $clean_text;
				}
			} else {
				++$strong_ctas;
			}
		}

		// Check for visual prominence by looking for CSS that makes CTAs stand out.
		$has_prominent_styling = preg_match(
			'/\.(?:btn|button|cta|call-to-action)[^{]*\{[^}]*(?:background(?:-color)?|border|box-shadow|padding|font-weight:\s*bold)/i',
			$html
		);

		// If most CTAs are weak OR styling is missing.
		$total_ctas      = $weak_ctas + $strong_ctas;
		$weak_ratio      = $total_ctas > 0 ? ( $weak_ctas / $total_ctas ) : 0;
		$has_style_issue = ! $has_prominent_styling && $total_ctas > 0;

		if ( $weak_ratio > 0.5 || $has_style_issue ) {
			$issues = array();
			if ( $weak_ratio > 0.5 ) {
				$issues[] = sprintf(
					/* translators: 1: number of weak CTAs, 2: total CTAs */
					__( '%1$d of %2$d CTAs use vague or weak language', 'wpshadow' ),
					$weak_ctas,
					$total_ctas
				);
			}
			if ( $has_style_issue ) {
				$issues[] = __( 'CTAs lack visual prominence (styling to stand out)', 'wpshadow' );
			}

			$description = implode( '. ', $issues ) . '. ' .
				__( 'Use action-oriented words like "Download Guide" or "Start Free Trial" and ensure CTAs have clear visual styling.', 'wpshadow' );

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => $description,
				'severity'     => 'low',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/pub-cta-clear',
				'meta'         => array(
					'total_ctas'          => $total_ctas,
					'weak_ctas'           => $weak_ctas,
					'strong_ctas'         => $strong_ctas,
					'weak_examples'       => $weak_examples,
					'has_prominent_style' => $has_prominent_styling,
				),
			);
		}

		return null;
	}

	/**
	 * Live test for this diagnostic
	 *
	 * Diagnostic: Pub Cta Clear
	 * Slug: pub-cta-clear
	 *
	 * Test Purpose:
	 * - Verify that check() method returns the correct result based on site state
	 * - PASS: check() returns NULL when CTAs are clear and compelling (site is healthy)
	 * - FAIL: check() returns array when CTAs are weak or lack visual prominence (issue found)
	 * - Description: Checks if CTAs use action words and stand out visually
	 *
	 * @since  1.2601.2148
	 * @return array {
	 *     @type bool   $passed  Whether the test passed
	 *     @type string $message Human-readable test result message
	 * }
	 */
	public static function test_live_pub_cta_clear(): array {
		$result = self::check();

		if ( null === $result ) {
			return array(
				'passed'  => true,
				'message' => __( 'PASS: CTAs are clear, compelling, and visually prominent', 'wpshadow' ),
			);
		}

		return array(
			'passed'  => false,
			'message' => sprintf(
				/* translators: %s: diagnostic finding description */
				__( 'FAIL: %s', 'wpshadow' ),
				$result['description']
			),
		);
	}
}
