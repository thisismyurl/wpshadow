<?php
/**
 * Product Videos Created Diagnostic
 *
 * Tests whether the site includes video demonstrations for products to improve
 * understanding and reduce returns. Product videos dramatically increase conversion.
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
 * Diagnostic_Includes_Product_Videos Class
 *
 * Diagnostic #2: Product Videos Created from Specialized & Emerging Success Habits.
 * Checks if the site includes product video demonstrations.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Includes_Product_Videos extends Diagnostic_Base {

	protected static $slug = 'includes-product-videos';
	protected static $title = 'Product Videos Created';
	protected static $description = 'Tests whether the site includes video demonstrations for products';
	protected static $family = 'ecommerce-optimization';

	public static function check() {
		$score          = 0;
		$max_score      = 5;
		$score_details  = array();
		$recommendations = array();

		// Check WooCommerce active.
		if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			return null;
		}

		// Check video embeds in product content.
		$products_with_videos = get_posts(
			array(
				'post_type'      => 'product',
				'posts_per_page' => 50,
				'post_status'    => 'publish',
				's'              => 'youtube.com youtu.be vimeo.com',
			)
		);

		if ( count( $products_with_videos ) >= 10 ) {
			$score += 2;
			$score_details[] = sprintf(
				/* translators: %d: number of products */
				__( '✓ %d+ products with video content', 'wpshadow' ),
				count( $products_with_videos )
			);
		} elseif ( ! empty( $products_with_videos ) ) {
			++$score;
			$score_details[]   = sprintf( __( '◐ %d product(s) with videos', 'wpshadow' ), count( $products_with_videos ) );
			$recommendations[] = __( 'Add videos to more products, especially complex or high-value items', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No product videos detected', 'wpshadow' );
			$recommendations[] = __( 'Create demonstration videos showing products in use', 'wpshadow' );
		}

		// Check video gallery plugins.
		$video_plugins = array(
			'yith-woocommerce-featured-video/init.php',
			'woo-product-video-gallery/woo-product-video-gallery.php',
		);

		$has_video_plugin = false;
		foreach ( $video_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_video_plugin = true;
				++$score;
				$score_details[] = __( '✓ Product video plugin active', 'wpshadow' );
				break;
			}
		}

		if ( ! $has_video_plugin ) {
			$score_details[]   = __( '✗ No video gallery plugin', 'wpshadow' );
			$recommendations[] = __( 'Install a product video plugin to integrate videos in product galleries', 'wpshadow' );
		}

		// Check video pages/gallery.
		$video_content = get_posts(
			array(
				'post_type'      => 'page',
				'posts_per_page' => 5,
				'post_status'    => 'publish',
				's'              => 'video gallery demonstration',
			)
		);

		if ( ! empty( $video_content ) ) {
			++$score;
			$score_details[] = __( '✓ Video gallery or demonstration page available', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No dedicated video content area', 'wpshadow' );
			$recommendations[] = __( 'Create a video gallery showcasing product demonstrations', 'wpshadow' );
		}

		// Check YouTube/Vimeo channel mentions.
		$social_video_content = get_posts(
			array(
				'post_type'      => 'any',
				'posts_per_page' => 5,
				'post_status'    => 'publish',
				's'              => 'youtube channel subscribe video',
			)
		);

		if ( ! empty( $social_video_content ) ) {
			++$score;
			$score_details[] = __( '✓ Video channel promotion detected', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No video channel mentioned', 'wpshadow' );
			$recommendations[] = __( 'Link to your YouTube/Vimeo channel for more product videos', 'wpshadow' );
		}

		$score_percentage = ( $score / $max_score ) * 100;

		if ( $score_percentage < 30 ) {
			$severity     = 'medium';
			$threat_level = 20;
		} elseif ( $score_percentage < 60 ) {
			$severity     = 'low';
			$threat_level = 10;
		} else {
			return null;
		}

		return array(
			'id'               => self::$slug,
			'title'            => self::$title,
			'description'      => sprintf(
				/* translators: %d: score percentage */
				__( 'Product video score: %d%%. Product videos increase conversions by 80%% and reduce returns by 35%%. 96%% of consumers watch explainer videos before buying. Products with videos sell 85%% more than products without videos. Video is the #1 requested content type.', 'wpshadow' ),
				$score_percentage
			),
			'severity'         => $severity,
			'threat_level'     => $threat_level,
			'auto_fixable'     => false,
			'kb_link'          => 'https://wpshadow.com/kb/product-videos',
			'details'          => $score_details,
			'recommendations'  => $recommendations,
			'impact'           => __( 'Videos demonstrate products in action, answer questions preemptively, and build confidence in purchase decisions.', 'wpshadow' ),
		);
	}
}
