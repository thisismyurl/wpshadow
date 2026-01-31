<?php
/**
 * HTML Detect Iframe Missing Lazyload Attribute Diagnostic
 *
 * Detects iframes without lazy loading.
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
 * HTML Detect Iframe Missing Lazyload Attribute Diagnostic Class
 *
 * Identifies iframes that could benefit from lazy loading to improve
 * page performance.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Html_Detect_Iframe_Missing_Lazyload_Attribute extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'html-detect-iframe-missing-lazyload-attribute';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Iframes Missing Lazy Load';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects iframes without loading="lazy" attribute';

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

		$iframes_no_lazy = array();

		// Check scripts for iframes.
		global $wp_scripts;

		if ( ! empty( $wp_scripts ) && isset( $wp_scripts->registered ) ) {
			foreach ( $wp_scripts->registered as $handle => $script_obj ) {
				if ( isset( $script_obj->extra['data'] ) ) {
					$data = (string) $script_obj->extra['data'];

					// Find all iframe tags.
					if ( preg_match_all( '/<iframe[^>]*>/i', $data, $matches ) ) {
						foreach ( $matches[0] as $iframe_tag ) {
							// Check if has loading="lazy" attribute.
							if ( ! preg_match( '/loading\s*=\s*["\']?lazy["\']?/i', $iframe_tag ) ) {
								// Extract src if possible.
								$src = '';

								if ( preg_match( '/src=["\']([^"\']+)["\']/', $iframe_tag, $m ) ) {
									$src = $m[1];
								}

								$iframes_no_lazy[] = array(
									'handle' => $handle,
									'src'    => $src,
									'tag'    => substr( $iframe_tag, 0, 60 ),
									'issue'  => __( 'Iframe missing loading="lazy" attribute', 'wpshadow' ),
								);
							}
						}
					}
				}
			}
		}

		// Check post content.
		global $post;

		if ( ! empty( $post ) && $post instanceof \WP_Post ) {
			$content = $post->post_content;

			if ( preg_match_all( '/<iframe[^>]*>/i', $content, $matches ) ) {
				foreach ( $matches[0] as $iframe_tag ) {
					// Check if has loading="lazy" attribute.
					if ( ! preg_match( '/loading\s*=\s*["\']?lazy["\']?/i', $iframe_tag ) ) {
						// Extract src if possible.
						$src = '';

						if ( preg_match( '/src=["\']([^"\']+)["\']/', $iframe_tag, $m ) ) {
							$src = $m[1];
						}

						$iframes_no_lazy[] = array(
							'handle' => 'post_content',
							'src'    => $src,
							'tag'    => substr( $iframe_tag, 0, 60 ),
							'issue'  => __( 'Iframe missing loading="lazy" attribute', 'wpshadow' ),
						);
					}
				}
			}
		}

		if ( empty( $iframes_no_lazy ) ) {
			return null;
		}

		$items_list = '';
		$max_items  = 5;

		foreach ( array_slice( $iframes_no_lazy, 0, $max_items ) as $item ) {
			$source = ! empty( $item['src'] ) ? parse_url( $item['src'], PHP_URL_HOST ) : 'unknown';
			$items_list .= sprintf(
				"\n- %s: %s",
				esc_html( $source ),
				esc_html( $item['issue'] )
			);
		}

		if ( count( $iframes_no_lazy ) > $max_items ) {
			$items_list .= sprintf(
				/* translators: %d: count */
				__( "\n...and %d more iframes without lazy loading", 'wpshadow' ),
				count( $iframes_no_lazy ) - $max_items
			);
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: 1: count, 2: list */
				__( 'Found %1$d iframe(s) without lazy loading attribute. Add loading="lazy" to iframes to defer their loading until needed, which improves page speed, especially for below-the-fold embedded content.%2$s', 'wpshadow' ),
				count( $iframes_no_lazy ),
				$items_list
			),
			'severity'     => 'low',
			'threat_level' => 30,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/html-detect-iframe-missing-lazyload-attribute',
			'meta'         => array(
				'iframes' => $iframes_no_lazy,
			),
		);
	}
}
