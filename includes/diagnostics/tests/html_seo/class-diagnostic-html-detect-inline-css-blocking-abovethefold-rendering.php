<?php
/**
 * HTML Detect Inline CSS Blocking Above The Fold Rendering Diagnostic
 *
 * Detects inline CSS that blocks above-the-fold content rendering.
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
 * HTML Detect Inline CSS Blocking Above The Fold Rendering Diagnostic Class
 *
 * Identifies large blocks of inline CSS in the <head> that delay rendering
 * of above-the-fold content.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Html_Detect_Inline_Css_Blocking_Abovethefold_Rendering extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'html-detect-inline-css-blocking-abovethefold-rendering';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Inline CSS Blocking Above-the-Fold Rendering';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects large inline CSS blocks that delay page rendering';

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

		$blocking_css = array();
		$threshold    = 15000; // 15KB of inline CSS is considered excessive.

		// Check WordPress styles for large inline CSS.
		global $wp_styles;

		if ( ! empty( $wp_styles ) && isset( $wp_styles->registered ) ) {
			foreach ( $wp_styles->registered as $handle => $style_obj ) {
				if ( isset( $style_obj->extra['data'] ) ) {
					$data = (string) $style_obj->extra['data'];

					// Extract CSS content (between <style> tags or raw CSS).
					$css_content = '';

					if ( preg_match( '/<style[^>]*>(.*?)<\/style>/is', $data, $m ) ) {
						$css_content = $m[1];
					} else {
						// Inline CSS without wrapper tags.
						$css_content = $data;
					}

					$size = strlen( $css_content );

					if ( $size > $threshold ) {
						$blocking_css[] = array(
							'handle'     => $handle,
							'size_bytes' => $size,
							'size_kb'    => round( $size / 1024, 2 ),
							'issue'      => sprintf(
								__( 'Large inline CSS block (%s) delays page rendering', 'wpshadow' ),
								round( $size / 1024, 1 ) . 'KB'
							),
						);
					}
				}
			}
		}

		if ( empty( $blocking_css ) ) {
			return null;
		}

		$items_list = '';
		$max_items  = 5;

		foreach ( array_slice( $blocking_css, 0, $max_items ) as $item ) {
			$items_list .= sprintf(
				"\n- %s: %s (%sKB)",
				esc_html( $item['handle'] ),
				esc_html( $item['issue'] ),
				number_format( $item['size_kb'], 1 )
			);
		}

		if ( count( $blocking_css ) > $max_items ) {
			$items_list .= sprintf(
				/* translators: %d: count */
				__( "\n...and %d more blocking CSS blocks", 'wpshadow' ),
				count( $blocking_css ) - $max_items
			);
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: 1: count, 2: list */
				__( 'Found %1$d large inline CSS block(s) in page head. Large CSS blocks delay rendering of above-the-fold content. Consider: using critical CSS inlining (only essential styles inline), moving non-critical CSS to external files, or deferring CSS loading.%2$s', 'wpshadow' ),
				count( $blocking_css ),
				$items_list
			),
			'severity'     => 'medium',
			'threat_level' => 30,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/html-detect-inline-css-blocking-abovethefold-rendering',
			'meta'         => array(
				'blocks' => $blocking_css,
			),
		);
	}
}
