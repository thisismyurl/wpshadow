<?php
/**
 * HTML Detect Too Many External Links Diagnostic
 *
 * Detects excessive external links on a page.
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
 * HTML Detect Too Many External Links Diagnostic Class
 */
class Diagnostic_Html_Detect_Too_Many_External_Links extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'html-detect-too-many-external-links';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Too Many External Links';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects excessive external links';

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

		global $post;

		if ( empty( $post ) || ! ( $post instanceof \WP_Post ) ) {
			return null;
		}

		$content = $post->post_content;
		$site_url = home_url();

		$external_links = array();

		if ( preg_match_all( '/<a[^>]*href=["\']([^"\']+)["\'][^>]*>([^<]+)<\/a>/i', $content, $matches ) ) {
			foreach ( $matches[1] as $idx => $href ) {
				// Check if external link.
				if ( strpos( $href, $site_url ) !== 0 && strpos( $href, '/' ) !== 0 && strpos( $href, '#' ) !== 0 ) {
					$external_links[] = array(
						'href' => substr( $href, 0, 60 ),
						'text' => trim( $matches[2][ $idx ] ),
					);
				}
			}
		}

		// More than 20 external links is excessive.
		if ( count( $external_links ) > 20 ) {
			$items_list = '';
			$max_items  = 5;

			foreach ( array_slice( $external_links, 0, $max_items ) as $item ) {
				$items_list .= sprintf(
					"\n- %s",
					esc_html( $item['href'] )
				);
			}

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: count */
					__( 'Found %d external link(s), which is excessive. Too many external links dilute your page\'s SEO value and can hurt rankings. Remove unnecessary external links or convert internal references where possible.%s', 'wpshadow' ),
					count( $external_links ),
					$items_list
				),
				'severity'     => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/html-detect-too-many-external-links',
				'meta'         => array(
					'external_count' => count( $external_links ),
					'external_links' => array_slice( $external_links, 0, 10 ),
				),
			);
		}

		return null;
	}
}
