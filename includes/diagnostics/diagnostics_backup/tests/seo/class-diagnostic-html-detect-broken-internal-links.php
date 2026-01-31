<?php
/**
 * HTML Detect Broken Internal Links Diagnostic
 *
 * Detects broken internal links on the page.
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
 * HTML Detect Broken Internal Links Diagnostic Class
 */
class Diagnostic_Html_Detect_Broken_Internal_Links extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'html-detect-broken-internal-links';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Broken Internal Links';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects broken internal links on the page';

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

		$broken_links = array();

		global $post;

		if ( ! empty( $post ) && $post instanceof \WP_Post ) {
			$content = $post->post_content;

			if ( preg_match_all( '/<a[^>]*href=["\']([^"\']+)["\'][^>]*>([^<]+)<\/a>/i', $content, $matches ) ) {
				foreach ( $matches[1] as $idx => $href ) {
					$anchor_text = trim( $matches[2][ $idx ] );

					// Check for common broken link patterns.
					if ( empty( $href ) || $href === '#' || $href === 'javascript:void(0);' ) {
						$broken_links[] = array(
							'text' => $anchor_text,
							'href' => $href,
							'issue' => 'Empty or void href',
						);
					}

					// Check for 404 patterns or placeholder URLs.
					if ( preg_match( '/example\.com|test\.com|placeholder|404|\/404\//i', $href ) ) {
						$broken_links[] = array(
							'text' => $anchor_text,
							'href' => $href,
							'issue' => 'Broken or placeholder URL',
						);
					}
				}
			}
		}

		if ( empty( $broken_links ) ) {
			return null;
		}

		$items_list = '';
		$max_items  = 5;

		foreach ( array_slice( $broken_links, 0, $max_items ) as $item ) {
			$items_list .= sprintf(
				"\n- \"%s\" → %s (%s)",
				esc_html( substr( $item['text'], 0, 40 ) ),
				esc_html( $item['href'] ),
				esc_html( $item['issue'] )
			);
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %d: count */
				__( 'Found %d broken internal link(s). Users clicking these will see errors or get stuck. Fix by removing the links or updating them to valid pages.%s', 'wpshadow' ),
				count( $broken_links ),
				$items_list
			),
			'severity'     => 'high',
			'threat_level' => 30,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/html-detect-broken-internal-links',
			'meta'         => array(
				'broken_links' => $broken_links,
			),
		);
	}
}
