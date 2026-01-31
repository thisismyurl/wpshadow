<?php
/**
 * Missing Meta Tags Audit Diagnostic
 *
 * Checks pages for missing title, meta description, Open Graph tags, and structured data.
 * Analyzes SEO tag completeness across site.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6028.1630
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Missing Meta Tags Audit Diagnostic Class
 *
 * Detects missing SEO meta tags that could:
 * - Reduce search engine visibility
 * - Impair social media sharing
 * - Lower click-through rates
 * - Hurt search rankings
 *
 * @since 1.6028.1630
 */
class Diagnostic_Missing_Meta_Tags extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @since 1.6028.1630
	 * @var   string
	 */
	protected static $slug = 'missing-meta-tags';

	/**
	 * The diagnostic title
	 *
	 * @since 1.6028.1630
	 * @var   string
	 */
	protected static $title = 'Missing Meta Tags Audit';

	/**
	 * The diagnostic description
	 *
	 * @since 1.6028.1630
	 * @var   string
	 */
	protected static $description = 'Analyzes pages for missing SEO meta tags, Open Graph tags, and structured data';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @since 1.6028.1630
	 * @var   string
	 */
	protected static $family = 'seo';

	/**
	 * Cache duration in seconds (3 hours)
	 *
	 * @since 1.6028.1630
	 */
	private const CACHE_DURATION = 10800;

	/**
	 * Number of posts to sample
	 *
	 * @since 1.6028.1630
	 */
	private const SAMPLE_SIZE = 5;

	/**
	 * Run the diagnostic check
	 *
	 * Analyzes pages for missing SEO meta tags including:
	 * - Title tags
	 * - Meta descriptions
	 * - Open Graph tags (og:title, og:description, og:image, og:url)
	 * - Twitter Card tags
	 * - Structured data (schema.org JSON-LD)
	 *
	 * @since  1.6028.1630
	 * @return array|null Finding array if issues detected, null otherwise.
	 */
	public static function check() {
		// Check transient cache first.
		$cache_key = 'wpshadow_diagnostic_missing_meta_tags';
		$cached    = get_transient( $cache_key );
		if ( false !== $cached ) {
			return self::evaluate_results( $cached );
		}

		// Analyze meta tags across site.
		$analysis = self::analyze_meta_tags();

		// Cache results.
		set_transient( $cache_key, $analysis, self::CACHE_DURATION );

		return self::evaluate_results( $analysis );
	}

	/**
	 * Analyze meta tags across site
	 *
	 * @since  1.6028.1630
	 * @return array Analysis results containing tag completeness data.
	 */
	private static function analyze_meta_tags(): array {
		$analysis = array(
			'pages_checked'       => 0,
			'pages_with_issues'   => 0,
			'missing_tags'        => array(),
			'tag_scores'          => array(
				'title'          => 0,
				'description'    => 0,
				'og_title'       => 0,
				'og_description' => 0,
				'og_image'       => 0,
				'og_url'         => 0,
				'twitter_card'   => 0,
				'structured_data' => 0,
			),
			'pages_analyzed'      => array(),
			'overall_completeness' => 0,
			'issues'              => array(),
		);

		// Check homepage.
		$homepage_result = self::check_page_meta_tags( home_url(), 'Homepage' );
		self::merge_page_result( $analysis, $homepage_result );

		// Check sample posts.
		$posts = get_posts( array(
			'numberposts' => self::SAMPLE_SIZE,
			'post_type'   => 'post',
			'post_status' => 'publish',
			'orderby'     => 'rand',
		) );

		foreach ( $posts as $post ) {
			$post_url    = get_permalink( $post );
			$post_result = self::check_page_meta_tags( $post_url, $post->post_title );
			self::merge_page_result( $analysis, $post_result );
		}

		// Calculate overall completeness.
		if ( $analysis['pages_checked'] > 0 ) {
			$total_tags    = count( $analysis['tag_scores'] );
			$total_score   = array_sum( $analysis['tag_scores'] );
			$max_score     = $total_tags * $analysis['pages_checked'];
			
			$analysis['overall_completeness'] = ( $max_score > 0 ) ? round( ( $total_score / $max_score ) * 100 ) : 0;
		}

		// Build issues list.
		$analysis = self::identify_issues( $analysis );

		return $analysis;
	}

	/**
	 * Check meta tags for a single page
	 *
	 * @since  1.6028.1630
	 * @param  string $url       Page URL.
	 * @param  string $page_name Page identifier.
	 * @return array Page analysis results.
	 */
	private static function check_page_meta_tags( string $url, string $page_name ): array {
		$result = array(
			'url'         => $url,
			'page_name'   => $page_name,
			'html'        => '',
			'tags_found'  => array(),
			'tags_missing' => array(),
			'score'       => 0,
		);

		// Fetch page HTML.
		$response = wp_remote_get( $url, array(
			'timeout'     => 10,
			'sslverify'   => false,
			'user-agent'  => 'WPShadow-SEO-Analyzer/1.0',
		) );

		if ( is_wp_error( $response ) ) {
			$result['error'] = $response->get_error_message();
			return $result;
		}

		$html          = wp_remote_retrieve_body( $response );
		$result['html'] = $html;

		// Parse meta tags.
		$result['tags_found'] = array(
			'title'           => self::has_title_tag( $html ),
			'description'     => self::has_meta_description( $html ),
			'og_title'        => self::has_og_tag( $html, 'og:title' ),
			'og_description'  => self::has_og_tag( $html, 'og:description' ),
			'og_image'        => self::has_og_tag( $html, 'og:image' ),
			'og_url'          => self::has_og_tag( $html, 'og:url' ),
			'twitter_card'    => self::has_twitter_card( $html ),
			'structured_data' => self::has_structured_data( $html ),
		);

		// Calculate score.
		$result['score'] = array_sum( $result['tags_found'] );

		// Identify missing tags.
		foreach ( $result['tags_found'] as $tag => $found ) {
			if ( ! $found ) {
				$result['tags_missing'][] = $tag;
			}
		}

		return $result;
	}

	/**
	 * Check if page has title tag
	 *
	 * @since  1.6028.1630
	 * @param  string $html Page HTML.
	 * @return int 1 if found, 0 otherwise.
	 */
	private static function has_title_tag( string $html ): int {
		return preg_match( '/<title[^>]*>(.+?)<\/title>/i', $html ) ? 1 : 0;
	}

	/**
	 * Check if page has meta description
	 *
	 * @since  1.6028.1630
	 * @param  string $html Page HTML.
	 * @return int 1 if found, 0 otherwise.
	 */
	private static function has_meta_description( string $html ): int {
		return preg_match( '/<meta\s+name=["\']description["\']\s+content=["\'](.+?)["\']/i', $html ) ? 1 : 0;
	}

	/**
	 * Check if page has Open Graph tag
	 *
	 * @since  1.6028.1630
	 * @param  string $html     Page HTML.
	 * @param  string $property OG property name.
	 * @return int 1 if found, 0 otherwise.
	 */
	private static function has_og_tag( string $html, string $property ): int {
		$pattern = sprintf( '/<meta\s+property=["\']%s["\']\s+content=["\'](.+?)["\']/i', preg_quote( $property, '/' ) );
		return preg_match( $pattern, $html ) ? 1 : 0;
	}

	/**
	 * Check if page has Twitter Card tags
	 *
	 * @since  1.6028.1630
	 * @param  string $html Page HTML.
	 * @return int 1 if found, 0 otherwise.
	 */
	private static function has_twitter_card( string $html ): int {
		return preg_match( '/<meta\s+name=["\']twitter:card["\']\s+content=["\'](.+?)["\']/i', $html ) ? 1 : 0;
	}

	/**
	 * Check if page has structured data (JSON-LD)
	 *
	 * @since  1.6028.1630
	 * @param  string $html Page HTML.
	 * @return int 1 if found, 0 otherwise.
	 */
	private static function has_structured_data( string $html ): int {
		return preg_match( '/<script\s+type=["\']application\/ld\+json["\']/i', $html ) ? 1 : 0;
	}

	/**
	 * Merge page result into analysis
	 *
	 * @since 1.6028.1630
	 * @param array $analysis Analysis data (passed by reference).
	 * @param array $result   Page result to merge.
	 * @return void
	 */
	private static function merge_page_result( array &$analysis, array $result ): void {
		if ( isset( $result['error'] ) ) {
			return; // Skip failed requests.
		}

		++$analysis['pages_checked'];

		// Accumulate tag scores.
		foreach ( $result['tags_found'] as $tag => $found ) {
			$analysis['tag_scores'][ $tag ] += $found;
		}

		// Track pages with missing tags.
		if ( ! empty( $result['tags_missing'] ) ) {
			++$analysis['pages_with_issues'];
		}

		// Store page analysis.
		$analysis['pages_analyzed'][] = array(
			'url'          => $result['url'],
			'page_name'    => $result['page_name'],
			'tags_missing' => $result['tags_missing'],
			'score'        => $result['score'],
		);
	}

	/**
	 * Identify issues based on analysis
	 *
	 * @since  1.6028.1630
	 * @param  array $analysis Analysis data.
	 * @return array Updated analysis with issues list.
	 */
	private static function identify_issues( array $analysis ): array {
		$issues = array();

		// Overall completeness check.
		if ( $analysis['overall_completeness'] < 50 ) {
			$issues[] = sprintf(
				/* translators: %d: completeness percentage */
				__( 'Only %d%% of SEO meta tags are complete across your site', 'wpshadow' ),
				$analysis['overall_completeness']
			);
		} elseif ( $analysis['overall_completeness'] < 80 ) {
			$issues[] = sprintf(
				/* translators: %d: completeness percentage */
				__( 'SEO meta tag completeness is %d%% - room for improvement', 'wpshadow' ),
				$analysis['overall_completeness']
			);
		}

		// Check individual tag types.
		$tag_labels = array(
			'title'           => __( 'Title tags', 'wpshadow' ),
			'description'     => __( 'Meta descriptions', 'wpshadow' ),
			'og_title'        => __( 'OG title tags', 'wpshadow' ),
			'og_description'  => __( 'OG description tags', 'wpshadow' ),
			'og_image'        => __( 'OG image tags', 'wpshadow' ),
			'og_url'          => __( 'OG URL tags', 'wpshadow' ),
			'twitter_card'    => __( 'Twitter Card tags', 'wpshadow' ),
			'structured_data' => __( 'Structured data', 'wpshadow' ),
		);

		foreach ( $analysis['tag_scores'] as $tag => $score ) {
			$completeness = ( $analysis['pages_checked'] > 0 ) ? round( ( $score / $analysis['pages_checked'] ) * 100 ) : 0;
			
			if ( 0 === $completeness ) {
				$issues[] = sprintf(
					/* translators: %s: tag type label */
					__( '%s are missing on all checked pages', 'wpshadow' ),
					$tag_labels[ $tag ]
				);
				$analysis['missing_tags'][] = $tag;
			} elseif ( $completeness < 50 ) {
				$issues[] = sprintf(
					/* translators: 1: tag type label, 2: completeness percentage */
					__( '%1$s are only %2$d%% complete', 'wpshadow' ),
					$tag_labels[ $tag ],
					$completeness
				);
				$analysis['missing_tags'][] = $tag;
			}
		}

		$analysis['issues'] = $issues;

		return $analysis;
	}

	/**
	 * Evaluate analysis results and build finding
	 *
	 * @since  1.6028.1630
	 * @param  array $analysis Analysis results.
	 * @return array|null Finding array if issues detected, null otherwise.
	 */
	private static function evaluate_results( array $analysis ) {
		// No issues found.
		if ( empty( $analysis['issues'] ) ) {
			return null;
		}

		// Build finding.
		return self::build_finding( $analysis );
	}

	/**
	 * Build finding array
	 *
	 * @since  1.6028.1630
	 * @param  array $analysis Analysis results.
	 * @return array Finding array with full diagnostic information.
	 */
	private static function build_finding( array $analysis ): array {
		$issue_count  = count( $analysis['issues'] );
		$threat_level = self::calculate_threat_level( $analysis );
		$severity     = ( $analysis['overall_completeness'] < 50 ) ? 'high' : 'medium';

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: 1: number of issues, 2: completeness percentage */
				__( 'Found %1$d SEO meta tag issues. Overall completeness: %2$d%%', 'wpshadow' ),
				$issue_count,
				$analysis['overall_completeness']
			),
			'severity'     => $severity,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/seo-missing-meta-tags',
			'family'       => self::$family,
			'meta'         => array(
				'issue_count'           => $issue_count,
				'pages_checked'         => $analysis['pages_checked'],
				'pages_with_issues'     => $analysis['pages_with_issues'],
				'overall_completeness'  => $analysis['overall_completeness'],
				'missing_tag_types'     => $analysis['missing_tags'],
				'seo_impact'            => self::calculate_seo_impact( $analysis['overall_completeness'] ),
			),
			'details'      => self::build_finding_details( $analysis ),
		);
	}

	/**
	 * Calculate threat level based on completeness
	 *
	 * @since  1.6028.1630
	 * @param  array $analysis Analysis results.
	 * @return int Threat level (25-50).
	 */
	private static function calculate_threat_level( array $analysis ): int {
		$completeness = $analysis['overall_completeness'];

		if ( $completeness >= 80 ) {
			return 25;
		} elseif ( $completeness >= 60 ) {
			return 30;
		} elseif ( $completeness >= 40 ) {
			return 35;
		} elseif ( $completeness >= 20 ) {
			return 40;
		} else {
			return 50;
		}
	}

	/**
	 * Calculate SEO impact message
	 *
	 * @since  1.6028.1630
	 * @param  int $completeness Completeness percentage.
	 * @return string SEO impact description.
	 */
	private static function calculate_seo_impact( int $completeness ): string {
		if ( $completeness >= 80 ) {
			return __( 'Minor SEO impact - most tags present', 'wpshadow' );
		} elseif ( $completeness >= 60 ) {
			return __( 'Moderate SEO impact - important tags missing', 'wpshadow' );
		} elseif ( $completeness >= 40 ) {
			return __( 'Significant SEO impact - many critical tags missing', 'wpshadow' );
		} else {
			return __( 'Severe SEO impact - major tags missing across site', 'wpshadow' );
		}
	}

	/**
	 * Build detailed information for finding
	 *
	 * @since  1.6028.1630
	 * @param  array $analysis Analysis results.
	 * @return array Detailed information array.
	 */
	private static function build_finding_details( array $analysis ): array {
		return array(
			'issues_found'        => $analysis['issues'],
			'pages_analyzed'      => $analysis['pages_analyzed'],
			'tag_completeness'    => array(
				'title'           => self::format_completeness( $analysis['tag_scores']['title'], $analysis['pages_checked'] ),
				'description'     => self::format_completeness( $analysis['tag_scores']['description'], $analysis['pages_checked'] ),
				'og_title'        => self::format_completeness( $analysis['tag_scores']['og_title'], $analysis['pages_checked'] ),
				'og_description'  => self::format_completeness( $analysis['tag_scores']['og_description'], $analysis['pages_checked'] ),
				'og_image'        => self::format_completeness( $analysis['tag_scores']['og_image'], $analysis['pages_checked'] ),
				'og_url'          => self::format_completeness( $analysis['tag_scores']['og_url'], $analysis['pages_checked'] ),
				'twitter_card'    => self::format_completeness( $analysis['tag_scores']['twitter_card'], $analysis['pages_checked'] ),
				'structured_data' => self::format_completeness( $analysis['tag_scores']['structured_data'], $analysis['pages_checked'] ),
			),
			'why_this_matters'    => __( 'SEO meta tags are crucial for search engine visibility, social media sharing, and user engagement. Missing tags can significantly reduce your site\'s discoverability and click-through rates from search results.', 'wpshadow' ),
			'recommended_plugins' => array(
				'Yoast SEO'    => 'https://wordpress.org/plugins/wordpress-seo/',
				'Rank Math'    => 'https://wordpress.org/plugins/seo-by-rank-math/',
				'All in One SEO' => 'https://wordpress.org/plugins/all-in-one-seo-pack/',
			),
			'next_steps'          => array(
				__( 'Install an SEO plugin like Yoast SEO or Rank Math', 'wpshadow' ),
				__( 'Configure default meta tags for all pages', 'wpshadow' ),
				__( 'Add custom meta descriptions for key pages', 'wpshadow' ),
				__( 'Set up Open Graph tags for social sharing', 'wpshadow' ),
				__( 'Implement structured data (schema.org) for rich snippets', 'wpshadow' ),
			),
		);
	}

	/**
	 * Format completeness percentage
	 *
	 * @since  1.6028.1630
	 * @param  int $score  Tag score.
	 * @param  int $total  Total pages checked.
	 * @return string Formatted completeness string.
	 */
	private static function format_completeness( int $score, int $total ): string {
		if ( 0 === $total ) {
			return '0%';
		}

		$percentage = round( ( $score / $total ) * 100 );
		return sprintf( '%d%% (%d/%d pages)', $percentage, $score, $total );
	}
}
