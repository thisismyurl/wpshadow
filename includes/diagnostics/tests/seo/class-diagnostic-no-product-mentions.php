<?php
/**
 * Missing Product Mentions Diagnostic
 *
 * Tests whether product blogs naturally mention their products. Product content
 * should include natural product references for conversion opportunities.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_No_Product_Mentions Class
 *
 * Detects when blog content doesn't naturally reference products/services,
 * missing conversion opportunities.
 *
 * @since 1.6093.1200
 */
class Diagnostic_No_Product_Mentions extends Diagnostic_Base {

	protected static $slug = 'no-product-mentions';
	protected static $title = 'Missing Product Mentions';
	protected static $description = 'Tests whether blog content naturally mentions products';
	protected static $family = 'conversion';

	public static function check() {
		$score          = 0;
		$max_score      = 3;
		$score_details  = array();
		$recommendations = array();

		// Check if WooCommerce active (product-based site).
		$has_products = is_plugin_active( 'woocommerce/woocommerce.php' );

		if ( ! $has_products ) {
			// Check for other commerce indicators.
			$service_pages = get_posts(
				array(
					'post_type'      => 'page',
					'posts_per_page' => 5,
					'post_status'    => 'publish',
					's'              => 'pricing product service solution',
				)
			);

			if ( empty( $service_pages ) ) {
				// Not a product/service site.
				return null;
			}
		}

		// Check blog posts for product/service mentions.
		$blog_posts = get_posts(
			array(
				'post_type'      => 'post',
				'posts_per_page' => 30,
				'post_status'    => 'publish',
			)
		);

		$posts_with_product_mentions = 0;
		$posts_checked = 0;

		foreach ( $blog_posts as $post ) {
			++$posts_checked;
			$content = strtolower( $post->post_content );
			
			// Look for product/service keywords.
			$product_keywords = array( 'our product', 'our service', 'our solution', 'we offer', 'you can buy', 'purchase', 'pricing', 'get started' );
			
			$has_mention = false;
			foreach ( $product_keywords as $keyword ) {
				if ( strpos( $content, $keyword ) !== false ) {
					$has_mention = true;
					break;
				}
			}

			// Check for product links (if WooCommerce).
			if ( $has_products ) {
				preg_match_all( '/post_type=product/', $content, $product_links );
				if ( ! empty( $product_links[0] ) ) {
					$has_mention = true;
				}
			}

			if ( $has_mention ) {
				++$posts_with_product_mentions;
			}
		}

		// Score based on product mention rate.
		if ( $posts_checked > 0 ) {
			$mention_percentage = ( $posts_with_product_mentions / $posts_checked ) * 100;

			if ( $mention_percentage >= 40 ) {
				$score = 3;
				$score_details[] = sprintf( __( '✓ %d%% of posts mention products/services', 'wpshadow' ), round( $mention_percentage ) );
			} elseif ( $mention_percentage >= 20 ) {
				$score = 2;
				$score_details[]   = sprintf( __( '◐ %d%% of posts mention products', 'wpshadow' ), round( $mention_percentage ) );
				$recommendations[] = __( 'Increase natural product mentions in content', 'wpshadow' );
			} else {
				$score = 1;
				$score_details[]   = sprintf( __( '✗ Only %d%% of posts mention products (%d of %d)', 'wpshadow' ), round( $mention_percentage ), $posts_with_product_mentions, $posts_checked );
				$recommendations[] = __( 'Connect content to products naturally - show how products solve problems discussed', 'wpshadow' );
			}
		}

		$score_percentage = ( $score / $max_score ) * 100;

		if ( $score_percentage >= 70 ) {
			return null;
		}

		$severity = 'low';
		$threat_level = 15;

		return array(
			'id'               => self::$slug,
			'title'            => self::$title,
			'description'      => sprintf(
				/* translators: %d: score percentage */
				__( 'Product mention score: %d%%. Blog content that naturally references products converts 40%% better. Not pushy sales - helpful context: "For this workflow, we use [product] to...", "The easiest way is with [feature] in [product]". Educational content that mentions products converts at 12%% vs 2%% for pure education.', 'wpshadow' ),
				$score_percentage
			),
			'severity'         => $severity,
			'threat_level'     => $threat_level,
			'auto_fixable'     => false,
			'kb_link'          => 'https://wpshadow.com/kb/product-content-integration',
			'details'          => $score_details,
			'recommendations'  => $recommendations,
			'impact'           => __( 'Natural product mentions in content help readers discover solutions while providing conversion opportunities in educational content.', 'wpshadow' ),
		);
	}
}
