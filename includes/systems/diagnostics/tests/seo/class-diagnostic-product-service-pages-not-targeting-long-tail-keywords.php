<?php
/**
 * Product/Service Pages Not Targeting Long-Tail Keywords Diagnostic
 *
 * Checks if pages use long-tail keywords (3-5 word, specific, high-intent phrases).
 *
 * @package    WPShadow
 * @subpackage Diagnostics\SEO
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Long-Tail Keywords Targeting Diagnostic
 *
 * Detects when product/service pages don't use long-tail keywords (3-5 word, specific
 * phrases like "emergency plumber Seattle" vs generic "plumber"). Long-tail keywords
 * have 50% less competition but convert 3x better because they match exact customer searches.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Product_Service_Pages_Not_Targeting_Long_Tail_Keywords extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'product-service-pages-long-tail-keywords';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Pages Target Specific Long-Tail Keywords';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if product/service pages use long-tail keywords (3-5 words, specific, high-intent)';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the diagnostic check
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$product_pages = self::get_product_service_pages();

		if ( empty( $product_pages ) ) {
			return null; // No product pages to check
		}

		$long_tail_usage = self::analyze_long_tail_usage( $product_pages );

		if ( $long_tail_usage['percentage'] < 50 ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %d: percentage of pages using long-tail keywords */
					__( 'Only %d%% of your product/service pages use long-tail keywords. Long-tail keywords (like "wedding photographer Seattle") have 50%% less competition but convert 3x better than generic terms ("photographer"). You\'re missing easy-to-rank opportunities that customers are actively searching for.', 'wpshadow' ),
					$long_tail_usage['percentage']
				),
				'severity'    => 'high',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/long-tail-keyword-strategy?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'     => array(
					'pages_analyzed'              => count( $product_pages ),
					'pages_with_long_tail'       => $long_tail_usage['count'],
					'percentage'                 => $long_tail_usage['percentage'],
					'example_keywords'           => $long_tail_usage['examples'],
					'recommendation'             => __( 'Add 3-5 word specific keywords to each page title and heading', 'wpshadow' ),
				),
			);
		}

		return null; // No issue found
	}

	/**
	 * Get product/service pages from site
	 *
	 * @since 0.6093.1200
	 * @return array Array of page data with titles and content
	 */
	private static function get_product_service_pages(): array {
		$pages = array();

		// Look for pages with common product/service keywords
		$args = array(
			'post_type'      => array( 'page', 'post' ),
			'posts_per_page' => 20,
			's'              => 'service OR product OR offer OR solution',
			'fields'         => 'ids',
		);

		$query = new \WP_Query( $args );

		if ( $query->have_posts() ) {
			foreach ( $query->posts as $post_id ) {
				$post = get_post( $post_id );

				if ( $post ) {
					$pages[] = array(
						'id'      => $post_id,
						'title'   => $post->post_title,
						'content' => $post->post_content,
						'url'     => get_permalink( $post_id ),
					);
				}
			}
		}

		return $pages;
	}

	/**
	 * Analyze long-tail keyword usage across pages
	 *
	 * @since 0.6093.1200
	 * @param  array $pages Pages to analyze.
	 * @return array Analysis results with percentage and examples
	 */
	private static function analyze_long_tail_usage( array $pages ): array {
		$pages_with_long_tail = 0;
		$examples             = array();

		foreach ( $pages as $page ) {
			$title   = strtolower( $page['title'] );
			$heading = self::extract_heading( $page['content'] );

			// Count words in title
			$word_count = count( array_filter( explode( ' ', $title ) ) );

			// Check for specific, intent-driven keywords (not generic)
			$has_specificity = self::check_specificity( $title . ' ' . $heading );

			if ( $word_count >= 3 && $has_specificity ) {
				$pages_with_long_tail++;

				if ( count( $examples ) < 3 ) {
					$examples[] = array(
						'title'   => $page['title'],
						'length'  => $word_count . ' words',
						'type'    => 'Good long-tail keyword usage',
					);
				}
			}
		}

		$percentage = ! empty( $pages ) ? round( ( $pages_with_long_tail / count( $pages ) ) * 100 ) : 0;

		return array(
			'count'      => $pages_with_long_tail,
			'percentage' => $percentage,
			'examples'   => $examples,
		);
	}

	/**
	 * Extract main heading from content
	 *
	 * @since 0.6093.1200
	 * @param  string $content Page content.
	 * @return string First H1 or H2 tag content
	 */
	private static function extract_heading( string $content ): string {
		if ( preg_match( '/<h[1-2][^>]*>([^<]+)<\/h[1-2]>/i', $content, $matches ) ) {
			return $matches[1];
		}

		return '';
	}

	/**
	 * Check if keywords show specificity (not generic)
	 *
	 * @since 0.6093.1200
	 * @param  string $text Text to check.
	 * @return bool True if text contains specific keyword patterns
	 */
	private static function check_specificity( string $text ): bool {
		// Look for location modifiers
		if ( preg_match( '/near|in|local|area|city|region|county|state/i', $text ) ) {
			return true;
		}

		// Look for problem/solution language
		if ( preg_match( '/best|fast|quick|emergency|urgent|professional|certified|affordable/i', $text ) ) {
			return true;
		}

		// Look for question format (intent-based)
		if ( preg_match( '/how|why|what|where|when|tips|guide|tutorial|learn/i', $text ) ) {
			return true;
		}

		return false;
	}
}
