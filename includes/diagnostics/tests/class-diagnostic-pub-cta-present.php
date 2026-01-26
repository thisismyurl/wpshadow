<?php
/**
 * Diagnostic: CTA Present in Posts
 *
 * Category: Content Publishing
 * Priority: 2
 * Philosophy: 7, 8, 9
 *
 * Test Description:
 * Do published posts contain Call-To-Action (CTA) elements?
 *
 * @package WPShadow
 * @subpackage Diagnostics
 *
 * @since 1.2601.2148
 * @verified 2026-01-26 - Fully implemented
 * @guardian-integrated Pending
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Diagnostic_Lean_Checks;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Pub_Cta_Present Class
 *
 * Checks if published posts and pages contain Call-To-Action elements.
 * CTAs are essential for converting readers into customers or subscribers.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Pub_Cta_Present extends Diagnostic_Base {
	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'pub-cta-present';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'CTA Present in Posts';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Do published posts contain Call-To-Action elements?';

	/**
	 * The category this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $category = 'Content Publishing';

	/**
	 * The threat level (low for content quality)
	 *
	 * @var string
	 */
	protected static $threat_level = 'low';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'general';

	/**
	 * The family label
	 *
	 * @var string
	 */
	protected static $family_label = 'General';

	/**
	 * Run the diagnostic check
	 *
	 * Checks if recent published posts contain Call-To-Action elements.
	 * CTAs can be buttons, action-oriented links, or conversion prompts.
	 *
	 * @since  1.2601.2148
	 * @return array|null Null if pass, array of findings if fail.
	 */
	public static function check(): ?array {
		// Get recent published posts and pages.
		$posts = get_posts(
			array(
				'post_type'      => array( 'post', 'page' ),
				'post_status'    => 'publish',
				'posts_per_page' => 10,
				'orderby'        => 'date',
				'order'          => 'DESC',
			)
		);

		if ( empty( $posts ) ) {
			return null; // No posts to check.
		}

		$posts_without_cta = 0;
		foreach ( $posts as $post ) {
			if ( ! self::has_cta( $post->post_content ) ) {
				++$posts_without_cta;
			}
		}

		$percentage = ( $posts_without_cta / count( $posts ) ) * 100;

		// Alert if more than 30% of posts lack CTAs.
		if ( $percentage > 30 ) {
			return Diagnostic_Lean_Checks::build_finding(
				'pub-cta-present',
				'Posts Missing Call-To-Action',
				sprintf(
					/* translators: %s: percentage of posts without CTAs */
					__( '%.0f%% of your recent posts lack Call-To-Action elements. CTAs are essential for converting readers into customers or subscribers. Consider adding buttons like "Sign Up," "Learn More," or "Get Started" to guide your readers.', 'wpshadow' ),
					$percentage
				),
				'general',
				'low',
				25,
				'pub-cta-present'
			);
		}

		return null;
	}

	/**
	 * Check if content has a CTA element
	 *
	 * Detects various types of CTAs including:
	 * - Button elements (<button> tags)
	 * - WordPress button blocks
	 * - Links with action-oriented text
	 * - Common conversion keywords
	 *
	 * @since  1.2601.2148
	 * @param  string $content Post content to check.
	 * @return bool True if CTA found, false otherwise.
	 */
	private static function has_cta( string $content ): bool {
		// Check for button elements.
		if ( preg_match( '/<button[\s>]/i', $content ) ) {
			return true;
		}

		// Check for WordPress button blocks.
		if ( false !== strpos( $content, 'wp-block-button' ) || false !== strpos( $content, '<!-- wp:button' ) ) {
			return true;
		}

		// Check for action-oriented keywords in links.
		$action_keywords = array(
			'click here',
			'sign up',
			'subscribe',
			'register',
			'download',
			'buy now',
			'purchase',
			'get started',
			'learn more',
			'find out',
			'discover',
			'try free',
			'join now',
			'contact us',
			'book now',
			'reserve',
			'order now',
			'claim',
			'unlock',
			'upgrade',
		);

		// Convert to lowercase for case-insensitive matching.
		$content_lower = strtolower( $content );

		foreach ( $action_keywords as $keyword ) {
			// Look for keywords within anchor tags.
			if ( preg_match( '/<a[^>]*>.*?' . preg_quote( $keyword, '/' ) . '.*?<\/a>/is', $content_lower ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Run diagnostic test (legacy method)
	 *
	 * @since  1.2601.2148
	 * @return array Diagnostic results.
	 */
	public static function run(): array {
		$result = self::check();

		if ( null === $result ) {
			return array(
				'status'  => 'pass',
				'message' => __( 'Posts contain appropriate Call-To-Action elements', 'wpshadow' ),
			);
		}

		return array(
			'status'  => 'fail',
			'message' => $result['description'] ?? __( 'Posts missing Call-To-Action elements', 'wpshadow' ),
			'data'    => $result,
		);
	}
}
