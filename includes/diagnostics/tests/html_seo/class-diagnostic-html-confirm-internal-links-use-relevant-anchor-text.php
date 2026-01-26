<?php
/**
 * HTML Confirm Internal Links Use Relevant Anchor Text Diagnostic
 *
 * Validates internal links have relevant anchor text.
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
 * HTML Confirm Internal Links Use Relevant Anchor Text Diagnostic Class
 */
class Diagnostic_Html_Confirm_Internal_Links_Use_Relevant_Anchor_Text extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'html-confirm-internal-links-use-relevant-anchor-text';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Internal Links Missing Descriptive Text';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates internal links use relevant anchor text';

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

		$poor_internal_links = array();
		$generic_patterns    = array( '/^click\s+here$/i', '/^more$/i', '/^here$/i', '/^link$/i', '/^visit$/i' );

		global $post;

		if ( ! empty( $post ) && $post instanceof \WP_Post ) {
			$content = $post->post_content;
			$site_url = home_url();

			if ( preg_match_all( '/<a[^>]*href=["\']([^"\']+)["\'][^>]*>([^<]+)<\/a>/i', $content, $matches ) ) {
				foreach ( $matches[1] as $idx => $href ) {
					$anchor_text = trim( $matches[2][ $idx ] );

					// Check if internal link.
					if ( strpos( $href, $site_url ) === 0 || strpos( $href, '/' ) === 0 ) {
						// Check if anchor text is generic.
						foreach ( $generic_patterns as $pattern ) {
							if ( preg_match( $pattern, $anchor_text ) ) {
								$poor_internal_links[] = array(
									'text' => $anchor_text,
									'href' => substr( $href, 0, 60 ),
								);
								break;
							}
						}
					}
				}
			}
		}

		if ( empty( $poor_internal_links ) ) {
			return null;
		}

		$items_list = '';
		$max_items  = 5;

		foreach ( array_slice( $poor_internal_links, 0, $max_items ) as $item ) {
			$items_list .= sprintf(
				"\n- \"%s\" → %s",
				esc_html( $item['text'] ),
				esc_html( $item['href'] )
			);
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %d: count */
				__( 'Found %d internal link(s) with generic anchor text. Internal links should use descriptive text that explains the destination, helping both SEO and accessibility.%s', 'wpshadow' ),
				count( $poor_internal_links ),
				$items_list
			),
			'severity'     => 'low',
			'threat_level' => 30,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/html-confirm-internal-links-use-relevant-anchor-text',
			'meta'         => array(
				'poor_links' => $poor_internal_links,
			),
		);
	}
}
