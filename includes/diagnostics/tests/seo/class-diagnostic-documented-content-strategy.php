<?php
/**
 * Documented Content Strategy Diagnostic
 *
 * Verifies that site has documented content strategy including goals,
 * audience personas, and success metrics.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Content
 * @since      1.6034.2322
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Documented Content Strategy Diagnostic Class
 *
 * Checks for evidence of documented content strategy by examining site
 * settings, meta fields, and content patterns that indicate strategic planning.
 *
 * **Why This Matters:**
 * - Sites with strategy achieve 313% better results
 * - Without strategy, content is random and ineffective
 * - 60% of marketers have no documented strategy
 * - Strategy provides direction and measurable goals
 * - Aligns team efforts and resources
 *
 * **Strategy Components:**
 * - Target audience definition
 * - Content goals and KPIs
 * - Editorial guidelines
 * - Brand voice and tone
 * - Content types and formats
 *
 * @since 1.6034.2322
 */
class Diagnostic_Documented_Content_Strategy extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'documented-content-strategy';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Documented Content Strategy';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies site has documented content strategy with goals and metrics';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'content';

	/**
	 * Run the diagnostic check
	 *
	 * @since  1.6034.2322
	 * @return array|null Finding array if no strategy detected, null otherwise.
	 */
	public static function check() {
		$strategy_score = 0;
		$evidence = array();

		// Check 1: Categories indicate topical strategy
		$categories = get_categories( array( 'hide_empty' => true ) );
		if ( count( $categories ) >= 5 && count( $categories ) <= 15 ) {
			$strategy_score += 20;
			$evidence[] = sprintf(
				/* translators: %d: number of categories */
				__( '%d content categories suggest strategic organization', 'wpshadow' ),
				count( $categories )
			);
		}

		// Check 2: Tagline suggests defined purpose
		$tagline = get_bloginfo( 'description' );
		if ( ! empty( $tagline ) && strlen( $tagline ) > 20 ) {
			$strategy_score += 15;
			$evidence[] = __( 'Site tagline defines purpose/audience', 'wpshadow' );
		}

		// Check 3: About page exists (audience communication)
		if ( self::has_about_page() ) {
			$strategy_score += 15;
			$evidence[] = __( 'About page communicates site mission', 'wpshadow' );
		}

		// Check 4: Content types diversity
		if ( self::has_diverse_content_types() ) {
			$strategy_score += 20;
			$evidence[] = __( 'Multiple content types indicate strategic variety', 'wpshadow' );
		}

		// Check 5: Regular author(s) with bios
		if ( self::has_author_information() ) {
			$strategy_score += 15;
			$evidence[] = __( 'Author information suggests editorial standards', 'wpshadow' );
		}

		// Check 6: Content strategy plugin
		if ( self::has_strategy_tools() ) {
			$strategy_score += 15;
			$evidence[] = __( 'Content strategy tools installed', 'wpshadow' );
		}

		// Score >= 60 indicates documented strategy
		if ( $strategy_score >= 60 ) {
			return null; // Strategy is documented/evident
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'No documented content strategy detected. Sites with strategy achieve 313% better results. Define your audience, goals, and success metrics.', 'wpshadow' ),
			'severity'     => 'medium',
			'threat_level' => 55,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/content-strategy',
			'details'      => array(
				'strategy_score' => $strategy_score,
				'evidence_found' => $evidence,
				'recommendation' => __( 'Document your content strategy with audience, goals, and metrics', 'wpshadow' ),
				'key_components' => array(
					'Target audience personas',
					'Content goals and KPIs',
					'Brand voice and tone guidelines',
					'Editorial calendar',
					'Content types and formats',
					'Distribution channels',
				),
			),
		);
	}

	/**
	 * Check if site has an About page
	 *
	 * @since  1.6034.2322
	 * @return bool True if About page exists.
	 */
	private static function has_about_page() {
		$about_page = get_page_by_path( 'about' );
		if ( ! $about_page ) {
			$about_page = get_page_by_path( 'about-us' );
		}

		return $about_page && 'publish' === $about_page->post_status;
	}

	/**
	 * Check for diverse content types
	 *
	 * @since  1.6034.2322
	 * @return bool True if multiple content types used.
	 */
	private static function has_diverse_content_types() {
		$types_count = 0;

		// Check for video content
		if ( self::has_content_with_pattern( 'youtube|vimeo|<video' ) ) {
			$types_count++;
		}

		// Check for list posts
		if ( self::has_content_with_pattern( '<ol>|<ul>|top \d+|best \d+' ) ) {
			$types_count++;
		}

		// Check for how-to content
		if ( self::has_content_with_pattern( 'how to|tutorial|guide' ) ) {
			$types_count++;
		}

		// Check for interviews/case studies
		if ( self::has_content_with_pattern( 'interview|case study|success story' ) ) {
			$types_count++;
		}

		return $types_count >= 3;
	}

	/**
	 * Check for content matching pattern
	 *
	 * @since  1.6034.2322
	 * @param  string $pattern Regex pattern to search for.
	 * @return bool True if pattern found in recent content.
	 */
	private static function has_content_with_pattern( $pattern ) {
		$recent_posts = get_posts(
			array(
				'post_type'      => 'post',
				'post_status'    => 'publish',
				'posts_per_page' => 20,
			)
		);

		foreach ( $recent_posts as $post ) {
			$content = strtolower( $post->post_title . ' ' . $post->post_content );
			if ( preg_match( '/' . $pattern . '/i', $content ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if authors have biographical information
	 *
	 * @since  1.6034.2322
	 * @return bool True if author info found.
	 */
	private static function has_author_information() {
		$authors = get_users(
			array(
				'role__in' => array( 'author', 'editor', 'administrator' ),
				'number'   => 5,
			)
		);

		$authors_with_bio = 0;
		foreach ( $authors as $author ) {
			$bio = get_user_meta( $author->ID, 'description', true );
			if ( ! empty( $bio ) && strlen( $bio ) > 50 ) {
				$authors_with_bio++;
			}
		}

		return $authors_with_bio >= 1;
	}

	/**
	 * Check for content strategy tools/plugins
	 *
	 * @since  1.6034.2322
	 * @return bool True if strategy tools found.
	 */
	private static function has_strategy_tools() {
		$strategy_plugins = array(
			'yoast-seo/wp-seo.php',
			'seo-by-rank-math/rank-math.php',
			'publishpress/publishpress.php',
		);

		foreach ( $strategy_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				return true;
			}
		}

		return false;
	}
}
