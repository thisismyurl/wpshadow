<?php
/**
 * HTML Detect Heavy Use Of Inline Styles Diagnostic
 *
 * Detects excessive use of inline style attributes.
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
 * HTML Detect Heavy Use Of Inline Styles Diagnostic Class
 *
 * Identifies elements using inline style attributes excessively, which
 * violates CSS best practices and makes styles hard to maintain.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Html_Detect_Heavy_Use_Of_Inline_Styles extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'html-detect-heavy-use-of-inline-styles';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Heavy Use of Inline Styles';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects excessive use of inline style attributes in HTML';

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

		$inline_styles = array();

		// Check WordPress scripts and styles for inline style patterns.
		global $wp_scripts;

		if ( ! empty( $wp_scripts ) && isset( $wp_scripts->registered ) ) {
			foreach ( $wp_scripts->registered as $handle => $script_obj ) {
				if ( isset( $script_obj->extra['data'] ) ) {
					$data = (string) $script_obj->extra['data'];

					// Count style attributes.
					$count = preg_match_all( '/style=["\']([^"\']*)["\']/', $data, $matches );

					if ( $count > 10 ) {
						// Excessive inline styles.
						$total_length = 0;

						foreach ( $matches[1] as $style ) {
							$total_length += strlen( $style );
						}

						$inline_styles[] = array(
							'handle'       => $handle,
							'count'        => $count,
							'total_bytes'  => $total_length,
							'avg_length'   => round( $total_length / $count ),
							'issue'        => sprintf(
								__( '%d elements with inline styles (%s total CSS)', 'wpshadow' ),
								$count,
								round( $total_length / 1024, 1 ) . 'KB'
							),
						);
					}
				}
			}
		}

		// Check styles too.
		global $wp_styles;

		if ( ! empty( $wp_styles ) && isset( $wp_styles->registered ) ) {
			foreach ( $wp_styles->registered as $handle => $style_obj ) {
				if ( isset( $style_obj->extra['data'] ) ) {
					$data = (string) $style_obj->extra['data'];

					// Count style attributes.
					$count = preg_match_all( '/style=["\']([^"\']*)["\']/', $data, $matches );

					if ( $count > 10 ) {
						// Excessive inline styles.
						$total_length = 0;

						foreach ( $matches[1] as $style ) {
							$total_length += strlen( $style );
						}

						$inline_styles[] = array(
							'handle'       => $handle,
							'count'        => $count,
							'total_bytes'  => $total_length,
							'avg_length'   => round( $total_length / $count ),
							'issue'        => sprintf(
								__( '%d elements with inline styles (%s total CSS)', 'wpshadow' ),
								$count,
								round( $total_length / 1024, 1 ) . 'KB'
							),
						);
					}
				}
			}
		}

		if ( empty( $inline_styles ) ) {
			return null;
		}

		$items_list = '';
		$max_items  = 5;

		foreach ( array_slice( $inline_styles, 0, $max_items ) as $item ) {
			$items_list .= sprintf(
				"\n- %s: %s",
				esc_html( $item['handle'] ),
				esc_html( $item['issue'] )
			);
		}

		if ( count( $inline_styles ) > $max_items ) {
			$items_list .= sprintf(
				/* translators: %d: count */
				__( "\n...and %d more sources with inline styles", 'wpshadow' ),
				count( $inline_styles ) - $max_items
			);
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: 1: count, 2: list */
				__( 'Found %1$d source(s) with excessive inline styles. Heavy use of inline style="..." attributes violates CSS best practices, makes styles hard to maintain, and can\'t be cached. Move repeated inline styles to CSS classes and stylesheets instead.%2$s', 'wpshadow' ),
				count( $inline_styles ),
				$items_list
			),
			'severity'     => 'low',
			'threat_level' => 30,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/html-detect-heavy-use-of-inline-styles',
			'meta'         => array(
				'sources' => $inline_styles,
			),
		);
	}
}
