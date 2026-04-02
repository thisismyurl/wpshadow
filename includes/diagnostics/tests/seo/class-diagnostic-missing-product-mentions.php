<?php
/**
 * Missing Product Mentions Diagnostic
 *
 * Detects content that doesn't mention your products or services, representing
 * missed opportunities to showcase your offerings.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Content
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Missing Product Mentions Diagnostic Class
 *
 * Analyzes content for product/service mentions to ensure brand visibility
 * and natural integration of offerings throughout your content strategy.
 *
 * **Why This Matters:**
 * - Content should naturally integrate your offerings
 * - Product mentions increase brand awareness
 * - Helps readers discover relevant solutions
 * - Improves conversion funnel
 *
 * **What's Checked:**
 * - WooCommerce products
 * - Custom product post types
 * - Product names in site title/tagline
 * - Service-related keywords
 *
 * @since 1.6093.1200
 */
class Diagnostic_Missing_Product_Mentions extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'missing-product-mentions';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Missing Product Mentions';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects content that doesn\'t mention your products or services';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'content';

	/**
	 * Run the diagnostic check
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if posts lack product mentions, null otherwise.
	 */
	public static function check() {
		// Get product/service keywords
		$product_keywords = self::get_product_keywords();

		if ( empty( $product_keywords ) ) {
			return null; // No products/services configured
		}

		$posts = get_posts(
			array(
				'post_type'      => 'post',
				'post_status'    => 'publish',
				'posts_per_page' => 50,
				'orderby'        => 'date',
				'order'          => 'DESC',
			)
		);

		if ( empty( $posts ) ) {
			return null;
		}

		$posts_without_mentions = array();

		foreach ( $posts as $post ) {
			$content = strtolower( wp_strip_all_tags( $post->post_content . ' ' . $post->post_title ) );
			$has_mention = false;

			foreach ( $product_keywords as $keyword ) {
				if ( strpos( $content, strtolower( $keyword ) ) !== false ) {
					$has_mention = true;
					break;
				}
			}

			if ( ! $has_mention ) {
				$posts_without_mentions[] = array(
					'id'    => $post->ID,
					'title' => $post->post_title,
					'date'  => get_the_date( '', $post ),
				);
			}
		}

		if ( empty( $posts_without_mentions ) ) {
			return null;
		}

		$count = count( $posts_without_mentions );
		$percentage = round( ( $count / count( $posts ) ) * 100 );

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: 1: number of posts, 2: percentage */
				__( '%1$d post(s) (%2$d%%) don\'t mention your products or services. Consider natural integration opportunities.', 'wpshadow' ),
				$count,
				$percentage
			),
			'severity'     => 'medium',
			'threat_level' => 55,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/content-product-mentions',
			'details'      => array(
				'posts_without_mentions' => $count,
				'percentage'             => $percentage,
				'sample_posts'           => array_slice( $posts_without_mentions, 0, 10 ),
				'product_keywords'       => $product_keywords,
			),
		);
	}

	/**
	 * Get product/service keywords from various sources
	 *
	 * @since 1.6093.1200
	 * @return array Array of product/service keywords.
	 */
	private static function get_product_keywords() {
		$keywords = array();

		// WooCommerce products
		if ( class_exists( 'WooCommerce' ) ) {
			$products = get_posts(
				array(
					'post_type'      => 'product',
					'post_status'    => 'publish',
					'posts_per_page' => 20,
				)
			);

			foreach ( $products as $product ) {
				$keywords[] = $product->post_title;
			}
		}

		// Site name (often contains company/product name)
		$site_name = get_bloginfo( 'name' );
		if ( $site_name ) {
			$keywords[] = $site_name;
		}

		// Tagline (often describes services)
		$tagline = get_bloginfo( 'description' );
		if ( $tagline ) {
			$tagline_words = explode( ' ', $tagline );
			foreach ( $tagline_words as $word ) {
				if ( strlen( $word ) > 5 ) {
					$keywords[] = $word;
				}
			}
		}

		return array_unique( array_filter( $keywords ) );
	}
}
