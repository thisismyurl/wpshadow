<?php
/**
 * Diagnostic: Video Sales Letters
 *
 * Tests whether the site uses video to explain products and increase
 * conversion rates by 80%.
 *
 * Issue: https://github.com/thisismyurl/wpshadow/issues/4539
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Behavioral
 * @since      1.6034.1445
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Video Sales Letters Diagnostic
 *
 * Checks if product/service pages use video explanations. Video increases
 * understanding and conversion rates significantly vs text-only pages.
 *
 * @since 1.6034.1445
 */
class Diagnostic_Behavioral_Video_Sales_Letters extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'creates-video-sales-letters';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Video Sales Letters';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether site uses video to explain products/services';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'behavioral';

	/**
	 * Check for video sales implementation.
	 *
	 * Looks for video embeds on product, service, and landing pages.
	 *
	 * @since  1.6034.1445
	 * @return array|null Finding array if missing, null if present.
	 */
	public static function check() {
		$has_video = false;

		// Check for video plugins.
		$video_plugins = array(
			'presto-player/presto-player.php',
			'video-conferencing-with-zoom-api/video-conferencing-with-zoom-api.php',
			'wp-video-lightbox/wp-video-lightbox.php',
		);

		foreach ( $video_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_video = true;
				break;
			}
		}

		// Check for embedded video services.
		$post_types = array( 'page', 'product' );
		
		foreach ( $post_types as $post_type ) {
			$posts = get_posts(
				array(
					'post_type'      => $post_type,
					'posts_per_page' => 20,
					'post_status'    => 'publish',
				)
			);

			foreach ( $posts as $post ) {
				$content = $post->post_content;
				
				// Check for video embeds.
				$video_patterns = array(
					'youtube\.com',
					'youtu\.be',
					'vimeo\.com',
					'wistia\.com',
					'vidyard\.com',
					'<video',
					'\[video\s',
				);

				foreach ( $video_patterns as $pattern ) {
					if ( preg_match( '/' . $pattern . '/i', $content ) ) {
						$has_video = true;
						break 3; // Exit all loops.
					}
				}
			}
		}

		if ( $has_video ) {
			return null;
		}

		// Only recommend if site sells products/services.
		$needs_video = false;
		
		if ( class_exists( 'WooCommerce' ) ) {
			$needs_video = true;
		}

		// Check for sales/service pages.
		$sales_keywords = array( 'pricing', 'product', 'service', 'solution', 'package' );
		$pages          = get_pages( array( 'number' => 50 ) );
		
		foreach ( $pages as $page ) {
			foreach ( $sales_keywords as $keyword ) {
				if ( stripos( $page->post_title, $keyword ) !== false || stripos( $page->post_name, $keyword ) !== false ) {
					$needs_video = true;
					break 2;
				}
			}
		}

		if ( ! $needs_video ) {
			return null; // Content site, less critical.
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => __(
				'No video content detected on product/service pages. Video sales letters increase conversion rates by 80% compared to text alone. Videos explain complex offerings, build trust through personal connection, and address objections visually. Consider adding explainer videos to key landing pages.',
				'wpshadow'
			),
			'severity'     => 'low',
			'threat_level' => 32,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/video-sales-letters',
		);
	}
}
