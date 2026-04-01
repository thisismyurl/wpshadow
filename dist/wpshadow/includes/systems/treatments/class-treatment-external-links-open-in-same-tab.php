<?php
/**
 * Treatment: Fix External Links Opening in Same Tab
 *
 * Adds target="_blank" and rel="noopener noreferrer" to external links
 * for improved security and better visitor experience.
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
 * Treatment_External_Links_Open_In_Same_Tab Class
 *
 * Ensures external links open in a new tab and use safe rel attributes.
 *
 * @since 0.6093.1200
 */
class Treatment_External_Links_Open_In_Same_Tab extends Treatment_Base {

	/**
	 * Get the finding ID this treatment addresses.
	 *
	 * @since 0.6093.1200
	 * @return string Finding ID.
	 */
	public static function get_finding_id() {
		return 'external-links-open-same-tab';
	}

	/**
	 * Apply the treatment.
	 *
	 * Adds target and rel attributes to external links in posts and pages.
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
		$posts = get_posts(
			array(
				'post_type'      => array( 'post', 'page' ),
				'posts_per_page' => -1,
				'post_status'    => 'publish',
			)
		);

		$updated_posts  = 0;
		$links_updated  = 0;
		$site_host      = wp_parse_url( home_url(), PHP_URL_HOST );
		$updated_samples = array();

		foreach ( $posts as $post ) {
			$content = $post->post_content;
			$original = $content;

			$content = preg_replace_callback(
				'/<a\s+([^>]*?)href\s*=\s*(["\"])([^"\"]+)\2([^>]*)>/i',
				function ( $matches ) use ( $site_host, &$links_updated ) {
					$before_attrs = $matches[1];
					$href         = $matches[3];
					$after_attrs  = $matches[4];

					$link_host = wp_parse_url( $href, PHP_URL_HOST );

					// Skip internal links or malformed URLs.
					if ( empty( $link_host ) || $link_host === $site_host ) {
						return $matches[0];
					}

					$attrs = trim( $before_attrs . ' href="' . esc_url( $href ) . '" ' . $after_attrs );

					$has_target = preg_match( '/\btarget\s*=\s*["\"]?_blank["\"]?/i', $attrs );
					$has_rel    = preg_match( '/\brel\s*=\s*["\"][^"\"]*["\"]/i', $attrs, $rel_match );

					if ( ! $has_target ) {
						$attrs .= ' target="_blank"';
					}

					if ( $has_rel ) {
						$rel_value = $rel_match[0];
						$rel_clean = preg_replace( '/\brel\s*=\s*["\"]/', '', $rel_value );
						$rel_clean = preg_replace( '/["\"]$/', '', $rel_clean );
						$rel_parts = array_filter( array_unique( preg_split( '/\s+/', $rel_clean ) ) );

						if ( ! in_array( 'noopener', $rel_parts, true ) ) {
							$rel_parts[] = 'noopener';
						}
						if ( ! in_array( 'noreferrer', $rel_parts, true ) ) {
							$rel_parts[] = 'noreferrer';
						}

						$attrs = preg_replace( '/\brel\s*=\s*["\"][^"\"]*["\"]/', 'rel="' . esc_attr( implode( ' ', $rel_parts ) ) . '"', $attrs );
					} else {
						$attrs .= ' rel="noopener noreferrer"';
					}

					$links_updated++;

					return '<a ' . trim( $attrs ) . '>';
				},
				$content
			);

			if ( $content !== $original ) {
				wp_update_post(
					array(
						'ID'           => $post->ID,
						'post_content' => $content,
					)
				);

				$updated_posts++;
				$updated_samples[] = array(
					'id'    => $post->ID,
					'title' => $post->post_title,
				);
			}
		}

		if ( $links_updated > 0 ) {
			return array(
				'success' => true,
				'message' => sprintf(
					/* translators: 1: number of links updated, 2: number of posts updated */
					__( 'Updated %1$d external links across %2$d posts and pages. External links now open safely in a new tab.', 'wpshadow' ),
					$links_updated,
					$updated_posts
				),
				'details' => array(
					'links_updated' => $links_updated,
					'posts_updated' => $updated_posts,
					'samples'       => array_slice( $updated_samples, 0, 10 ),
				),
			);
		}

		return array(
			'success' => false,
			'message' => __( 'No external links needed updates. Everything already opens safely in a new tab.', 'wpshadow' ),
		);
	}
}
