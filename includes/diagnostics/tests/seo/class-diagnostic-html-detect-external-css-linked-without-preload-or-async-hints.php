<?php
/**
 * HTML Detect External CSS Without Preload Hints Diagnostic
 *
 * Detects external CSS linked without preload or async hints.
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
 * HTML Detect External CSS Linked Without Preload Hints Diagnostic Class
 *
 * Identifies external CSS files that could benefit from preload hints
 * or async loading to improve performance.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Html_Detect_External_Css_Linked_Without_Preload_Or_Async_Hints extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'html-detect-external-css-linked-without-preload-or-async-hints';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'External CSS Without Preload Hints';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects external CSS files missing preload or async optimization hints';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

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

		$missing_hints = array();

		// Check WordPress styles.
		global $wp_styles;

		if ( ! empty( $wp_styles ) && isset( $wp_styles->registered ) ) {
			foreach ( $wp_styles->registered as $handle => $style_obj ) {
				if ( isset( $style_obj->src ) && ! empty( $style_obj->src ) ) {
					$src = (string) $style_obj->src;

					// Only care about external CSS.
					if ( strpos( $src, 'http' ) === 0 || strpos( $src, '//' ) === 0 ) {
						// Check if it has preload or async hint.
						$has_preload = isset( $style_obj->extra['preload'] ) && $style_obj->extra['preload'];
						$has_async   = isset( $style_obj->extra['async'] ) && $style_obj->extra['async'];
						$has_href    = isset( $style_obj->extra['data'] ) && strpos( $style_obj->extra['data'], 'rel="preload"' ) !== false;

						if ( ! $has_preload && ! $has_async && ! $has_href ) {
							$missing_hints[] = array(
								'handle' => $handle,
								'src'    => $src,
								'domain' => parse_url( $src, PHP_URL_HOST ),
								'issue'  => __( 'External CSS missing preload or async hint', 'wpshadow' ),
							);
						}
					}
				}
			}
		}

		// Check inline style data for external links.
		if ( ! empty( $wp_styles ) && isset( $wp_styles->registered ) ) {
			foreach ( $wp_styles->registered as $handle => $style_obj ) {
				if ( isset( $style_obj->extra['data'] ) ) {
					$data = (string) $style_obj->extra['data'];

					// Find <link> tags.
					if ( preg_match_all( '/<link[^>]*href=["\']([^"\']*https?:\/\/[^"\']+)["\'][^>]*>/i', $data, $matches ) ) {
						foreach ( $matches[1] as $href ) {
							// Check if already has preload or async.
							if ( ! preg_match( '/rel=["\']?preload["\']?|async|async/i', $data ) ) {
								$missing_hints[] = array(
									'handle' => $handle,
									'src'    => $href,
									'domain' => parse_url( $href, PHP_URL_HOST ),
									'issue'  => __( 'External CSS from CDN should use preload or async', 'wpshadow' ),
								);
							}
						}
					}
				}
			}
		}

		if ( empty( $missing_hints ) ) {
			return null;
		}

		$items_list = '';
		$max_items  = 5;

		foreach ( array_slice( $missing_hints, 0, $max_items ) as $item ) {
			$items_list .= sprintf(
				"\n- %s (%s)",
				esc_html( $item['handle'] ),
				esc_html( $item['domain'] ?? 'external' )
			);
		}

		if ( count( $missing_hints ) > $max_items ) {
			$items_list .= sprintf(
				/* translators: %d: count */
				__( "\n...and %d more external stylesheets", 'wpshadow' ),
				count( $missing_hints ) - $max_items
			);
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: 1: count, 2: list */
				__( 'Found %1$d external CSS file(s) without preload hints. External stylesheets block rendering until fetched. Use <link rel="preload" as="style"> to tell the browser to load CSS earlier, or use async attribute for non-critical styles.%2$s', 'wpshadow' ),
				count( $missing_hints ),
				$items_list
			),
			'severity'     => 'low',
			'threat_level' => 30,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/html-detect-external-css-linked-without-preload-or-async-hints',
			'meta'         => array(
				'css_files' => $missing_hints,
			),
		);
	}
}
