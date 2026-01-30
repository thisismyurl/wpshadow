<?php
/**
 * HTML Detect Unsafe External Links Missing Rel Noopener Diagnostic
 *
 * Detects external links missing security attributes.
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
 * HTML Detect Unsafe External Links Missing Rel Noopener Diagnostic Class
 */
class Diagnostic_Html_Detect_Unsafe_External_Links_Missing_Relnoopener extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'html-detect-unsafe-external-links-missing-relnoopener';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Unsafe External Links (Missing rel="noopener")';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects external links missing rel="noopener"';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

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

		$unsafe_links = array();

		if ( preg_match_all( '/<a[^>]*href=["\']([^"\']+)["\'][^>]*(target=["\']_blank["\'])?[^>]*>/i', $content, $matches ) ) {
			foreach ( $matches[1] as $idx => $href ) {
				$full_tag = $matches[0][ $idx ];
				$target_blank = ! empty( $matches[2][ $idx ] );

				// Only check external links with target="_blank".
				if ( $target_blank && strpos( $href, $site_url ) !== 0 && strpos( $href, '/' ) !== 0 ) {
					// Check for rel="noopener".
					if ( ! preg_match( '/rel=["\']([^"\']*noopener[^"\']*)["\']/', $full_tag ) ) {
						$unsafe_links[] = array(
							'href' => substr( $href, 0, 60 ),
							'issue' => 'target="_blank" without rel="noopener"',
						);
					}
				}
			}
		}

		if ( empty( $unsafe_links ) ) {
			return null;
		}

		$items_list = '';
		$max_items  = 5;

		foreach ( array_slice( $unsafe_links, 0, $max_items ) as $item ) {
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
				__( 'Found %d external link(s) with target="_blank" but missing rel="noopener". This is a security/privacy vulnerability—the linked site can access your site via the window.opener property. Always add rel="noopener noreferrer" to external links that open in a new tab.%s', 'wpshadow' ),
				count( $unsafe_links ),
				$items_list
			),
			'severity'     => 'high',
			'threat_level' => 40,
			'auto_fixable' => true,
			'kb_link'      => 'https://wpshadow.com/kb/html-detect-unsafe-external-links-missing-relnoopener',
			'meta'         => array(
				'unsafe_links' => $unsafe_links,
			),
		);
	}
}
