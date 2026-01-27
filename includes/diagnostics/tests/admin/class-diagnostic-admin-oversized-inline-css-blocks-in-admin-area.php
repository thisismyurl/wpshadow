<?php
/**
 * Admin Oversized Inline CSS Blocks In Admin Area Diagnostic
 *
 * Detects excessively large inline CSS blocks in the admin area.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Admin
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin Oversized Inline CSS Blocks In Admin Area Diagnostic Class
 *
 * Identifies large inline CSS blocks in admin pages,
 * which impact performance and should be moved to external stylesheets.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Admin_Oversized_Inline_Css_Blocks_In_Admin_Area extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'admin-oversized-inline-css-blocks-in-admin-area';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Oversized Inline CSS Blocks In Admin Area';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects excessively large inline CSS blocks in admin pages';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'admin';

	/**
	 * Threshold for "large" inline styles (in bytes)
	 *
	 * @var int
	 */
	private static $size_threshold = 10240; // 10KB

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if ( ! is_admin() ) {
			return null;
		}

		$large_inline_styles = array();

		// Check for large inline styles in registered styles.
		global $wp_styles;

		if ( ! empty( $wp_styles ) && isset( $wp_styles->registered ) ) {
			foreach ( $wp_styles->registered as $handle => $style_obj ) {
				// Check for extra inline styles.
				if ( isset( $style_obj->extra['before'] ) ) {
					foreach ( $style_obj->extra['before'] as $before ) {
						if ( isset( $before ) ) {
							$size = strlen( (string) $before );

							if ( $size > self::$size_threshold ) {
								$large_inline_styles[] = array(
									'handle'   => $handle,
									'location' => 'before',
									'size'     => $size,
									'size_kb'  => round( $size / 1024, 2 ),
								);
							}
						}
					}
				}

				if ( isset( $style_obj->extra['after'] ) ) {
					foreach ( $style_obj->extra['after'] as $after ) {
						if ( isset( $after ) ) {
							$size = strlen( (string) $after );

							if ( $size > self::$size_threshold ) {
								$large_inline_styles[] = array(
									'handle'   => $handle,
									'location' => 'after',
									'size'     => $size,
									'size_kb'  => round( $size / 1024, 2 ),
								);
							}
						}
					}
				}
			}
		}

		if ( empty( $large_inline_styles ) ) {
			return null;
		}

		$items_list = '';
		$max_items  = 5;

		foreach ( array_slice( $large_inline_styles, 0, $max_items ) as $style ) {
			$items_list .= sprintf(
				"\n- %s (%s): %s KB",
				esc_html( $style['handle'] ),
				esc_html( $style['location'] ),
				$style['size_kb']
			);
		}

		if ( count( $large_inline_styles ) > $max_items ) {
			$items_list .= sprintf(
				/* translators: %d: count */
				__( "\n...and %d more oversized inline styles", 'wpshadow' ),
				count( $large_inline_styles ) - $max_items
			);
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: 1: count, 2: list */
				__( 'Found %1$d oversized inline CSS block(s) (over 10 KB). Large inline styles should be moved to external stylesheets for better caching, performance, and maintainability.%2$s', 'wpshadow' ),
				count( $large_inline_styles ),
				$items_list
			),
			'severity'     => 'low',
			'threat_level' => 30,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/admin-oversized-inline-css-blocks-in-admin-area',
			'meta'         => array(
				'large_styles' => $large_inline_styles,
			),
		);
	}
}
