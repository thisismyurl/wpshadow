<?php
/**
 * Admin Oversized Inline JS Blocks In Admin Area Diagnostic
 *
 * Detects excessively large inline JavaScript blocks in the admin area.
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
 * Admin Oversized Inline JS Blocks In Admin Area Diagnostic Class
 *
 * Identifies large inline JavaScript blocks in admin pages,
 * which impact performance and should be moved to external files.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Admin_Oversized_Inline_Js_Blocks_In_Admin_Area extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'admin-oversized-inline-js-blocks-in-admin-area';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Oversized Inline JavaScript Blocks In Admin Area';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects excessively large inline JavaScript blocks in admin pages';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'admin';

	/**
	 * Threshold for "large" inline script (in bytes)
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

		// Check for large inline scripts in registered scripts using WordPress API.
		$large_inline_scripts = array();

		// Check for large inline scripts in registered scripts.
		global $wp_scripts;

		if ( ! empty( $wp_scripts ) && isset( $wp_scripts->registered ) ) {
			foreach ( $wp_scripts->registered as $handle => $script_obj ) {
				// Check for extra inline scripts.
				if ( isset( $script_obj->extra['before'] ) ) {
					foreach ( $script_obj->extra['before'] as $before ) {
						if ( isset( $before ) ) {
							$size = strlen( (string) $before );

							if ( $size > self::$size_threshold ) {
								$large_inline_scripts[] = array(
									'handle'   => $handle,
									'location' => 'before',
									'size'     => $size,
									'size_kb'  => round( $size / 1024, 2 ),
								);
							}
						}
					}
				}

				if ( isset( $script_obj->extra['after'] ) ) {
					foreach ( $script_obj->extra['after'] as $after ) {
						if ( isset( $after ) ) {
							$size = strlen( (string) $after );

							if ( $size > self::$size_threshold ) {
								$large_inline_scripts[] = array(
									'handle'   => $handle,
									'location' => 'after',
									'size'     => $size,
									'size_kb'  => round( $size / 1024, 2 ),
								);
							}
						}
					}
				}

				// Check localized data.
				if ( isset( $script_obj->extra['data'] ) ) {
					$size = strlen( (string) $script_obj->extra['data'] );

					if ( $size > self::$size_threshold ) {
						$large_inline_scripts[] = array(
							'handle'   => $handle,
							'location' => 'localized_data',
							'size'     => $size,
							'size_kb'  => round( $size / 1024, 2 ),
						);
					}
				}
			}
		}

		if ( empty( $large_inline_scripts ) ) {
			return null;
		}

		$items_list = '';
		$max_items  = 5;

		foreach ( array_slice( $large_inline_scripts, 0, $max_items ) as $script ) {
			$items_list .= sprintf(
				"\n- %s (%s): %s KB",
				esc_html( $script['handle'] ),
				esc_html( $script['location'] ),
				$script['size_kb']
			);
		}

		if ( count( $large_inline_scripts ) > $max_items ) {
			$items_list .= sprintf(
				/* translators: %d: count */
				__( "\n...and %d more oversized inline scripts", 'wpshadow' ),
				count( $large_inline_scripts ) - $max_items
			);
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: 1: count, 2: list */
				__( 'Found %1$d oversized inline JavaScript block(s) (over 10 KB). Large inline scripts should be moved to external files for better caching, performance, and maintainability.%2$s', 'wpshadow' ),
				count( $large_inline_scripts ),
				$items_list
			),
			'severity'     => 'low',
			'threat_level' => 30,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/admin-oversized-inline-js-blocks-in-admin-area',
			'meta'         => array(
				'large_scripts' => $large_inline_scripts,
			),
		);
	}
}
