<?php
/**
 * HTML Detect Generic Anchor Text Like Click Here Diagnostic
 *
 * Detects generic, non-descriptive anchor text.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\HTML
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * HTML Detect Generic Anchor Text Like Click Here Diagnostic Class
 *
 * Identifies pages with generic, non-descriptive anchor text that harms
 * SEO and accessibility.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Html_Detect_Generic_Anchor_Text_Like_Click_Here extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'html-detect-generic-anchor-text-like-click-here';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Generic Anchor Text Detected';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects non-descriptive anchor text like "click here"';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if ( is_admin() ) {
			return null;
		}

		$generic_anchors = array();

		// Generic anchor text patterns that are non-descriptive.
		$generic_patterns = array(
			'/^click\s+here$/i'         => 'click here',
			'/^learn\s+more$/i'         => 'learn more',
			'/^read\s+more$/i'          => 'read more',
			'/^more$/i'                 => 'more',
			'/^link$/i'                 => 'link',
			'/^here$/i'                 => 'here',
			'/^continue$/i'             => 'continue',
			'/^visit$/i'                => 'visit',
			'/^find\s+out\s+more$/i'    => 'find out more',
			'/^go$/i'                   => 'go',
			'/^submit$/i'               => 'submit',
		);

		// Check post content for links.
		global $post;

		if ( ! empty( $post ) && $post instanceof \WP_Post ) {
			$content = $post->post_content;

			// Find all anchor tags.
			if ( preg_match_all( '/<a[^>]*href=["\']([^"\']+)["\'][^>]*>([^<]+)<\/a>/i', $content, $matches ) ) {
				foreach ( $matches[1] as $idx => $href ) {
					$anchor_text = trim( $matches[2][ $idx ] );

					if ( empty( $anchor_text ) ) {
						continue;
					}

					// Check if anchor text matches generic patterns.
					foreach ( $generic_patterns as $pattern => $label ) {
						if ( preg_match( $pattern, $anchor_text ) ) {
							$generic_anchors[] = array(
								'text'    => $anchor_text,
								'href'    => substr( $href, 0, 60 ),
								'pattern' => $label,
							);

							break;
						}
					}
				}
			}
		}

		if ( empty( $generic_anchors ) ) {
			return null;
		}

		$items_list = '';
		$max_items  = 5;

		foreach ( array_slice( $generic_anchors, 0, $max_items ) as $item ) {
			$items_list .= sprintf(
				"\n- \"%s\" linking to %s",
				esc_html( $item['text'] ),
				esc_html( $item['href'] )
			);
		}

		if ( count( $generic_anchors ) > $max_items ) {
			$items_list .= sprintf(
				/* translators: %d: count */
				__( "\n...and %d more generic links", 'wpshadow' ),
				count( $generic_anchors ) - $max_items
			);
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: 1: count, 2: list */
				__( 'Found %1$d generic anchor text(s). Links with generic text like "click here" or "learn more" hurt both SEO and accessibility. Screen reader users lose context; search engines don\'t understand link purpose. Use descriptive text that explains what users will find: instead of "click here", write "learn about our SEO services".%2$s', 'wpshadow' ),
				count( $generic_anchors ),
				$items_list
			),
			'severity'     => 'low',
			'threat_level' => 30,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/html-detect-generic-anchor-text-like-click-here',
			'meta'         => array(
				'generic_anchors' => $generic_anchors,
			),
		);
	}
}
