<?php
/**
 * Missing Canonical URL Diagnostic
 *
 * Detects missing or incorrect canonical tags, creating duplicate content
 * issues and diluting ranking power. Self-referential canonicals are best practice.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6028.1655
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Missing_Canonical_URL Class
 *
 * Checks for proper rel="canonical" implementation on all public pages.
 * Validates self-reference and format to prevent duplicate content issues.
 *
 * @since 1.6028.1655
 */
class Diagnostic_Missing_Canonical_URL extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'missing-canonical-url';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Missing Canonical URL Self-Reference';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects missing or incorrect canonical tags causing duplicate content issues';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6028.1655
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$analysis = self::analyze_canonical_tags();

		if ( $analysis['missing_count'] === 0 && $analysis['incorrect_count'] === 0 ) {
			return null; // All canonicals are properly implemented.
		}

		$total              = $analysis['total_pages'];
		$problematic        = $analysis['missing_count'] + $analysis['incorrect_count'];
		$problematic_pct    = ( $problematic / max( $total, 1 ) ) * 100;

		// Determine severity.
		if ( $problematic_pct > 50 ) {
			$severity     = 'medium';
			$threat_level = 55;
		} elseif ( $problematic_pct > 25 ) {
			$severity     = 'low';
			$threat_level = 40;
		} else {
			$severity     = 'info';
			$threat_level = 25;
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				/* translators: 1: percentage of problematic pages, 2: number of pages */
				__( '%1$s%% of pages (%2$d) have missing or incorrect canonical tags', 'wpshadow' ),
				number_format( $problematic_pct, 1 ),
				$problematic
			),
			'severity'    => $severity,
			'threat_level' => $threat_level,
			'auto_fixable' => true,
			'kb_link'     => 'https://wpshadow.com/kb/canonical-urls',
			'family'      => self::$family,
			'meta'        => array(
				'affected_count'   => $problematic,
				'total_pages'      => $total,
				'missing_count'    => $analysis['missing_count'],
				'incorrect_count'  => $analysis['incorrect_count'],
				'recommended'      => __( 'All pages should have self-referential canonical tags', 'wpshadow' ),
				'impact_level'     => 'medium',
				'immediate_actions' => array(
					__( 'Install SEO plugin (Yoast/RankMath) for automatic canonicals', 'wpshadow' ),
					__( 'Verify canonical tags in page source', 'wpshadow' ),
					__( 'Fix incorrect canonical URLs', 'wpshadow' ),
					__( 'Test with Google Search Console', 'wpshadow' ),
				),
			),
			'details'     => array(
				'why_important' => __( 'Canonical tags tell search engines which URL is the "master" version when duplicate content exists. Missing canonicals allow search engines to index multiple versions (with/without trailing slash, HTTP vs HTTPS, www vs non-www), diluting ranking power. Incorrect canonicals point authority to the wrong page.', 'wpshadow' ),
				'user_impact'   => array(
					__( 'Ranking Dilution: Authority split across duplicate URLs', 'wpshadow' ),
					__( 'Indexation Issues: Wrong versions appear in search results', 'wpshadow' ),
					__( 'Crawl Waste: Search engines waste time on duplicates', 'wpshadow' ),
					__( 'Link Equity Loss: Backlinks don\'t consolidate to canonical', 'wpshadow' ),
				),
				'canonical_status' => array(
					'missing'   => $analysis['missing_count'],
					'incorrect' => $analysis['incorrect_count'],
					'correct'   => $total - $problematic,
				),
				'examples'      => $analysis['examples'],
				'solution_options' => array(
					'free'     => array(
						'label'       => __( 'SEO Plugin Automatic Canonicals', 'wpshadow' ),
						'description' => __( 'Install Yoast SEO or RankMath for automatic canonical generation', 'wpshadow' ),
						'steps'       => array(
							__( 'Install Yoast SEO (free) or RankMath (free)', 'wpshadow' ),
							__( 'Plugin auto-adds self-referential canonical to all pages', 'wpshadow' ),
							__( 'Verify canonical in <head> source: <link rel="canonical">', 'wpshadow' ),
							__( 'Check format: https://example.com/page/ (exact URL)', 'wpshadow' ),
							__( 'Test with Google Rich Results Test', 'wpshadow' ),
						),
					),
					'premium'  => array(
						'label'       => __( 'Manual Theme Implementation', 'wpshadow' ),
						'description' => __( 'Add canonical tag to theme header.php', 'wpshadow' ),
						'steps'       => array(
							__( 'Open theme header.php file', 'wpshadow' ),
							__( 'Add before </head>: <?php wp_head(); ?>', 'wpshadow' ),
							__( 'WordPress outputs canonical via wp_head hook', 'wpshadow' ),
							__( 'Or manually add: <link rel="canonical" href="<?php echo esc_url( get_permalink() ); ?>">', 'wpshadow' ),
							__( 'Test across different page types', 'wpshadow' ),
						),
					),
					'advanced' => array(
						'label'       => __( 'Custom Canonical Logic', 'wpshadow' ),
						'description' => __( 'Add advanced canonical rules via functions.php', 'wpshadow' ),
						'steps'       => array(
							__( 'Hook into get_canonical_url filter', 'wpshadow' ),
							__( 'Add custom logic for paginated content', 'wpshadow' ),
							__( 'Handle faceted navigation canonicals', 'wpshadow' ),
							__( 'Set preferred domain (www vs non-www)', 'wpshadow' ),
							__( 'Test with URL parameters', 'wpshadow' ),
						),
					),
				),
				'best_practices' => array(
					__( 'Use self-referential canonical on all pages (even unique ones)', 'wpshadow' ),
					__( 'Canonical should be absolute URL (https://example.com/page/)', 'wpshadow' ),
					__( 'Keep consistent URL format (trailing slash or not)', 'wpshadow' ),
					__( 'Point paginated content to page 1 or use rel="next/prev"', 'wpshadow' ),
					__( 'Ensure canonical matches final redirect destination', 'wpshadow' ),
				),
				'testing_steps' => array(
					'verification' => array(
						__( 'View page source and search for rel="canonical"', 'wpshadow' ),
						__( 'Verify URL matches address bar (self-reference)', 'wpshadow' ),
						__( 'Test with Google Rich Results Test tool', 'wpshadow' ),
						__( 'Check Search Console for duplicate content warnings', 'wpshadow' ),
					),
					'expected_result' => __( 'All pages have self-referential canonical in <head>', 'wpshadow' ),
				),
			),
		);
	}

	/**
	 * Analyze canonical tag implementation across all published pages.
	 *
	 * @since  1.6028.1655
	 * @return array Analysis results with counts and examples.
	 */
	private static function analyze_canonical_tags() {
		$result = array(
			'total_pages'      => 0,
			'missing_count'    => 0,
			'incorrect_count'  => 0,
			'examples'         => array(),
		);

		// Get all published posts and pages.
		$args = array(
			'post_type'      => array( 'post', 'page' ),
			'post_status'    => 'publish',
			'posts_per_page' => 100, // Sample for performance.
			'fields'         => 'ids',
			'orderby'        => 'rand',
		);

		$posts = get_posts( $args );

		if ( empty( $posts ) ) {
			return $result;
		}

		$result['total_pages'] = count( $posts );
		$example_limit         = 5;
		$example_count         = 0;

		foreach ( $posts as $post_id ) {
			$canonical = self::get_canonical_url( $post_id );
			$permalink = get_permalink( $post_id );

			if ( empty( $canonical ) ) {
				$result['missing_count']++;
				if ( $example_count < $example_limit ) {
					$result['examples'][] = array(
						'url'    => $permalink,
						'title'  => get_the_title( $post_id ),
						'issue'  => 'missing',
						'canonical' => '',
					);
					$example_count++;
				}
			} elseif ( self::normalize_url( $canonical ) !== self::normalize_url( $permalink ) ) {
				$result['incorrect_count']++;
				if ( $example_count < $example_limit ) {
					$result['examples'][] = array(
						'url'    => $permalink,
						'title'  => get_the_title( $post_id ),
						'issue'  => 'incorrect',
						'canonical' => $canonical,
					);
					$example_count++;
				}
			}
		}

		return $result;
	}

	/**
	 * Get canonical URL for a specific post.
	 *
	 * Simulates WordPress canonical tag generation.
	 *
	 * @since  1.6028.1655
	 * @param  int $post_id Post ID.
	 * @return string Canonical URL or empty string.
	 */
	private static function get_canonical_url( $post_id ) {
		// Check if Yoast SEO canonical meta exists.
		$yoast_canonical = get_post_meta( $post_id, '_yoast_wpseo_canonical', true );
		if ( ! empty( $yoast_canonical ) ) {
			return $yoast_canonical;
		}

		// Check if RankMath canonical meta exists.
		$rankmath_canonical = get_post_meta( $post_id, 'rank_math_canonical_url', true );
		if ( ! empty( $rankmath_canonical ) ) {
			return $rankmath_canonical;
		}

		// WordPress core uses get_permalink() as canonical by default.
		// However, without SEO plugin, it may not output canonical tag.
		// Check if wp_head outputs canonical (via SEO plugin or theme).
		if ( function_exists( 'wpseo_auto_load' ) || class_exists( 'RankMath' ) ) {
			return get_permalink( $post_id );
		}

		// No canonical found.
		return '';
	}

	/**
	 * Normalize URL for comparison.
	 *
	 * Removes protocol, trailing slash, and query parameters.
	 *
	 * @since  1.6028.1655
	 * @param  string $url URL to normalize.
	 * @return string Normalized URL.
	 */
	private static function normalize_url( $url ) {
		// Remove protocol.
		$url = preg_replace( '#^https?://#i', '', $url );
		
		// Remove trailing slash.
		$url = rtrim( $url, '/' );
		
		// Remove query parameters.
		$url = preg_replace( '/\?.*$/', '', $url );
		
		return strtolower( $url );
	}
}
