<?php
/**
 * HTML Detect Iframe Missing Title Attribute Diagnostic
 *
 * Detects iframes without title attributes.
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
 * HTML Detect Iframe Missing Title Attribute Diagnostic Class
 *
 * Identifies iframes missing title attributes, which are required for
 * accessibility and screen reader compatibility.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Html_Detect_Iframe_Missing_Title_Attribute extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'html-detect-iframe-missing-title-attribute';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Iframes Missing Title Attribute';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects iframes without accessible title attribute';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'accessibility';

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

		$iframes_no_title = array();

		// Check scripts for iframes.
		global $wp_scripts;

		if ( ! empty( $wp_scripts ) && isset( $wp_scripts->registered ) ) {
			foreach ( $wp_scripts->registered as $handle => $script_obj ) {
				if ( isset( $script_obj->extra['data'] ) ) {
					$data = (string) $script_obj->extra['data'];

					// Find all iframe tags.
					if ( preg_match_all( '/<iframe[^>]*>/i', $data, $matches ) ) {
						foreach ( $matches[0] as $iframe_tag ) {
							// Check if has title attribute.
							if ( ! preg_match( '/title\s*=\s*["\']([^"\']*)["\']|title\s*=\s*([^\s>]+)/i', $iframe_tag ) ) {
								// Extract src if possible.
								$src = '';

								if ( preg_match( '/src=["\']([^"\']+)["\']/', $iframe_tag, $m ) ) {
									$src = $m[1];
								}

								$iframes_no_title[] = array(
									'handle' => $handle,
									'src'    => $src,
									'tag'    => substr( $iframe_tag, 0, 80 ),
									'issue'  => __( 'Iframe missing title attribute (required for accessibility)', 'wpshadow' ),
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
					// Check if has title attribute.
					if ( ! preg_match( '/title\s*=\s*["\']([^"\']*)["\']|title\s*=\s*([^\s>]+)/i', $iframe_tag ) ) {
						// Extract src if possible.
						$src = '';

						if ( preg_match( '/src=["\']([^"\']+)["\']/', $iframe_tag, $m ) ) {
							$src = $m[1];
						}

						$iframes_no_title[] = array(
							'handle' => 'post_content',
							'src'    => $src,
							'tag'    => substr( $iframe_tag, 0, 80 ),
							'issue'  => __( 'Iframe missing title attribute (required for accessibility)', 'wpshadow' ),
						);
					}
				}
			}
		}

		if ( empty( $iframes_no_title ) ) {
			return null;
		}

		$items_list = '';
		$max_items  = 5;

		foreach ( array_slice( $iframes_no_title, 0, $max_items ) as $item ) {
			$source = ! empty( $item['src'] ) ? parse_url( $item['src'], PHP_URL_HOST ) : 'unknown';
			$items_list .= sprintf(
				"\n- %s: %s",
				esc_html( $source ),
				esc_html( $item['issue'] )
			);
		}

		if ( count( $iframes_no_title ) > $max_items ) {
			$items_list .= sprintf(
				/* translators: %d: count */
				__( "\n...and %d more iframes missing title", 'wpshadow' ),
				count( $iframes_no_title ) - $max_items
			);
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: 1: count, 2: list */
				__( 'Found %1$d iframe(s) without title attribute. The title attribute is required for accessibility—screen readers announce it to describe what the embedded content is. Add meaningful title="content description" to all iframes.%2$s', 'wpshadow' ),
				count( $iframes_no_title ),
				$items_list
			),
			'severity'     => 'high',
			'threat_level' => 40,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/html-detect-iframe-missing-title-attribute',
			'meta'         => array(
				'iframes' => $iframes_no_title,
			),
		);
	}
}
