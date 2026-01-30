<?php
/**
 * HTML Detect Long Title Beyond 60 Characters Diagnostic
 *
 * Detects page titles that are too long (beyond 60 characters).
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
 * HTML Detect Long Title Beyond 60 Characters Diagnostic Class
 *
 * Identifies pages with titles that are too long (beyond 60 characters),
 * which get truncated in search results and reduce readability.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Html_Detect_Long_Title_Beyond_60_Characters extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'html-detect-long-title-beyond-60-characters';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Page Title Too Long (Over 60 Characters)';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects page titles longer than recommended 60 characters';

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

		$long_titles = array();

		// Check page title.
		if ( isset( $GLOBALS['title'] ) ) {
			$title = (string) $GLOBALS['title'];

			if ( strlen( $title ) > 60 ) {
				$long_titles[] = array(
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
					if ( preg_match( '/document\.title\s*=\s*["\']([^"\']{61,})["\']/', $data, $m ) ) {
						$long_titles[] = array(
							'title'  => substr( $m[1], 0, 100 ),
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
			if ( strlen( $post->post_title ) > 60 ) {
				$long_titles[] = array(
					'title'  => $post->post_title,
					'length' => strlen( $post->post_title ),
					'source' => 'post_title',
				);
			}
		}

		if ( empty( $long_titles ) ) {
			return null;
		}

		$items_list = '';
		$max_items  = 5;

		foreach ( array_slice( $long_titles, 0, $max_items ) as $item ) {
			$items_list .= sprintf(
				"\n- \"%s...\" (%d chars)",
				esc_html( substr( $item['title'], 0, 50 ) ),
				(int) $item['length']
			);
		}

		if ( count( $long_titles ) > $max_items ) {
			$items_list .= sprintf(
				/* translators: %d: count */
				__( "\n...and %d more long titles", 'wpshadow' ),
				count( $long_titles ) - $max_items
			);
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: 1: count, 2: list */
				__( 'Found %1$d page title(s) exceeding 60 characters. Search engines typically display only 50-60 characters in results, causing titles to be truncated. Keep titles between 20-60 characters for optimal display.%2$s', 'wpshadow' ),
				count( $long_titles ),
				$items_list
			),
			'severity'     => 'low',
			'threat_level' => 30,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/html-detect-long-title-beyond-60-characters',
			'meta'         => array(
				'titles' => $long_titles,
			),
		);
	}
}
