<?php
/**
 * Glossary Content Processor
 *
 * Processes post content and injects glossary term tooltips.
 *
 * @package WPShadow
 * @subpackage Glossary
 */

declare(strict_types=1);

namespace WPShadow\Glossary;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Glossary_Content_Processor class.
 */
class Glossary_Content_Processor {
	/**
	 * Cache of processed glossary terms.
	 *
	 * @var array
	 */
	private static $glossary_cache = [];

	/**
	 * Initialize content processor.
	 */
	public static function init(): void {
		add_filter( 'the_content', [ __CLASS__, 'process_content' ], 15 );
	}

	/**
	 * Process post content and inject glossary tooltips.
	 *
	 * @param string $content The post content.
	 * @return string
	 */
	public static function process_content( $content ): string {
		if ( is_admin() || is_feed() || ! is_singular() ) {
			return $content;
		}

		// Get all glossary terms
		$glossary_terms = self::get_glossary_terms();
		if ( empty( $glossary_terms ) ) {
			return $content;
		}

		// Process each term
		foreach ( $glossary_terms as $term ) {
			$content = self::inject_tooltip( $content, $term );
		}

		return $content;
	}

	/**
	 * Get all enabled glossary terms.
	 *
	 * @return array
	 */
	private static function get_glossary_terms(): array {
		// Check cache first
		$cache_key = 'wpshadow_glossary_terms_cache';
		$cached     = wp_cache_get( $cache_key );
		if ( false !== $cached ) {
			return $cached;
		}

		$args = [
			'post_type'      => 'wpshadow_glossary',
			'posts_per_page' => -1,
			'post_status'    => 'publish',
			'meta_query'     => [
				[
					'key'   => 'wpshadow_glossary_tooltip_enabled',
					'value' => '1',
				],
			],
		];

		$query = new \WP_Query( $args );
		$terms = [];

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$post_id = get_the_ID();

				$variations        = get_post_meta( $post_id, 'wpshadow_glossary_variations', true );
				$case_sensitive    = get_post_meta( $post_id, 'wpshadow_glossary_case_sensitive', true );
				$excerpt           = wp_strip_all_tags( get_the_excerpt() );
				$glossary_page_url = get_the_permalink();

				if ( is_array( $variations ) && ! empty( $variations ) ) {
					foreach ( $variations as $variation ) {
						$terms[] = [
							'term'            => $variation,
							'excerpt'         => $excerpt,
							'url'             => $glossary_page_url,
							'case_sensitive'  => '1' === $case_sensitive,
							'post_id'         => $post_id,
						];
					}
				}
			}
			wp_reset_postdata();
		}

		// Cache for 1 hour
		wp_cache_set( $cache_key, $terms, '', 3600 );

		return $terms;
	}

	/**
	 * Inject tooltip for a single glossary term.
	 *
	 * @param string $content The content.
	 * @param array  $term    The glossary term data.
	 * @return string
	 */
	private static function inject_tooltip( $content, $term ): string {
		$search_term = $term['term'];
		$excerpt     = $term['excerpt'];
		$url         = $term['url'];

		if ( ! $term['case_sensitive'] ) {
			// Case insensitive search
			$pattern = '/\b' . preg_quote( $search_term, '/' ) . '\b/i';
			$replace = self::get_tooltip_html( $search_term, $excerpt, $url );
			$content = preg_replace_callback(
				$pattern,
				function() use ( $replace ) {
					return $replace;
				},
				$content
			);
		} else {
			// Case sensitive search
			$pattern = '/\b' . preg_quote( $search_term, '/' ) . '\b/';
			$replace = self::get_tooltip_html( $search_term, $excerpt, $url );
			$content = preg_replace_callback(
				$pattern,
				function() use ( $replace ) {
					return $replace;
				},
				$content
			);
		}

		return $content;
	}

	/**
	 * Get tooltip HTML markup.
	 *
	 * @param string $term    The glossary term.
	 * @param string $excerpt The term excerpt.
	 * @param string $url     The glossary page URL.
	 * @return string
	 */
	private static function get_tooltip_html( $term, $excerpt, $url ): string {
		$excerpt_display = wp_strip_all_tags( $excerpt );
		if ( strlen( $excerpt_display ) > 150 ) {
			$excerpt_display = substr( $excerpt_display, 0, 150 ) . '...';
		}

		return sprintf(
			'<span class="wpshadow-glossary-term" data-term="%s" data-excerpt="%s" data-url="%s" title="%s">%s</span>',
			esc_attr( $term ),
			esc_attr( $excerpt_display ),
			esc_attr( $url ),
			esc_attr( $excerpt_display ),
			esc_html( $term )
		);
	}
}
