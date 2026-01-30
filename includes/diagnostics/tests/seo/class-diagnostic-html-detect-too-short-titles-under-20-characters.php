<?php
/**
 * HTML Detect Too Short Titles Under 20 Characters Diagnostic
 *
 * Detects page titles that are too short (under 20 characters).
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
 * HTML Detect Too Short Titles Under 20 Characters Diagnostic Class
 *
 * Identifies pages with titles that are too short (under 20 characters),
 * which reduces SEO effectiveness and user context.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Html_Detect_Too_Short_Titles_Under_20_Characters extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'html-detect-too-short-titles-under-20-characters';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Page Title Too Short (Under 20 Characters)';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects page titles shorter than recommended 20 characters';

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

		$short_titles = array();

		// Check page title.
		if ( isset( $GLOBALS['title'] ) ) {
			$title = (string) $GLOBALS['title'];

			if ( strlen( $title ) < 20 ) {
				$short_titles[] = array(
					'title'  => $title,
					'length' => strlen( $title ),
					'source' => 'GLOBALS[title]',
				);
			}
		}

		// Check scripts for title patterns.
		global $wp_scripts;

		if ( ! empty( $wp_scripts ) && isset( $wp_scripts->registered ) ) {
			foreach ( $wp_scripts->registered as $handle => $script_obj ) {
				if ( isset( $script_obj->extra['data'] ) ) {
					$data = (string) $script_obj->extra['data'];

					// Look for document.title assignments.
					if ( preg_match( '/document\.title\s*=\s*["\']([^"\']{1,19})["\']/', $data, $m ) ) {
						$short_titles[] = array(
							'title'  => $m[1],
							'length' => strlen( $m[1] ),
							'source' => $handle,
						);
					}
				}
			}
		}

		// Check post/page title if available.
		global $post;

		if ( ! empty( $post ) && $post instanceof \WP_Post ) {
			if ( strlen( $post->post_title ) < 20 ) {
				$short_titles[] = array(
					'title'  => $post->post_title,
					'length' => strlen( $post->post_title ),
					'source' => 'post_title',
				);
			}
		}

		if ( empty( $short_titles ) ) {
			return null;
		}

		$items_list = '';
		$max_items  = 5;

		foreach ( array_slice( $short_titles, 0, $max_items ) as $item ) {
			$items_list .= sprintf(
				"\n- \"%s\" (%d chars)",
				esc_html( $item['title'] ),
				(int) $item['length']
			);
		}

		if ( count( $short_titles ) > $max_items ) {
			$items_list .= sprintf(
				/* translators: %d: count */
				__( "\n...and %d more short titles", 'wpshadow' ),
				count( $short_titles ) - $max_items
			);
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: 1: count, 2: list */
				__( 'Found %1$d page title(s) under 20 characters. Titles should be 20-60 characters to be effective for SEO and user context. Short titles lack keyword opportunity and don\'t describe content adequately.%2$s', 'wpshadow' ),
				count( $short_titles ),
				$items_list
			),
			'severity'     => 'low',
			'threat_level' => 30,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/html-detect-too-short-titles-under-20-characters',
			'meta'         => array(
				'titles' => $short_titles,
			),
		);
	}
}
