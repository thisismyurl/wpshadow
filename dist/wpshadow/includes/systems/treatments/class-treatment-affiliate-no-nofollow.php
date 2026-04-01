<?php
/**
 * Treatment: Add Nofollow/Sponsored to Affiliate Links
 *
 * Automatically adds rel="sponsored" or rel="nofollow" to affiliate links
 * to comply with FTC and Google requirements.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Affiliate_No_Nofollow Class
 *
 * Adds proper rel attributes to affiliate links.
 *
 * @since 0.6093.1200
 */
class Treatment_Affiliate_No_Nofollow extends Treatment_Base {

	/**
	 * Get the finding ID this treatment addresses.
	 *
	 * @since 0.6093.1200
	 * @return string Finding ID.
	 */
	public static function get_finding_id() {
		return 'affiliate-no-nofollow';
	}

	/**
	 * Apply the treatment.
	 *
	 * Scans all posts for affiliate links and adds rel="sponsored" attribute.
	 *
	 * @since 0.6093.1200
	 * @return array {
	 *     Result array.
	 *
	 *     @type bool   $success Whether treatment succeeded.
	 *     @type string $message Human-readable result message.
	 *     @type array  $details Additional details about changes made.
	 * }
	 */
	public static function apply() {
		// Affiliate URL patterns.
		$affiliate_patterns = array(
			'amazon.com',
			'amzn.to',
			'shareasale.com',
			'clickbank',
			'jvzoo.com',
			'warrior.com/aff',
			'affiliate',
			'/aff/',
			'?ref=',
			'?aff=',
			'/ref/',
		);

		$posts = get_posts(
			array(
				'post_type'      => 'post',
				'posts_per_page' => -1,
				'post_status'    => 'publish',
			)
		);

		$updated_count = 0;
		$link_count    = 0;
		$updated_posts = array();

		foreach ( $posts as $post ) {
			$content = $post->post_content;
			$updated = false;

			// Extract all links.
			preg_match_all( '/<a\s+([^>]*?)href=["\']([^"\']+)["\']([^>]*?)>/i', $content, $matches, PREG_SET_ORDER );

			foreach ( $matches as $match ) {
				$full_tag      = $match[0];
				$before_href   = $match[1];
				$url           = $match[2];
				$after_href    = $match[3];
				$is_affiliate  = false;

				// Check if URL matches affiliate patterns.
				foreach ( $affiliate_patterns as $pattern ) {
					if ( stripos( $url, $pattern ) !== false ) {
						$is_affiliate = true;
						break;
					}
				}

				if ( ! $is_affiliate ) {
					continue;
				}

				// Check if already has rel="sponsored" or rel="nofollow".
				$all_attrs = $before_href . ' ' . $after_href;
				if ( stripos( $all_attrs, 'rel=' ) !== false ) {
					if ( stripos( $all_attrs, 'sponsored' ) !== false || stripos( $all_attrs, 'nofollow' ) !== false ) {
						continue; // Already compliant.
					}

					// Has rel but not compliant - update it.
					$new_tag = preg_replace( '/rel=["\'][^"\']*["\']/i', 'rel="sponsored nofollow"', $full_tag );
				} else {
					// No rel attribute - add it before closing >.
					$new_tag = str_replace( '>', ' rel="sponsored nofollow">', $full_tag );
				}

				$content = str_replace( $full_tag, $new_tag, $content );
				$updated = true;
				$link_count++;
			}

			if ( $updated ) {
				wp_update_post(
					array(
						'ID'           => $post->ID,
						'post_content' => $content,
					)
				);
				$updated_count++;
				$updated_posts[] = array(
					'id'    => $post->ID,
					'title' => $post->post_title,
					'links' => $link_count,
				);
			}
		}

		if ( $updated_count > 0 ) {
			return array(
				'success' => true,
				'message' => sprintf(
					/* translators: 1: number of posts updated, 2: number of links fixed */
					__( 'Updated %1$d posts with %2$d affiliate links now compliant with FTC/Google requirements.', 'wpshadow' ),
					$updated_count,
					$link_count
				),
				'details' => array(
					'posts_updated' => $updated_count,
					'links_fixed'   => $link_count,
					'updated_posts' => array_slice( $updated_posts, 0, 10 ), // First 10 for display.
				),
			);
		}

		return array(
			'success' => false,
			'message' => __( 'No affiliate links found requiring updates. All affiliate links are already compliant.', 'wpshadow' ),
		);
	}
}
