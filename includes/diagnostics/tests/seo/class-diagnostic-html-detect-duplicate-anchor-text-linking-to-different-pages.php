<?php
/**
 * HTML Detect Duplicate Anchor Text Linking To Different Pages Diagnostic
 *
 * Detects duplicate anchor text pointing to different URLs.
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
 * HTML Detect Duplicate Anchor Text Linking To Different Pages Diagnostic Class
 *
 * Identifies pages with duplicate anchor text that points to different
 * URLs, which confuses search engines about link context.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Html_Detect_Duplicate_Anchor_Text_Linking_To_Different_Pages extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'html-detect-duplicate-anchor-text-linking-to-different-pages';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Duplicate Anchor Text with Different URLs';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects same anchor text linking to different pages';

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

		$anchor_map = array();

		// Check post content for links.
		global $post;

		if ( ! empty( $post ) && $post instanceof \WP_Post ) {
			$content = $post->post_content;

			// Find all anchor tags and their targets.
			if ( preg_match_all( '/<a[^>]*href=["\']([^"\']+)["\'][^>]*>([^<]+)<\/a>/i', $content, $matches ) ) {
				foreach ( $matches[1] as $idx => $href ) {
					$anchor_text = trim( $matches[2][ $idx ] );

					if ( empty( $anchor_text ) ) {
						continue;
					}

					// Normalize URL (remove trailing slash, protocol).
					$href_normalized = rtrim( wp_kses_post( $href ), '/' );

					if ( ! isset( $anchor_map[ $anchor_text ] ) ) {
						$anchor_map[ $anchor_text ] = array();
					}

					$anchor_map[ $anchor_text ][] = $href_normalized;
				}
			}
		}

		$duplicates = array();

		// Find anchors with different URLs.
		foreach ( $anchor_map as $text => $hrefs ) {
			$unique_hrefs = array_unique( $hrefs );

			if ( count( $unique_hrefs ) > 1 ) {
				$duplicates[] = array(
					'anchor_text' => $text,
					'urls'        => $unique_hrefs,
					'count'       => count( $hrefs ),
				);
			}
		}

		if ( ! empty( $duplicates ) ) {
			$items_list = '';
			$max_items  = 3;

			foreach ( array_slice( $duplicates, 0, $max_items ) as $item ) {
				$items_list .= sprintf(
					"\n- \"%s\" → %d different URLs",
					esc_html( substr( $item['anchor_text'], 0, 50 ) ),
					count( $item['urls'] )
				);
			}

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: 1: count, 2: list */
					__( 'Found %1$d case(s) of duplicate anchor text linking to different pages. This confuses search engines about link context and SEO value. Use unique, descriptive anchor text for each link, or if the same text must link to different pages, reconsider the link structure.%2$s', 'wpshadow' ),
					count( $duplicates ),
					$items_list
				),
				'severity'     => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/html-detect-duplicate-anchor-text-linking-to-different-pages',
				'meta'         => array(
					'duplicates' => $duplicates,
				),
			);
		}

		return null;
	}
}
