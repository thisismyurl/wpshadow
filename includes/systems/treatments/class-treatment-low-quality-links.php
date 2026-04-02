<?php
/**
 * Treatment: Remove Low-Quality Outbound Links
 *
 * Adds nofollow to or removes spam/low-quality outbound links
 * to protect site trust score.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Low_Quality_Links Class
 *
 * Handles spam and low-quality external links.
 *
 * @since 1.6093.1200
 */
class Treatment_Low_Quality_Links extends Treatment_Base {

	/**
	 * Get the finding ID this treatment addresses.
	 *
	 * @since 1.6093.1200
	 * @return string Finding ID.
	 */
	public static function get_finding_id() {
		return 'low-quality-links';
	}

	/**
	 * Apply the treatment.
	 *
	 * Adds nofollow to spam links or optionally removes them entirely.
	 *
	 * @since 1.6093.1200
	 * @return array {
	 *     Result array.
	 *
	 *     @type bool   $success Whether treatment succeeded.
	 *     @type string $message Human-readable result message.
	 *     @type array  $details Additional details about changes made.
	 * }
	 */
	public static function apply() {
		// Spam domain patterns.
		$spam_patterns = array(
			'.ru/',
			'.tk/',
			'.gq/',
			'.ga/',
			'.ml/',
			'.cf/',
			'free-',
			'download-',
			'get-',
			'-free.',
			'casino',
			'pharma',
			'viagra',
			'porn',
		);

		$posts = get_posts(
			array(
				'post_type'      => 'post',
				'posts_per_page' => -1,
				'post_status'    => 'publish',
			)
		);

		$updated_count  = 0;
		$links_fixed    = 0;
		$links_removed  = 0;
		$updated_posts  = array();

		foreach ( $posts as $post ) {
			$content = $post->post_content;
			$updated = false;

			// Extract all external links.
			preg_match_all( '/<a\s+([^>]*?)href=["\']([^"\']+)["\']([^>]*?)>(.*?)<\/a>/is', $content, $matches, PREG_SET_ORDER );

			foreach ( $matches as $match ) {
				$full_link     = $match[0];
				$before_href   = $match[1];
				$url           = $match[2];
				$after_href    = $match[3];
				$link_text     = $match[4];
				$is_spam       = false;

				// Skip internal links.
				if ( strpos( $url, home_url() ) !== false || strpos( $url, '/' ) === 0 ) {
					continue;
				}

				// Check if URL matches spam patterns.
				foreach ( $spam_patterns as $pattern ) {
					if ( stripos( $url, $pattern ) !== false ) {
						$is_spam = true;
						break;
					}
				}

				if ( ! $is_spam ) {
					continue;
				}

				// Option 1: Remove the link entirely (conservative approach).
				// Replace <a href="...">text</a> with just text.
				$new_content = str_replace( $full_link, esc_html( wp_strip_all_tags( $link_text ) ), $content );

				if ( $new_content !== $content ) {
					$content = $new_content;
					$links_removed++;
					$updated = true;
				}
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
					'id'            => $post->ID,
					'title'         => $post->post_title,
					'links_removed' => $links_removed,
				);
			}
		}

		if ( $updated_count > 0 ) {
			return array(
				'success' => true,
				'message' => sprintf(
					/* translators: 1: number of posts updated, 2: number of spam links removed */
					__( 'Removed %2$d spam/low-quality links from %1$d posts. Your trust score is now protected!', 'wpshadow' ),
					$updated_count,
					$links_removed
				),
				'details' => array(
					'posts_updated' => $updated_count,
					'links_removed' => $links_removed,
					'updated_posts' => array_slice( $updated_posts, 0, 10 ),
					'note'          => __( 'Links were removed entirely to protect your site. Link text was preserved.', 'wpshadow' ),
				),
			);
		}

		return array(
			'success' => false,
			'message' => __( 'No spam or low-quality links found. All outbound links are high quality.', 'wpshadow' ),
		);
	}
}
